<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Sponsor
{
    public $id;
    public $external;
    public $banner;
    public $banner_mobile;
    public $css;
    public $created_at;
    public $start_at;
    public $end_at;
    public $enabled;
    public $link;

    const SQL_SELECT = '
        SELECT `sponsors`.*,  `link_title` `title`
        FROM `sponsors`
        LEFT JOIN `links` ON (`links`.`link_id` = `sponsors`.`link`)
    ';

    public static function getById($id)
    {
        global $db;

        return $db->get_object('
            '.static::SQL_SELECT.'
            WHERE `sponsors`.`id` = "'.(int)$id.'"
            LIMIT 1;
        ', 'Sponsor');
    }

    public static function getByLinkId($id)
    {
        global $db;

        return $db->get_object('
            '.static::SQL_SELECT.'
            WHERE `sponsors`.`link` = "'.(int)$id.'"
            LIMIT 1;
        ', 'Sponsor');
    }

    public static function listing($offset, $limit)
    {
        global $db;

        return $db->get_results('
            '.static::SQL_SELECT.'
            ORDER BY `sponsors`.`start_at` DESC
            LIMIT '.(int)$offset.', '.(int)$limit.';
        ', 'Sponsor');
    }

    public static function count()
    {
        global $db;

        return $db->get_var('SELECT COUNT(*) FROM `sponsors`;');
    }

    public static function getCurrent()
    {
        global $db;

        return $db->get_object('
            '.static::SQL_SELECT.'
            WHERE (
                `sponsors`.`enabled` = 1
                AND `sponsors`.`start_at` < NOW()
                AND `sponsors`.`end_at` > NOW()
                AND `sponsors`.`link` = `links`.`link_id`
            )
            LIMIT 1;
        ', 'Sponsor');
    }

    public function store()
    {
        global $db, $current_user;

        if (!$db->get_col('SELECT `link_id` FROM `links` WHERE `link_id` = "'.(int)$this->link.'" LIMIT 1;')) {
            throw new Exception('El envío asociado al identificado no existe');
        }

        $this->external = $db->escape(strip_tags($this->external));
        $this->css = $db->escape(strip_tags($this->css));

        $this->start_at = date('Y-m-d H:i:s', strtotime($this->start_at));
        $this->end_at = date('Y-m-d H:i:s', strtotime($this->end_at));

        $this->enabled = (bool)$this->enabled;
        $this->link = (int)$this->link;

        if (empty($this->admin_id)) {
            $this->admin_id = (int)$current_user->user_id;
        }

        $this->validate();

        if ($this->id) {
            $this->update();
        } else {
            $this->insert();
        }

        $this->storeBanners();
    }

    private function validate()
    {
        if ($this->start_at >= $this->end_at) {
            throw new Exception('Las fechas no son correctas');
        }
    }

    private function update()
    {
        global $db;

        return $db->query('
            UPDATE `sponsors`
            SET
                `external` = "'.$this->external.'",
                `css` = "'.$this->css.'",
                `start_at` = "'.$this->start_at.'",
                `end_at` = "'.$this->end_at.'",
                `enabled` = "'.$this->enabled.'",
                `link` = "'.$this->link.'",
                `admin_id` = "'.$this->admin_id.'"
            WHERE `id` = "'.(int)$this->id.'"
            LIMIT 1;
        ');
    }

    private function insert()
    {
        global $db;

        $response = $db->query('
            INSERT INTO `sponsors`
            SET
                `external` = "'.$this->external.'",
                `css` = "'.$this->css.'",
                `start_at` = "'.$this->start_at.'",
                `end_at` = "'.$this->end_at.'",
                `enabled` = "'.$this->enabled.'",
                `link` = "'.$this->link.'",
                `admin_id` = "'.$this->admin_id.'";
        ');

        if ($response) {
            $this->id = $db->insert_id;
        }

        return $response;
    }

    private function storeBanners()
    {
        $changes = [];

        if (!empty($_FILES['banner']['tmp_name'])) {
            $changes[] = '`banner` = "'.$this->storeBanner($_FILES['banner'], 0).'"';
        }

        if (!empty($_FILES['banner_mobile']['tmp_name'])) {
            $changes[] = '`banner_mobile` = "'.$this->storeBanner($_FILES['banner_mobile'], 1).'"';
        }

        if (empty($changes)) {
            return;
        }

        global $db;

        return $db->query('
            UPDATE `sponsors`
            SET '.implode(', ', $changes).'
            WHERE `id` = "'.(int)$this->id.'"
            LIMIT 1;
        ');
    }

    private function storeBanner($file, $version)
    {
        $media = new Upload('sponsors', $this->id, $version);
        $media->access = 'public';

        if (($result = $media->from_temporal($file)) !== true) {
            throw new Exception($result);
        }

        return $this->getBannerUrl($version);
    }

    public function getBannerUrl($version = 0)
    {
        return Upload::get_url('sponsors', $this->id, $version);
    }
}
