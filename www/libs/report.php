<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Report extends LCPBase
{
    const REPORT_TYPE_LINK_COMMENT = 'link_comment';

    const REPORT_STATUS_PENDING = 'pending';
    const REPORT_STATUS_DEBATE = 'debate';
    const REPORT_STATUS_PENALIZED = 'penalized';
    const REPORT_STATUS_DISMISSED = 'dismissed';

    const SQL_COMMENT = '
        report_id as id, report_type as type, report_date as date, report_modified as modified, report_status as status, report_reason as reason, reporters.user_id as reporter_id, reporters.user_level as reporter_user_level, reporters.user_login as reporter_user_login, authors.user_id as author_id, authors.user_level as author_user_level, authors.user_login as author_user_login, revisors.user_id as revisor_id, revisors.user_level as revisor_user_level, revisors.user_login as revisor_user_login, report_ip as ip, comment_id as ref_id, comment_order, comment_link_id, link_uri as comment_link_uri FROM reports
        LEFT JOIN users as reporters on (reporters.user_id = report_user_id)
        LEFT JOIN comments on (comments.comment_id = reports.report_ref_id)
        LEFT JOIN links on (comments.comment_link_id = links.link_id)
        LEFT JOIN users as authors on (authors.user_id = comments.comment_user_id)
        LEFT JOIN users as revisors on (revisors.user_id = reports.report_revised_by)
    ';

    const SQL_COMMENT_GROUPED = '
        COUNT(*) as report_num, report_id as id, report_type as type, report_date as date, report_modified as modified, report_status as status, report_reason as reason, reporters.user_id as reporter_id, reporters.user_level as reporter_user_level, reporters.user_login as reporter_user_login, authors.user_id as author_id, authors.user_level as author_user_level, authors.user_login as author_user_login, revisors.user_id as revisor_id, revisors.user_level as revisor_user_level, revisors.user_login as revisor_user_login, report_ip as ip, comment_id as ref_id, comment_order, comment_link_id, link_uri as comment_link_uri FROM reports
        LEFT JOIN users as reporters on (reporters.user_id = report_user_id)
        LEFT JOIN comments on (comments.comment_id = reports.report_ref_id)
        LEFT JOIN links on (comments.comment_link_id = links.link_id)
        LEFT JOIN users as authors on (authors.user_id = comments.comment_user_id)
        LEFT JOIN users as revisors on (revisors.user_id = reports.report_revised_by)
    ';

    public static $statuses = [
        'pending' => 'Pendiente',
        'debate' => 'En debate',
        'penalized' => 'Penalizado',
        'dismissed' => 'Descartado',
    ];

    public static $reasons = [
        'inappropriate_content' => 'Contenido inapropiado',
        'private_data' => 'Contiene datos personales propios o de un tercero',
        'incites_hatred' => 'Incitación al odio',
        'legality' => 'Incumple la legalidad española vigente',
        'insult' => 'Insultos directos',
        'violence_porn' => 'Material pornográfico o de violencia gráfica',
        'advertising' => 'Promoción comercial de productos o servicios',
        'spam' => 'Spam',
        'violate_rules' => 'Viola las normas de uso',
    ];

    // sql fields to build an object from mysql
    public $id = 0;
    public $type = Report::REPORT_TYPE_LINK_COMMENT;
    public $date;
    public $status = Report::REPORT_STATUS_PENDING;
    public $reason = '';
    public $reporter_id;
    public $author_id;
    public $ref_id;
    public $revised_by;
    public $modified = null;
    public $ip;

    public static function from_db($id, $report_type = Report::REPORT_TYPE_LINK_COMMENT)
    {
        global $db;

        if ($report_type !== Report::REPORT_TYPE_LINK_COMMENT) {
            return;
        }

        return $db->get_object('
            SELECT '.Report::SQL_COMMENT.'
            WHERE (
                report_id = '.(int)$id.'
                AND report_type = "'.$db->escape($report_type).'"
            )
            LIMIT 1;
        ', 'Report');
    }

    public static function is_valid_reason($reason)
    {
        return in_array($reason, array(static::$reasons));
    }

    public static function getValidOrder($column, $mode)
    {
        if (!in_array($column, ['report_id', 'report_num', 'author_user_login', 'report_date', 'report_status', 'revisor_user_login', 'report_modified'])) {
            $column = 'report_num';
        }

        return $column.' '.(($mode === 'ASC') ? 'ASC' : 'DESC');
    }

    public static function check_min_karma()
    {
        global $current_user, $globals;

        return ($globals['min_karma_for_report_comments'] > $current_user->karma);
    }

    public static function already_reported($report_ref_id)
    {
        global $db, $current_user;

        return (bool)$db->get_var('
            SELECT COUNT(*)
            FROM reports
            WHERE (
                report_ref_id = "'.(int)$report_ref_id.'"
                AND report_user_id = "'.(int)$current_user->user_id.'"
            );
        ');
    }

    public static function check_report_user_limit()
    {
        global $db, $current_user, $globals;

        $count = (int)$db->get_var('
            SELECT COUNT(*)
            FROM reports
            WHERE (
                report_user_id = "'.(int)$current_user->user_id.'"
                AND (NOW() - report_date) < 86400
            );
        ');

        return $count < $globals['max_reports_for_comments'];
    }

    public static function getTotals()
    {
        global $db;

        return $db->get_results('
            SELECT COUNT(*) `total`, report_status `status`
            FROM `reports`
            GROUP BY `status`;
        ');
    }

    public function getStatusTitle()
    {
        return static::$statuses[$this->status];
    }

    public function getReasonTitle()
    {
        return static::$reasons[$this->reason];
    }

    public function store()
    {
        global $db, $globals;

        if (!$this->date) {
            $this->date = $globals['now'];
        }

        $report_date = $this->date;
        $report_type = $this->type;
        $report_reason = $this->reason;
        $report_user_id = $this->reporter_id;
        $report_ref_id = $this->ref_id;
        $report_status = $this->status;
        $report_modified = $this->modified;
        $report_revised_by = $this->revised_by;
        $report_ip = $this->ip = $globals['user_ip'];

        if ($this->id === 0) {
            $r = $db->query("INSERT INTO reports (report_date, report_type, report_reason, report_user_id, report_ref_id, report_status, report_modified, report_revised_by, report_ip) VALUES(FROM_UNIXTIME($report_date), '$report_type', '$report_reason', $report_user_id, $report_ref_id, '$report_status', null, null, '$report_ip')");
            $this->id = $db->insert_id;
        } else {
            $r = $db->query("UPDATE reports set report_date=FROM_UNIXTIME($report_date), report_type='$report_type', report_reason='$report_reason', report_user_id=$report_user_id, report_ref_id=$report_ref_id, report_status='$report_status', report_modified=FROM_UNIXTIME($report_modified), report_revised_by=$report_revised_by, report_ip='$report_ip'");
        }

        if (!$r) {
            $db->rollback();
            return false;
        }

        $db->commit();
        return true;
    }
}
