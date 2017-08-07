<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Preguntame
{
    public $id;
    public $title;
    public $link;
    public $image;
    public $start_at;
    public $end_at;
    public $enabled;
    public $visible;

    public function store()
    {
        global $db, $current_user;

        $this->title = $db->escape(strip_tags($this->title));
        $this->subtitle = $db->escape(strip_tags($this->subtitle));
        $this->link = $db->escape(strip_tags($this->link));

        $this->start_at = date('Y-m-d H:i:s', strtotime($this->start_at));
        $this->end_at = date('Y-m-d H:i:s', strtotime($this->end_at));

        $this->enabled = (bool)$this->enabled;
        $this->admin_id = (int)$current_user->user_id;

        if ($this->id) {
            return $this->update();
        }

        return $this->insert();
    }

    private function update()
    {
        global $db;

        return $db->query('
            UPDATE `preguntame`
            SET
                `title` = "'.$this->title.'",
                `subtitle` = "'.$this->subtitle.'",
                `link` = "'.$this->link.'",
                `image` = "'.$this->image.'",
                `start_at` = "'.$this->start_at.'",
                `end_at` = "'.$this->end_at.'",
                `enabled` = "'.$this->enabled.'",
                `admin_id` = "'.$this->admin_id.'"
            WHERE id = "'.(int)$this->id.'"
            LIMIT 1;
        ');
    }

    private function insert()
    {
        global $db;

        $response = $db->query('
            INSERT INTO `preguntame`
            SET
                `title` = "'.$this->title.'",
                `subtitle` = "'.$this->subtitle.'",
                `link` = "'.$this->link.'",
                `image` = "'.$this->image.'",
                `start_at` = "'.$this->start_at.'",
                `end_at` = "'.$this->end_at.'",
                `enabled` = "'.$this->enabled.'",
                `admin_id` = "'.$this->admin_id.'";
        ');

        if ($response) {
            $this->id = $db->insert_id;
        }

        return $response;
    }

    public static function listing($offset, $limit)
    {
        global $db;

        return $db->get_results('
            SELECT *
            FROM `preguntame`
            ORDER BY `start_at` DESC
            LIMIT '.(int)$offset.', '.(int)$limit.';
        ');
    }

    public static function count()
    {
        global $db;

        return $db->get_var('SELECT COUNT(*) FROM `preguntame`;');
    }

    public static function getById($id)
    {
        global $db;

        return $db->get_object('
            SELECT *
            FROM `preguntame`
            WHERE `id` = "'.(int)$id.'"
            LIMIT 1;
        ', 'Preguntame');
    }
}
