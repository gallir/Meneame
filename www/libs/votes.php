<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Vote {

    function __construct($type='links', $link=0, $user=-1) {
        $this->type = $type;
        $this->link = $link;
        $this->user = $user;
    }

    function get_where($value='> 0') {
        global $globals;
        // Begin check user and ip
        $where = "vote_type='$this->type' AND vote_link_id=$this->link";
        if (empty($this->ip)) $this->ip=$globals['user_ip_int'];
        if($this->user > 0) {
            $where .= " AND (vote_user_id=$this->user OR vote_ip_int=$this->ip)";
        } elseif ($this->user == 0 ) {
            $where .= " AND vote_ip_int=$this->ip";
        }
        if (!empty($value)) $where .= " AND vote_value $value ";
        // End check user and ip
        return $where;
    }

    function exists($check_ip = true) {
        global $db, $globals;

        if (! $this->ip) $this->ip=$globals['user_ip_int'];
        if($this->user > 0) {
            if (! $check_ip) $where = " AND vote_user_id=$this->user";
            else $where = " AND (vote_user_id=$this->user OR vote_ip_int=$this->ip)";
        } else {
            $where = " AND vote_ip_int=$this->ip";
        }

        return $db->get_var("SELECT SQL_NO_CACHE vote_value FROM votes WHERE vote_type='$this->type' AND vote_link_id=$this->link $where LIMIT 1");
    }

    function count($value="> 0") {
        global $db;
        $where = $this->get_where($value);
        $count=$db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes WHERE $where");
        return $count;
    }

    function insert() {
        global $db, $globals;
        if (empty($this->ip)) $this->ip=$globals['user_ip_int'];
        $this->value=round($this->value);

        $sql="INSERT IGNORE INTO votes (vote_type, vote_user_id, vote_link_id, vote_value, vote_ip_int) VALUES ('$this->type', $this->user, $this->link, $this->value, $this->ip)";
        $r = $db->query($sql);
        return $db->affected_rows;
    }

    public static function fast_vote($type, $seconds = 60)
    {
        global $db, $current_user;

        if (!$current_user) {
            return true;
        }

        $query = '
            SELECT `vote_date`
            FROM `votes`
            WHERE (
                `vote_user_id` = "'.(int)$current_user->user_id.'"
                AND `vote_type` = "'.$type.'"
            )
            ORDER BY `vote_date` DESC
            LIMIT 1;
        ';

        if ($vote = $db->get_var(DbHelper::queryPlain($query))) {
            return (strtotime($vote) > (time() - $seconds));
        }
    }
}
