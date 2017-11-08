<?php
defined('config_done') or die();

$globals['extra_js'][] = 'autocomplete/jquery.autocomplete.min.js';
$globals['extra_js'][] = 'jquery.user_autocomplete.js';
$globals['extra_css'][] = 'jquery.autocomplete.css';
$globals['extra_css'][] = 'bootstrap.min.css';
$globals['extra_css'][] = 'bootstrap-theme.min.css';
$globals['extra_css'][] = 'admin.css';
$globals['extra_js'][] = '../admin/js/admin.js';
$globals['ads'] = false;

if (!$current_user->admin) {
    die(Haanga::Load('admin/no_access.html'));
}

function get_admin_tabs()
{
    return [
        'admin_logs' => 'logs.php',
        'comment_reports' => 'reports.php',
        'strikes' => 'strikes.php',
        'hostname' => 'bans.php?tab=hostname',
        'punished_hostname' => 'bans.php?tab=punished_hostname',
        'email' => 'bans.php?tab=email',
        'ip' => 'bans.php?tab=ip',
        'words' => 'bans.php?tab=words',
        'noaccess' => 'bans.php?tab=noaccess',
        'preguntame' => 'preguntame.php',
        'sponsors' => 'sponsors.php',
        'mafia' => 'mafia.php',
        'admin_users' => 'admin_users.php'
    ];
}

function do_admin_tabs($tab_selected = false)
{
    global $current_user;

    Haanga::Load('admin/tabs.html', [
        'tabs' => array_intersect_key(get_admin_tabs(), array_flip(AdminUser::sectionsByAdminId($current_user->user_id))),
        'tab_selected' => $tab_selected
    ]);
}

function adminAllowed($section)
{
    global $current_user;

    if (!AdminUser::allowed($current_user->user_id, $section)) {
        die(Haanga::Load('admin/no_access.html'));
    }
}

function URLQuery()
{
    parse_str($_SERVER['QUERY_STRING'], $data);

    $args = func_get_args();

    for ($i = 0, $count = count($args); $i < $count; $i += 2) {
        $data[$args[$i]] = $args[$i + 1];
    }

    return http_build_query(array_filter($data));
}
