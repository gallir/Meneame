<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once(mnminclude.'favorites.php');

class Match {
	const TABLE = "league_matches";
	const VOTES = "league_votes";
	
	const REVOTE_TIME = 300;
	public $id;

	public function create(Array $data)
	{
		global $db;
		$league		= intval($data['league']);
		$local		= intval($data['local']);
		$visitor	= intval($data['visitor']);
		$date		= date('Y-m-d H:i:s', strtotime($data['date']));
		$vote_starts = date('Y-m-d H:i:s', strtotime($data['vote_starts'])); 
		$db->query("INSERT into `" . self::TABLE . "`(league_id, local, visitor, date, vote_starts) 
				VALUES('{$league}', '{$local}', '{$visitor}', '{$date}', '{$vote_starts}')");
	}


	public function __construct($id = NULL)
	{
		if (!is_null($id)) {
			$this->id = intval($id);
		}
	}

	public function read_basic()
	{
		global $db;
		$id = $this->id;
		if(($result = $db->get_row("SELECT * FROM " . self::TABLE . " WHERE id = $id"))) {
			foreach(get_object_vars($result) as $var => $value) $this->$var = $value;
			return true;
		}
		return false;
	}

	public function is_votable()
	{
		$now = time();
		return strtotime($this->vote_starts) < $now  && strtotime($this->date) > $now;
	}

	public function insert_vote($vote)
	{
		global $current_user, $db, $globals;
		$columns = array("tied", "local", "visitor");
		$vote    = (int)$vote;
		$userid  = $current_user->user_id;

		$check = $db->get_row("SELECT value, date FROM " . self::VOTES . " WHERE match_id = {$this->id} AND user_id = {$userid}");
		if (!empty($check)) {
			if ($check->value == $vote) {
				// nothing to do
				return;
			}
			if (strtotime($check->date) < time() - self::REVOTE_TIME) {
				throw new Exception(_("no se puede cambiar el voto"));
			}
			$column1 = "votes_{$columns[$check->value]}";
			$column2 = "votes_{$columns[$vote]}";
			$db->transaction();
			// update vote
			$db->query("UPDATE " . self::VOTES . " SET value = $vote WHERE user_id = $userid and match_id = {$this->id}");
			// update summary
			$db->query("UPDATE " . self::TABLE . " SET $column1 = $column1 - 1, $column2 = $column2 + 1 WHERE id = {$this->id}");
			$db->commit();
			return;
		}
		$column  = "votes_{$columns[$vote]}";
		$db->transaction();
		$db->query("INSERT INTO " . self::VOTES . " values({$this->id}, {$userid}, {$vote}, {$globals['user_ip_int']}, null)");
		$db->query("UPDATE " . self::TABLE . " SET $column = $column + 1 WHERE id = {$this->id}");
		$db->commit();
	}

	public function get_votes_box()
	{
		ob_start();
		$GLOBALS['globals']['match_id'] = $this->id;
		echo '<div id="voters-container-' . $this->id . '">';
		require mnmpath . '/backend/league_meneos.php';
		echo '</div>';
		return ob_get_clean();
	}

	public function print_summary()
	{
		Haanga::Load('league/match.tpl', array('match' => $this));
	}

	public function json_votes_info() {
		global $db, $current_user;
		$summary = $db->get_row("SELECT 
			votes_tied as tied, votes_local as local, votes_visitor visitor, v.value as voted
			FROM " . self::TABLE . " 
			LEFT JOIN " . self::VOTES . " v ON (v.match_id = id and v.user_id = {$current_user->user_id})
			WHERE id = {$this->id}");
		print json_encode($summary);
		die;
	}

	public function read()
	{
		global $db, $current_user, $globals;

		$id = $this->id;
		$sql = "SELECT
			m.*,
			t1.name as local_name,
			t1.shortname as local_short,
			t2.name as visitor_name,
			t2.shortname as visitor_short,
			" . ($current_user->user_id > 0 ? 'v.value as vote, v.date as vote_date,' : ''). "
			l.name as liga
		FROM 
			" . Match::TABLE ." m
		INNER JOIN " . Team::TABLE ." t1 ON (t1.id = m.local)
		INNER JOIN " . Team::TABLE ." t2 ON (t2.id = m.visitor)
		INNER JOIN " . League::TABLE ." l ON (l.id = m.league_id)
		";

		if ($current_user->user_id > 0) {
			$sql  .= " LEFT JOIN " . self::VOTES . " v ON (v.match_id = m.id AND v.user_id = {$current_user->user_id})";
		}

		$sql .= " WHERE m.id = {$id}";

		if(($result = $db->get_row($sql))) {
			foreach(get_object_vars($result) as $var => $value) {
				$this->$var = $value;
				if (is_numeric($value)) {
					$this->$var += 0;
				}
			}
			$this->total_votes = $this->votes_local + $this->votes_visitor + $this->votes_tied;
			$this->ts_date  = strtotime($this->date);
			$this->ts_vote_starts = strtotime($this->vote_starts);
			if ($this->ts_date > time()) {
				$this->result = null;
			} else {
				$this->result = $this->score_local == $this->score_visitor ? 0 : ($this->score_local > $this->visitor_name ? 1 : 2);
			}
			$globals['vote_values'] = array(_("empate"), $this->local_name, $this->visitor_name);
			return true;
		}
		return false;
	}

	public function store()
	{
		global $db;
		$league		= intval($this->league_id);
		$local		= intval($this->local);
		$visitor	= intval($this->visitor);
		$goalvis    = intval($this->score_visitor);
		$goalloc    = intval($this->score_local);
		$date		= date('Y-m-d H:i:s', strtotime($this->date));
		$vote_starts = date('Y-m-d H:i:s', strtotime($this->vote_starts)); 
		$db->query("UPDATE " . self::TABLE . " SET 
			local = $local, visitor = $visitor, league_id = $league,
			date = '$date', vote_starts = '$vote_starts',
			score_local = '$goalloc', score_visitor='$goalvis'
			WHERE id = {$this->id}");
	}

}

/* vim:set noet ci pi sts=0 sw=4 ts=4: */
