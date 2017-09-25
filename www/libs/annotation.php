<?php
// The source code packaged with this file is Free Software, Copyright (C) 2008 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//        http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Annotation
{
    const SQL_SELECT = '
            SELECT UNIX_TIMESTAMP(annotation_time) AS `time`, UNIX_TIMESTAMP(annotation_expire) AS `expire`, annotation_text AS `text`
            FROM annotations
            WHERE (
                annotation_key = "%s"
                AND (
                    annotation_expire IS NULL
                    OR annotation_expire > NOW()
                )
            );
    ';

    public $key = '';
    public $time = 0;
    public $text = '';

    public static function queryFromKey($key)
    {
        global $db;

        return sprintf(self::SQL_SELECT, $db->escape($key));
    }

    public static function from_db($key)
    {
        global $db;

        return $db->get_object(static::queryFromKey($key), 'Annotation');
    }

    public function __construct($key = false)
    {
        $this->key = $key;
    }

    public static function get_text($key)
    {
        if ($annotation = static::from_db($key)) {
            return $annotation->text;
        }

        return '';
    }

    public static function store_text($key, $text, $expire = false)
    {
        $annotation = new self($key);
        $annotation->text = $text;

        return $annotation->store($expire);
    }

    public function delete()
    {
        global $db;

        if (empty($this->key)) {
            return false;
        }

        return $db->query('
            DELETE FROM annotations
            WHERE annotation_key = "'.$db->escape($this->key).'";
        ');
    }

    public function store($expire = false)
    {
        global $db;

        if (empty($this->key)) {
            return false;
        }

        if ($expire) {
            $expire = 'FROM_UNIXTIME('.(int)$expire.')';
        } else {
            $expire = 'NULL';
        }

        return $db->query('
            REPLACE INTO annotations (annotation_key, annotation_text, annotation_expire)
            VALUES ("'.$db->escape($this->key).'", "'.$db->escape($this->text).'", '.$expire.');
        ');
    }

    public function read($key = false)
    {
        global $db;

        if ($key) {
            $this->key = $key;
        }

        if (!($result = $db->get_row(static::queryFromKey($this->key)))) {
            return false;
        }

        foreach (get_object_vars($result) as $var => $value) {
            $this->$var = $value;
        }

        return true;
    }

    public function append($text)
    {
        if (empty($text)) {
            return;
        }

        $this->read();
        $this->text .= $text;
        $this->store();
    }

    public function optimize()
    {
        // For compatibility with old versions
    }
}
