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
    public $subtitle;
    public $link;
    public $start_at;
    public $end_at;
    public $sponsored;
    public $enabled;
    public $admin_id;

    public $media_size;
    public $media_mime;
    public $media_extension;
    public $media_access;
    public $media_date;

    const SQL_SELECT = '
        SELECT `preguntame`.*,  `media`.`size` `media_size`, `media`.`mime` `media_mime`,
            `media`.`extension` `media_extension`, `media`.`access` `media_access`,
            UNIX_TIMESTAMP(`media`.`date`) `media_date`
        FROM `preguntame`
        LEFT JOIN `media` ON (
            `media`.`type`= "preguntame"
            AND `media`.`id` = `preguntame`.`id`
            AND `media`.`version` = 0
        )
    ';

    public static function getById($id)
    {
        global $db;

        return $db->get_object('
            '.static::SQL_SELECT.'
            WHERE `preguntame`.`id` = "'.(int)$id.'"
            LIMIT 1;
        ', 'Preguntame');
    }

    public static function listing($offset, $limit)
    {
        global $db;

        return $db->get_results('
            '.static::SQL_SELECT.'
            ORDER BY `preguntame`.`start_at` DESC
            LIMIT '.(int)$offset.', '.(int)$limit.';
        ', 'Preguntame');
    }

    public static function count()
    {
        global $db;

        return $db->get_var('SELECT COUNT(*) FROM `preguntame`;');
    }

    public static function next()
    {
        global $db;

        return $db->get_results('
            '.static::SQL_SELECT.'
            WHERE (
                `preguntame`.`end_at` > NOW()
                AND `preguntame`.`enabled` = 1
            )
            ORDER BY `preguntame`.`end_at` ASC;
        ', 'Preguntame');
    }

    public static function previous()
    {
        global $db;

        return $db->get_results('
            '.static::SQL_SELECT.'
            WHERE (
                `preguntame`.`end_at` < NOW()
                AND `preguntame`.`enabled` = 1
            )
            ORDER BY `preguntame`.`end_at` DESC;
        ', 'Preguntame');
    }

    public function store()
    {
        global $db, $current_user;

        $this->title = $db->escape(strip_tags($this->title));
        $this->subtitle = $db->escape(strip_tags($this->subtitle));
        $this->link = $db->escape(strip_tags($this->link));

        $this->start_at = date('Y-m-d H:i:s', strtotime($this->start_at));
        $this->end_at = date('Y-m-d H:i:s', strtotime($this->end_at));

        $this->sponsored = (bool)$this->sponsored;
        $this->enabled = (bool)$this->enabled;
        $this->admin_id = (int)$current_user->user_id;

        $this->validate();

        if ($this->id) {
            $this->update();
        } else {
            $this->insert();
        }

        $this->storeImage();
    }

    private function validate()
    {
        if (empty($this->id) && empty($_FILES['image']['tmp_name'])) {
            throw new Exception('La imagen es obligatoria');
        }

        if ($this->start_at >= $this->end_at) {
            throw new Exception('Las fechas no son correctas');
        }
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
                `start_at` = "'.$this->start_at.'",
                `end_at` = "'.$this->end_at.'",
                `sponsored` = "'.$this->sponsored.'",
                `enabled` = "'.$this->enabled.'",
                `admin_id` = "'.$this->admin_id.'"
            WHERE `id` = "'.(int)$this->id.'"
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
                `start_at` = "'.$this->start_at.'",
                `end_at` = "'.$this->end_at.'",
                `sponsored` = "'.$this->sponsored.'",
                `enabled` = "'.$this->enabled.'",
                `admin_id` = "'.$this->admin_id.'";
        ');

        if ($response) {
            $this->id = $db->insert_id;
        }

        return $response;
    }

    private function storeImage()
    {
        if (empty($_FILES['image']['tmp_name'])) {
            return;
        }

        global $db;

        $media = new Upload('preguntame', $this->id, 0);

        if (($result = $media->from_temporal($_FILES['image'])) !== true) {
            throw new Exception($result);
        }
    }

    public function getMediaImage()
    {
        return Upload::get_url('preguntame', $this->id, 0, $this->media_date, $this->media_mime);
    }
}
