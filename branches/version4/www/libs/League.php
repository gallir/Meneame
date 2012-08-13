<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once(mnminclude.'favorites.php');

class League {
	const TABLE = "league";

	var $id;
	var $per_page = 5;

	public function __construct($id = NULL)
	{
		global $globals;
		if (!is_null($id)) {
			$this->id = intval($id);
		}
		if (empty($globals['security_key'])) {
			$globals['security_key'] = get_security_key();
		}
	}

	public function get_pages($per_page=2)
	{
		global $db;

		$this->per_page = $per_page;

		$max = $db->get_row("SELECT count(*) as total FROM " . Match::TABLE . " WHERE league_id = $this->id");

		$total   = ceil($max->total/$per_page);
		$current = $total;
		if (!empty($_GET['page']) && is_numeric($_GET['page'])) {
   	 		$current = intval($_GET['page']);
		}
		$current = ($total - $current) * $per_page;

		$this->current = $current;
		$this->total   = $max->total;
		return $db->get_results("SELECT id FROM " . Match::TABLE . " WHERE league_id = $this->id ORDER BY id DESC LIMIT {$current}, $per_page");
	}

	public static function create(Array $data)
	{
		global $db;
		$name = $db->escape($data['name']);
		$db->query("INSERT into `" . self::TABLE . "`(name) VALUES('{$name}')");
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
		$db->query("UPDATE " . self::TABLE . " SET name='{$name}',shortname='{$shortname}' WHERE id = {$this->id}");
	}
}

/* vim:set noet ci pi sts=0 sw=4 ts=4: */
