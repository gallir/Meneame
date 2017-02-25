<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Strike
{
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

    public static $sortColumns = [
        'user_login', 'strike_type', 'strike_reason', 'strike_report_id',
        'strike_karma_old', 'strike_karma_new', 'strike_karma_restore',
        'strike_date', 'strike_modified', 'admin_login'
    ];

    const SQL_SIMPLE = '
        strike_id AS id, strike_type AS type, strike_date AS date, strike_reason AS reason,
        strike_admin_id AS admin_id, strike_user_id AS user_id, strike_report_id AS report_id,
        strike_karma_old AS karma_old, strike_karma_new AS karma_new, strike_karma_restore AS karma_restore,
        strike_comment AS comment, strike_hours AS hours, strike_expires_at AS expires_at, strike_ip AS ip
        FROM strikes
    ';

    const SQL = '
        strike_id AS id, strike_type AS type, strike_date AS date, strike_reason AS reason,
        strike_admin_id AS admin_id, strike_user_id AS user_id, strike_report_id AS report_id,
        strike_karma_old AS karma_old, strike_karma_new AS karma_new, strike_karma_restore AS karma_restore,
        strike_comment AS comment, strike_hours AS hours, strike_expires_at AS expires_at, strike_ip AS ip,
        admin.user_id AS admin_id, admin.user_login AS admin_login, users.user_id AS user_id,
        users.user_login AS user_login, users.user_karma AS actual_karma
        FROM strikes
        LEFT JOIN users AS admin ON (admin.user_id = strike_admin_id)
        LEFT JOIN users ON (users.user_id = strike_user_id)
        LEFT JOIN reports ON (reports.report_id = strike_report_id)
    ';

    // sql fields to build an object from mysql
    public $id = 0;
    public $type;
    public $date;
    public $reason;
    public $reason_message;
    public $admin_id = 0;
    public $user_id = 0;
    public $report_id = 0;
    public $karma_old;
    public $karma_new;
    public $karma_restore;
    public $hours = 0;
    public $expires_at;
    public $comment;
    public $ip;

    private $user;

    public function __construct(User $user, $type)
    {
        $this->user = $user;

        $this->setType($type);
    }

    public function setType($type)
    {
        if (!self::isValidType($type)) {
            return;
        }

        $this->type = $type;
        $this->karma_old = $this->user->karma;

        $type = self::getType($type);

        if (!is_array($type)) {
            return;
        }

        $this->karma_new = $type['karma'];
        $this->karma_restore = $this->karma_old + $type['restore'];
        $this->hours = $type['hours'];
    }

    public static function fromDB($id)
    {
        global $db;

        $row = $db->get_object('
            SELECT '.self::SQL_SIMPLE.'
            WHERE `strike_id` = "'.(int)$id.'"
            LIMIT 1;
        ', 'Strike');

        if ($row->reason) {
            $row->reason_message = self::$reasons[$row->reason];
        }

        return $row;
    }

    public static function list($search, $orderBy, $orderMode, $offset, $limit)
    {
        global $db;

        if ($search) {
            $search = $db->escape($search);

            $where = '
                WHERE (
                    admin.user_login LIKE "%'.$search.'%"
                    OR users.user_login LIKE "%'.$search.'%"
                    OR strike_type = "'.$search.'"
                    OR strike_reason = "'.$search.'"
                    OR (
                        strike_report_id > 0
                        AND strike_report_id = "'.(int)$search.'"
                    )
                )
            ';
        } else {
            $where = '';
        }

        $list = $db->get_results('
            SELECT '.self::SQL.'
            '.$where.'
            ORDER BY '.self::getValidOrder($orderBy, $orderMode).'
            LIMIT '.(int)$offset.', '.(int)$limit.';
        ');

        return self::setReasonMessage($list);
    }

    public static function setReasonMessage($list)
    {
        foreach ($list as $row) {
            $row->reason_message = self::$reasons[$row->reason];
        }

        return $list;
    }

    public static function getValidOrder($column, $mode)
    {
        if (!in_array($column, self::$sortColumns)) {
            $column = 'strike_date';
        }

        return $column.' '.(($mode === 'ASC') ? 'ASC' : 'DESC');
    }

    public static function count($search)
    {
        global $db;

        if (empty($search)) {
            return $db->get_var('SELECT COUNT(*) FROM strikes;');
        }
    }

    public static function getTypes()
    {
        global $globals;

        return $globals['strikes'];
    }

    public static function getType($type)
    {
        global $globals;

        if ($type === 'ban') {
            return $type;
        }

        if (self::isValidType($type)) {
            return $globals['strikes'][$type];
        }
    }

    public static function isValidType($type)
    {
        global $globals;

        return ($type === 'ban') || isset($globals['strikes'][$type]);
    }

    public static function isValidReason($reason)
    {
        return isset(self::$reasons[$reason]);
    }

    public function store()
    {
        global $db, $globals;

        if ($this->id) {
            return;
        }

        $this->date = date('Y-m-d H:i:s');
        $this->expires_at = date('Y-m-d H:i:s', strtotime('+'.$this->hours.' hours'));
        $this->user_id = $this->user->id;
        $this->ip = $globals['user_ip'];

        if (!$this->insert()) {
            $db->rollback();
            return false;
        }

        $this->executeAction();
        $this->executeReport();

        $db->commit();

        return true;
    }

    private function insert()
    {
        global $db, $globals;

        $response = $db->query('
            INSERT INTO `strikes`
            SET
                `strike_type` = "'.$this->type.'",
                `strike_reason` = "'.$this->reason.'",
                `strike_user_id` = "'.(int)$this->user_id.'",
                `strike_report_id` = "'.(int)$this->report_id.'",
                `strike_admin_id` = "'.(int)$this->admin_id.'",
                `strike_karma_old` = "'.(float)$this->karma_old.'",
                `strike_karma_new` = "'.(float)$this->karma_new.'",
                `strike_karma_restore` = "'.(float)$this->karma_restore.'",
                `strike_hours` = "'.(int)$this->hours.'",
                `strike_expires_at` = "'.$this->expires_at.'",
                `strike_comment` = "'.$this->comment.'",
                `strike_ip` = "'.$this->ip.'";
        ');

        if ($response) {
            $this->id = $db->insert_id;
        }

        return $response;
    }

    private function executeAction()
    {
        $this->{'executeAction'.ucfirst($this->type)}();
    }

    private function executeActionStrike1()
    {
        $this->executeActionStrike();
    }

    private function executeActionStrike2()
    {
        $this->executeActionStrike();
    }

    private function executeActionStrike3()
    {
        $this->executeActionStrike();
    }

    private function executeActionStrike()
    {
        global $db, $current_user;

        $db->query('
            UPDATE `users`
            SET `user_karma` = "'.(float)$this->karma_new.'"
            WHERE `user_id` = "'.(int)$this->user_id.'"
            LIMIT 1;
        ');

        LogAdmin::insert($this->type, $this->user_id, $current_user->user_id, $this->karma_old, $this->karma_new);
    }

    private function executeActionBan()
    {
        global $db, $current_user;

        $db->query('
            UPDATE `users`
            SET `user_level` = "disabled"
            WHERE `user_id` = "'.(int)$this->user_id.'"
            LIMIT 1;
        ');

        $db->query('
            INSERT INTO `bans`
            SET
                `ban_type` = "email",
                `ban_text` = "'.$this->user->email.'",
                `ban_comment` = "'.$this->comment.'";
        ');

        LogAdmin::insert($this->type, $this->user_id, $current_user->user_id, $this->user->level, 'disabled');
    }

    private function executeReport()
    {
        global $db, $current_user;

        if (!$this->report_id) {
            return;
        }

        $db->query('
            UPDATE `reports`
            SET
                `report_modified` = NOW(),
                `report_status` = "penalized",
                `report_revised_by` = "'.(int)$current_user->user_id.'"
            WHERE `report_id` = "'.(int)$this->report_id.'"
            LIMIT 1;
        ');
    }

    public static function getUserStrikes($user_id)
    {
        global $db;

        $list = $db->get_results('
            SELECT '.self::SQL.'
            WHERE `strike_user_id` = "'.(int)$user_id.'"
            ORDER BY `strike_date` DESC;
        ');

        return self::setReasonMessage($list);
    }

    public static function getUserValidTypes($user_id)
    {
        $current = self::getUserTypes($user_id);
        $types = self::getTypes();

        if (in_array('strike3', $current)) {
            return array();
        }

        if (in_array('strike2', $current)) {
            return array('strike3' => $types['strike3']);
        }

        if (in_array('strike1', $current)) {
            return array('strike2' => $types['strike2']);
        }

        return array('strike1' => $types['strike1']);
    }

    public static function isValidTypeForUser($user_id, $type)
    {
        if (self::isValidType($type)) {
            return !in_array($type, self::getUserTypes($user_id));
        }
    }

    public static function getUserTypes($user_id)
    {
        return array_filter(array_unique(array_map(function($value) {
            return $value->type;
        }, self::getUserStrikes($user_id))));
    }
}
