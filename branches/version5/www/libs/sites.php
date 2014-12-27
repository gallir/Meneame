<?php
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class SitesMgr {
	static private $id = 0;
	static private $parent = false;
	static private $info = false;

	const PREFERENCES_KEY = 'sub_preferences_';
	const SQL_BASIC = "SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type='sub_logo' and media.id = subs.id and media.version = 0) ";

	static public $extended_properties = array(
			'no_link' => 0,
			'intro_max_len' => 550,
			'intro_min_len' => 20,
			'intro_lines' => 0,
			'no_anti_spam' => 0,
			'allow_local_links' => 0,
			'allow_paragraphs' => 0,
			'allow_images' => 0,
			'rules' => '',
			'message' => '',
	);

	static public $extra_extended_properties = array(
			'twitter_page' => '',
			'twitter_consumer_key' => '',
			'twitter_consumer_secret' => '',
			'twitter_token' => '',
			'twitter_token_secret' => '',
			'facebook_page' => '',
			'facebook_key' => '',
			'facebook_secret' => '',
			'facebook_token' => '',
	);

	static function __init($id = false) {
		global $globals, $db;

		self::$info = false;
		if ($id > 0) {
			self::$id = $id;
		} elseif (!isset($globals['site_id'])) {
			if (empty($globals['site_shortname'])) {
				echo "Error, site_shortname not found, check your global['site_shortname']: ". $globals['site_shortname'];
			}
			self::$info = $db->get_row(SitesMgr::SQL_BASIC."where subs.name = '".$globals['site_shortname']."'");
			if (self::$info) {
				self::$id = self::$info->id;
			} else {
				self::$id = 0;
				return;
			}
		} else {
			self::$id = $globals['site_id'];
		}

		if (self::$info == false) {
			self::$info = $db->get_row(SitesMgr::SQL_BASIC."where subs.id = ".self::$id);
		}

		self::$parent = self::$info->created_from;
		if (self::$id > 0) {
			$db->query('set @site_id = '.self::$id);
		}
	}

	static public function my_id() {
		if (! self::$id ) self::__init();
		return self::$id;
	}

	static public function is_owner() {
		global $current_user;

		if (! self::$id ) self::__init();

		return $current_user->user_id > 0 && ($current_user->admin || self::$info->owner == $current_user->user_id);
	}

	static public function get_info($id = false, $force = false) {
		global $db, $globals;

		if ($id == false || $id == self::$id) {
			if (! self::$id || $force) self::__init($id);
			return self::$info;
		} else {
			return $db->get_row(SitesMgr::SQL_BASIC."where subs.id = $id");
		}
	}

	static public function get_name($id = false, $force = false) {
		global $db;

		if ($id == false || $id == self::$id) {
			if (! self::$id || $force) self::__init();
			return self::$info->name;
		} else {
			return $db->get_var("select name from subs where id = $id");
		}
	}

	static public function get_id($name) {
		global $db;

		$name = $db->escape($name);
		return $db->get_var("select id from subs where name = '$name'");
	}

	static public function deploy($link) {
		global $db;

		if (! self::$id ) self::__init();

		$delete_others = false;


		$me = self::get_status(self::$id, $link);
		if ($me->status == $link->status && $me->origen == $link->sub_id && empty($link->sub_changed)) {
			return true;
		}

		$do_changed_id = $do_current = $do_all = $do_delete = $status_changed = $copy_link_karma = false;

		$current = self::$id;
		$origen = $me->origen;

		if ($me->origen != $link->sub_id || ! empty($link->sub_changed)) {
			$do_changed_id = true;  // Force to save all statuses
			if ($me->status == 'new') {
				$me->status = $link->status;
			}
			$do_all = true;
			$me->origen = $origen = self::get_real_origen($current, $link);
		} else { // If origen has changed, don't do the rest
			$me->date = $link->date;
			if ($me->status != $link->status) {
				$status_changed = true;
				switch ($link->status) {
					case 'discard':
					case 'autodiscard':
					case 'abuse':
						$me->date = $link->sent_date;
						$do_all = true;
						break;
					case 'queued':
						$me->date = $link->sent_date;
						switch ($me->status) {
							case 'published':
								$do_current = true;
								break;
							default:
								$do_all = true;
						}
						break;
					case 'published':
					case 'metapublished':
						$do_current = true;
						$me->karma = $link->karma;
						$me->date = $link->date;
						$copy_link_karma = true;
						break;
					default:
						$do_current = true;
						syslog(LOG_INFO, "Menéame, status unknown in link $link->id");
				}
				$me->status = $link->status;
			}
		}

		$receivers = array();
		if ($do_current) {
			$receivers[] = $current;
		} elseif ($do_all) {
			$receivers[] = $origen;
			$receivers = array_merge($receivers, self::get_receivers($origen));

			// If the post has been copied/promoted to another site and its status has changed (tipically, to discarded)
			if ($status_changed && $link->votes + $link->negatives > 1) {
				$copies = $db->get_col("select id from sub_statuses where link = $link->id");
				$receivers = array_merge($receivers, $copies);
			}

			$receivers = array_unique($receivers);
		}

		$r = true; // Result of operations, for commit/rollback
		$db->transaction();
		if ($receivers) {
			foreach ($receivers as $r) {
				$new = $db->get_row("select * from sub_statuses where id = $r and link = $link->id");
				if (! $new) {
					$new = $me;
					$new->karma = 0;
				}
				$new->date = $me->date;
				$new->origen = $me->origen;
				if (! $do_changed_id) {  // Origen has changed, don't modify the status
					$new->status = $me->status;
				}
				if ($do_current &&  // changed status to published or from published to queued
						($copy_link_karma || round($new->karma) == 0 || $link->karma < $new->karma )) {
						// If karma was never updated (new published) or the link karma is negative/smaller
					$new->karma = $link->karma;
				}
				$r = $db->query("replace into sub_statuses (id, status, date, link, origen, karma) values ($r, '$new->status', from_unixtime($new->date), $new->link, $new->origen, $new->karma)");
				if (! $r) {
					$db->rollback();
					syslog(LOG_INFO, "Failed transaction replace sub_statuses: ".$link->get_permalink());
					return false;
				}
			}
		}

		// We delete those old statuses belong to the old sub that were not changed before
		if ($r && $do_changed_id) {
			$avoid = implode(',', $receivers);
			$r = $db->query("delete from sub_statuses where link = $link->id and id not in ($avoid)");
		}

		if (! $r) {
			$db->rollback();
			syslog(LOG_INFO, "Failed transaction in deploy: ".$link->get_permalink());
			return false;
		}

		return $db->commit();
	}

	// TODO: transient, for migration, edit/modify later
	static function get_real_origen($id, $link) {
		global $db;

		if ($link->sub_id > 0) return $link->sub_id;
		return $id;
	}

	// Receivers are categories from other sub sites that have importe as true
	static public function get_receivers($id = false) {
		global $db;

		if ($id == false) {
			$id = self::my_id();
		}
		$receivers = array();
		$receivers = array_merge($receivers, $db->get_col("select dst from subs_copy where src=$id"));
		return $receivers;
	}

	static public function get_senders($id = false) {
		global $db;

		if ($id == false) {
			$id = self::my_id();
		}
		$senders = array();
		$senders = array_merge($senders, $db->get_col("select src from subs_copy where dst=$id"));
		return $senders;
	}

	static private function get_status($id, $link) {
		global $db;

		$status = $db->get_row("select id, status, unix_timestamp(date) as date, link, origen, karma, 1 as found from sub_statuses where id = $id and link = $link->id");

		if (! $status) {
			// Create and object that can be later stored
			$origen = self::get_real_origen(self::$id, $link);
			$status = new stdClass();

			// Retrieve original status in any sub, if it exists
			$original_status = $db->get_var("select status from sub_statuses where link=$link->id and id=origen");
			if ($original_status) {
				$status->status = $original_status;
			} else {
				$status->status = 'new';
			}

			$status->id = $origen;
			$status->link = $link->id;
			$status->date = $link->date;
			$status->origen = $origen;
			$status->karma = 0;
			$status->found = 0;
		}

		return $status;
	}

	static public function store($s) { // Store a sub_statuses, as is.
		global $db;

		if (is_numeric($s->date)) {
			$date = "from_unixtime($s->date)";
		} else {
			$date = "'$s->date'";
		}

		return $db->query("replace into sub_statuses (id, status, date, link, origen, karma) values ($s->id, '$s->status', $date, $s->link, $s->origen, $s->karma)");
	}

	static public function get_sub_subs($id = false) {
		global $globals, $db;

		if ($id == false) {
			$id = self::my_id();
		}

		return $db->get_results("select subs.* from subs, subs_copy where dst = $id and id = src");
	}

	static public function get_sub_subs_ids($id = false) {
		global $globals, $db;

		if ($id == false) {
			$id = self::my_id();
		}

		return $db->get_col("select id from subs, subs_copy where dst = $id and id = src");
	}

	static public function get_active_sites($children = false) {
		global $db;

		return $db->get_col("select id from subs where parent = 0 and enabled");
	}

	static public function can_edit($id = -1) {
		global $current_user, $db;

		if (! $current_user->user_id) return false;
		if ($current_user->admin) return true;

		if ($id > 0) {
			return $db->get_var("select owner from subs where id = $id") == $current_user->user_id;
		}


		$n = $db->get_var("select count(*) from subs where owner = $current_user->user_id");

		return $n < 10 && time() - $current_user->user_date > 86400*10;
	}

	static public function my_parent() {
		// Get original site
		if (! self::$id ) self::__init();

		if (self::$parent > 0) return self::$parent;
		else return self::$id;
	}

	static public function get_subscriptions($user) {
		global $db;

		return $db->get_results("select subs.* from subs, prefs where pref_user_id = $user and pref_key = 'sub_follow' and subs.id = pref_value order by name asc");
	}

	static public function store_extended_properties($id = false, &$prefs) {
		if ($id == false) {
			$id = self::my_id();
		}
		$dict = array();
		$defaults = array_merge(self::$extended_properties, self::$extra_extended_properties);
		foreach ($prefs as $k => $v) {
			if ($v !== '' && isset($defaults[$k]) && $defaults[$k] != $v ) {
				switch ($k) {
					case 'rules':
					case 'message':
						$dict[$k] = clean_text_with_tags($v, 0, false, 300);
						break;
					default:
						if (isset($defaults[$k]) && is_int($defaults[$k])) {
							$dict[$k] = intval($v);
						} else {
							$dict[$k] = mb_substr(clean_input_string($v), 0, 140);
						}
				}
			}
		}

		$key = self::PREFERENCES_KEY.$id;
		$a = new Annotation($key);

		if (!empty($dict)) {
			$json = json_encode($dict);
			$a->text = $json;
			return $a->store();
		}
		return $a->delete();
	}

	static public function get_extended_properties($id = false) {
		static $properties = array(), $last_id = false;
		if ($id == false) {
			$id = self::my_id();
		}

		if (! empty($properties) && $last_id == $id) return $properties;

		$last_id = $id;
		$properties = self::$extended_properties;

		$key = self::PREFERENCES_KEY.$id;
		$a = new Annotation($key);
		if ($a->read() && !empty($a->text)) {
			$res = json_decode($a->text, true); // We use associative array
			if ($res) {
				foreach ($res as $k => $v) {
					$properties[$k] = $v;
				}
			}
		}
		return $properties;
	}

}
