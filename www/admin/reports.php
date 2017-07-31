<?php
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// David Martín :: Suki_ :: <david at sukiweb dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called 'COPYING'.

$globals['skip_check_ip_noaccess'] = true;
require_once __DIR__.'/../config.php';
require_once mnminclude.'html1.php';
require_once __DIR__.'/libs/admin.php';

$page_size = 40;
$offset = (get_current_page() - 1) * $page_size;

$operation = $_REQUEST['op'] ?: 'list';

if ($_REQUEST['tab']) {
    $selected_tab = clean_input_string($_REQUEST['tab']);
} else {
    $selected_tab = 'comment_reports';
}

if (empty($_REQUEST['report_status'])) {
    $report_status = array('pending', 'debate');
} elseif (is_array($_REQUEST['report_status'])) {
    $report_status = $_REQUEST['report_status'];
} elseif ($_REQUEST['report_status'] === 'all') {
    $report_status = array('pending', 'debate', 'penalized', 'dismissed');
}

if (!empty($_REQUEST['report_date'])) {
    $report_date = clean_input_string($_REQUEST['report_date']);
} else {
    $report_date = 'all';
}

$statistics = calculate_statistics();
$key = get_security_key();

switch ($operation) {
    case 'list':
        do_header(_('Comment reports'));
        do_admin_tabs($selected_tab);
        do_report_list($selected_tab, $report_status, $report_date, $key, $statistics);

        break;

    case 'change_status':
        if (!check_security_key($_REQUEST['key'])) {
            die;
        }

        $report = Report::from_db($_REQUEST['report_id']);
        $status = $_REQUEST['new_report_status'];

        update_status($report, $status);

        die(header('Location: '.$_SERVER['REQUEST_URI']));
}

do_footer();

function do_report_list($selected_tab, $report_status, $report_date, $key, $statistics)
{
    global $db, $offset, $page_size, $globals;

    $where = ' WHERE report_type = "'.Report::REPORT_TYPE_LINK_COMMENT.'"';

    if ($report_status) {
        $where .= ' AND report_status IN ("'.implode('","', $report_status).'")';
    }

    $ts = null;

    switch ($report_date) {
        case 'two_hours':
            $ts = 7200;
            break;
        case 'six_hours':
            $ts = 6 * 3600;
            break;
        case 'twelve_hours':
            $ts = 12 * 3600;
            break;
        case 'one_day':
            $ts = 86400;
            break;
        case 'one_week':
            $ts = 7 * 86400;
            break;
    }

    if ($ts) {
        $where .= ' AND report_date > "'.date('Y-m-d H:i:s', $globals['now'] - $ts).'"';
    }

    if ($search = $_REQUEST['s']) {
        if (strpos($search, 'id:') === 0) {
            $where .= ' AND report_id = "'.explode(':', $search)[1].'"';
        } else {
            $where .= '
                AND (
                    authors.user_login LIKE "%'.$db->escape($search).'%"
                    OR report_id = "'.(int)$search.'"
                )
            ';
        }

        $rows = 0;
    } else {
        $rows = $db->get_var('SELECT COUNT(*) FROM reports '.$where.';');
    }

    $orderBy = Report::getValidOrder($_REQUEST['order_by'], $_REQUEST['order_mode']);
    $order_mode = strstr($orderBy, 'DESC') ? 'ASC' : 'DESC';

    $reports = group_by_comment($db->get_results('
        SELECT '.Report::SQL_COMMENT_GROUPED.'
        '.$where.'
        GROUP BY ref_id, reason
        ORDER BY '.$orderBy.'
        '.(($rows > 0) ? (' LIMIT '.$offset.', '.$page_size) : '').';
    ', 'Report'));

    Haanga::Load('admin/reports/list.html', compact(
        'reports', 'selected_tab', 'key', 'search', 'report_status', 'report_date', 'statistics',
        'order_mode'
    ));

    do_pages($rows, $page_size, false);
}

function update_status($report, $status)
{
    global $db, $current_user, $globals;

    if ($status === 'debate') {
        update_status_debate_mail($report);
    }

    return $db->query('
        UPDATE reports
        SET
            report_status = "'.$db->escape($status).'",
            report_revised_by = "'.(int)$current_user->user_id.'",
            report_modified = FROM_UNIXTIME("'.(int)$globals['now'].'")
        WHERE (
            report_ref_id = "'.(int)$report->ref_id.'"
            AND report_reason = "'.$db->escape($report->reason).'"
            AND report_type = "link_comment"
        );
    ');
}

function update_status_debate_mail($report)
{
    global $db, $globals;

    if (empty($globals['adm_email'])) {
        return;
    }

    require_once(mnminclude.'mail.php');

    $comment = $db->get_row('
        SELECT *
        FROM `comments`
        WHERE `comment_id` = "'.(int)$report->ref_id.'"
        LIMIT 1;
    ');

    $url = $globals['scheme'].'//'.get_server_name()
        .$globals['base_url'].'story/'.$report->comment_link_uri
        .'/c0'.$report->comment_order.'#c-'.$report->comment_order;

    $subject = 'Reporte ID '.$report->id
        .': ['.$report->author_user_login.']'
        .' "'.$report->getReasonTitle().'"'
        .' - Reportado por '.$report->reporter_user_login;

    $message = 'El usuario '.$report->reporter_user_login
        .' ha reportado el comentario del usuario '.$report->author_user_login
        .' por "'.$report->getReasonTitle().'":'
        ."\n\n".'['.$comment->comment_date.'] '
        .$comment->comment_content
        ."\n\n".'Link: <a href="'.$url.'">'.$url.'</a>';

    send_mail_list($subject, $message);
}

function calculate_statistics()
{
    $statistics = array();

    foreach (Report::getTotals() as $row) {
        $statistics[$row->status] = $row->total;
    }

    return $statistics;
}

function group_by_comment($reports)
{
    $grouped_reports = array();
    $parsed = array();

    foreach ($reports as $report) {
        $group = array();

        foreach ($reports as $r) {
            if ($r->ref_id == $report->ref_id && !in_array($r->ref_id, $parsed)) {
                $group[] = $r;
            }
        }

        $parsed[] = $report->ref_id;

        $grouped_reports[] = array(
            'num_lines' => count($group),
            'lines' => $group
        );
    }

    return $grouped_reports;
}
