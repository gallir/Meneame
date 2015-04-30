<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once(mnminclude.'favorites.php');

class Team {
	const TABLE = "league_teams";
	var $id;

	public function create(Array $data)
	{
		global $db;
		$name	  = $db->escape($data['name']);
		$shortname = $db->escape($data['shortname']);
		$db->query("INSERT into `" . self::TABLE . "`(shortname, name) VALUES('{$shortname}', '{$name}')");
	}


	public function __construct($id = NULL)
	{
		if (!is_null($id)) {
			$this->id = intval($id);
		}
	}

	public function read()
	{
		global $db;
		$id = $this->id;
		if(($result = $db->get_row("SELECT * FROM " . self::TABLE . " WHERE id = $id"))) {
			foreach(get_object_vars($result) as $var => $value) $this->$var = $value;
			return true;
		}
		return false;
	}

	public function store()
	{
		global $db;
		$name = $db->escape($this->name);
		$shortname = $db->escape($this->shortname);
		$db->query("UPDATE " . self::TABLE . " SET name='{$name}', shortname='{$shortname}' WHERE id = {$this->id}");
	}

}

/* vim:set noet ci pi sts=0 sw=4 ts=4: */
