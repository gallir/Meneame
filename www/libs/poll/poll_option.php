<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class PollOption
{
    public $id;
    public $option;
    public $votes = 0;
    public $karma = 0;
    public $poll_id;

    public $simple;
    public $index;
    public $voted;
    public $winner;

    public function store()
    {
        if (empty($this->id) || ($this->id < 1)) {
            $response = $this->insert();
        } elseif ($this->option) {
            $response = $this->update();
        } else {
            $response = $this->delete();
        }

        return $response;
    }

    public function setOption($option)
    {
        $this->option = $this->normalize($option);
    }

    private function insert()
    {
        global $db;

        $response = $db->query(str_replace("\n", ' ', '
            INSERT INTO `polls_options`
            SET
                `option` = "'.$db->escape($this->option).'",
                `poll_id` = '.($this->poll_id ?: 'NULL').';
        '));

        if (empty($response)) {
            return false;
        }

        $this->id = $db->insert_id;

        return true;
    }

    private function update()
    {
        global $db;

        $response = $db->query(str_replace("\n", ' ', '
            UPDATE `polls_options`
            SET `option` = "'.$db->escape($this->option).'"
            WHERE `id` = "'.(int)$this->id.'"
            LIMIT 1;
        '));

        return $response ? true : false;
    }

    private function delete()
    {
        global $db;

        $response = $db->query(str_replace("\n", ' ', '
            DELETE FROM `polls_options`
            WHERE `id` = "'.(int)$this->id.'"
            LIMIT 1;
        '));

        return $response ? true : false;
    }

    public function vote()
    {
        global $db, $current_user;

        if (empty($this->id)) {
            return;
        }

        $query = '
            UPDATE `polls_options`
            SET
                `votes` = `votes` + 1,
                `karma` = `karma` + "'.(float)$current_user->user_karma.'"
            WHERE `id` = "'.$this->id.'"
            LIMIT 1;
        ';

        if (!$db->query(DbHelper::queryPlain($query))) {
            return;
        }

        $this->votes ++;
        $this->karma += (float)$current_user->user_karma;
        $this->voted = true;

        return true;
    }

    private function normalize($value)
    {
        return htmlspecialchars(trim(preg_replace('/[\n|\r]/', '', clear_whitespace(strip_tags($value)))));
    }

    public static function selectFromPollId($poll_id)
    {
        global $db;

        return $db->get_results(DbHelper::queryPlain('
            SELECT *
            FROM `polls_options`
            WHERE `poll_id` = "'.(int)$poll_id.'"
            ORDER BY `id` ASC;
        '), 'PollOption');
    }

    public static function selectFromPollIds(array $poll_ids)
    {
        global $db;

        if (empty($poll_ids)) {
            return array();
        }

        return $db->get_results(DbHelper::queryPlain('
            SELECT *
            FROM `polls_options`
            WHERE `poll_id` IN ('.DbHelper::implodedIds($poll_ids).')
            ORDER BY `id` ASC;
        '), 'PollOption');
    }
}
