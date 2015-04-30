<?php
// The source code packaged with this file is Free Software, Copyright (C) 2008 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Annotation {
	var $key = false;
	var $time;
	var $text = '';

	function Annotation($key = false) {
		$this->key = $key;
		return;
	}

	static function from_db($key) {
		global $db;

		$key = $db->escape($key);
		if(($result = $db->get_object("SELECT UNIX_TIMESTAMP(annotation_time) as time, UNIX_TIMESTAMP(annotation_expire) as expire, annotation_text as text FROM annotations WHERE annotation_key = '$key' and (annotation_expire is null or annotation_expire > now())", 'Annotation'))) {
			return $result;
		}
		return false;

	}

	static function get_text($key) {
		$annotation = Annotation::from_db($key);
		if ($annotation) {
			return $annotation->text;
		}
		return '';
	}

	static function store_text($key, $text, $expire = false) {
		$annotation = new Annotation($key);
		$annotation->text = $text;
		return $annotation->store($expire);
	}

	function delete() {
		global $db;

		if (empty($this->key)) return false;

		$key = $db->escape($this->key);
		return $db->query("DELETE FROM annotations WHERE annotation_key = '$key'");
	}

	function store($expire = false) {
		global $db;

		if (empty($this->key)) return false;

		if (! $expire) $expire = 'null';
		else $expire = "FROM_UNIXTIME($expire)";
		$key = $db->escape($this->key);
		$text = $db->escape($this->text);
		return $db->query("REPLACE INTO annotations (annotation_key, annotation_text, annotation_expire) VALUES ('$key', '$text', $expire)");
	}

	function read($key = false) {
		global $db;

		if ($key) $this->key = $key;

		$key =	$db->escape($this->key);
		if(($result = $db->get_row("SELECT UNIX_TIMESTAMP(annotation_time) as time, UNIX_TIMESTAMP(annotation_expire) as expire, annotation_text as text FROM annotations WHERE annotation_key = '$key' and (annotation_expire is null or annotation_expire > now())"))) {
			foreach(get_object_vars($result) as $var => $value) $this->$var = $value;
			return true;
		}
		return false;
	}

	function append($text) {
		if ($text) {
			$this->read();
			$this->text .= $text;
			$this->store();
		}
	}

	function optimize() {
		// For compatibility with old versions
		global $db;
	}
}
