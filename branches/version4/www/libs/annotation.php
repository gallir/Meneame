<?
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
		if ($key) $this->key = $key;
		return;
	}

	function store($expire = false) {
		global $db;

		if (empty($this->key)) return false;

		if (! $expire) $expire = 'null';
		else $expire = "FROM_UNIXTIME($expire)";
		$key = $db->escape($this->key);
		$text = $db->escape($this->text);
		$db->query("REPLACE INTO annotations (annotation_key, annotation_text, annotation_expire) VALUES ('$key', '$text', $expire)");
	}

	function read($key = false) {
		global $db;

		if ($key) $this->key = $key;
		if (empty($this->key)) return false;

		$key =	$db->escape($this->key);
		if(($record = $db->get_row("SELECT UNIX_TIMESTAMP(annotation_time) as time, UNIX_TIMESTAMP(annotation_expire) as expire, annotation_text as text FROM annotations WHERE annotation_key = '$key' and (annotation_expire is null or annotation_expire > now())"))) {
			$this->time = $record->time;
			$this->expire = $record->expire;
			$this->text = $record->text;
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
		global $db;

	}
}
