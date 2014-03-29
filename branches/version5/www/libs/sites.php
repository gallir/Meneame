<?
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

	static function __init($id = false) {
		global $globals, $db;

		self::$info = false;
		if ($id > 0) {
			self::$id = $id;
		} elseif (!isset($globals['site_id'])) {
			if (empty($globals['site_shortname'])) {
				echo "Error, site_shortname not found, check your global['site_shortname']: ". $globals['site_shortname'];
			}
			self::$info = $db->get_row("select * from subs where name = '".$globals['site_shortname']."'");
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
			self::$info = $db->get_row("select * from subs where id = ".self::$id);
		}

		self::$parent = self::$info->parent;
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

	static public function my_parent() {
		if (! self::$id ) self::__init();

		return self::$parent > 0 ? self::$parent : self::$id;
	}

	static public function get_info($id = false, $force = false) {
		global $db;

		if ($id == false || $id == self::$id) {
			if (! self::$id || $force) self::__init();
			return self::$info;
		} else {
			return $db->get_row("select * from subs where id = $id");
		}
	}

	static public function deploy($link) {
		global $db;

		if (! self::$id ) self::__init();

		$delete_others = false;


		$me = self::get_status(self::$id, $link);
		if ($me->status == $link->status && $me->origen == $me->origen && $me->category == $link->category) {
			return;
		}

		$do_changed_id = $do_current = $do_all = $do_delete = false;

		$current = self::$id;
		$origen = $me->origen; //Already translated from category

		if ($me->category != $link->category || $me->origen != $link->sub_id) {
			if ($me->status != 'new') {
				$do_changed_id = true;  // Force to save all statuses
			} else {
				$me->status = $link->status;
			}
			$do_all = true;
			$me->category = $link->category > 0 ? $link->category : $me->category;
			$me->category = $me->category < 0 ? 0 : $me->category;
			$link->origen = $me->origen = $origen = self::get_real_origen($current, $link);
		} else { // If category or origen have changed, don't do the rest
			$me->date = $link->date;
			if ($me->status != $link->status) {
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
			$receivers = array_unique($receivers);
		}


		$db->transaction();
		if ($receivers) {
			foreach ($receivers as $r) {
				$new = $db->get_row("select * from sub_statuses where id = $r and link = $link->id");
				if (! $new) {
					$new = $me;
					$new->karma = 0;
				}
				$new->date = $me->date;
				$new->category = $me->category;
				$new->origen = $me->origen;
				if (! $do_changed_id) {  // Category or origen have changed, don't modify the status
					$new->status = $me->status;
				}
				if ($do_current) {
					$new->karma = $link->karma;
				}
				$db->query("replace into sub_statuses (id, status, date, category, link, origen, karma) values ($r, '$new->status', from_unixtime($new->date), $new->category, $new->link, $new->origen, $new->karma)");
			}
		}

		// We delete those old statuses belong to the old category that were not changed before
		if ($do_changed_id) {
			$avoid = implode(',', $receivers);
			$db->query("delete from sub_statuses where link = $link->id and id not in ($avoid)");
		}
		$db->commit();

	}

	// TODO: transient, for migration, edit/modify later
	static function get_real_origen($id, $link) {
		global $db; 

		if ($link->category > 0) {
			$transition = array('100' => 37, '101' => 39, '102' => 40, '103' => 38);
			$meta = $db->get_var("select category_parent from categories where category_id = $link->category");
			
			if ($meta && $transition[$meta] > 0) {
				return $transition[$meta];
			}
		}
		if ($link->sub_id > 0) return $link->sub_id;
		return $id;
	}

	// Receivers are categories from other sub sites that have importe as true
	static public function get_receivers($id) {
		global $db;

		$receivers = array();
		$receivers = array_merge($receivers, $db->get_col("select dst from subs_copy where src=$id"));
		return $receivers;
	}

	static public function get_children($site_id) {
		global $db;
		if (! self::$id ) self::__init();

		return $db->get_col("select id from subs where parent = $site_id");
	}

	static private function get_category_configuration($id, $category) {
		global $db;
		return $db->get_row("select * from sub_categories where id = $id and category = $category");
	}

	static private function get_status($id, $link) {
		global $db;

		$status = $db->get_row("select id, status, unix_timestamp(date) as date, category, link, origen, karma, 1 as found from sub_statuses where id = $id and link = $link->id");

		if (! $status) {
			// Create and object that can be later stored
			$origen = self::get_real_origen(self::$id, $link);
			$status = new stdClass();
			$status->id = $origen;
			$status->link = $link->id;
			$status->date = $link->date;
			$status->status = 'new';
			$status->category = -1;
			$status->origen = $origen;
			$status->karma = 0;
			$status->found = 0;
		}

		return $status;
	}

	static private function store_status($id, $s) {
		global $db;

		$db->query("replace into sub_statuses (id, status, date, category, link, origen, karma) values ($id, '$s->status', from_unixtime($s->date), $s->category, $s->link, $s->origen, $s->karma)");
	}

	static public function get_metas($ids = false) {
		global $globals, $db;

		if ($ids) {
			if (is_array($ids)) {
				$extra = 'and category in ('.implode(',', $ids).')';
			} else {
				$extra = 'and category = '.(int) $ids;
			}
		} else {
			$extra = '';
		}

		if ($globals['allowed_metas']) {
			$extra .= ' and category in ('.implode(',',$globals['allowed_metas']).')';
		}

		return $db->get_results("SELECT SQL_CACHE category as id, category_name as name, category_uri as uri FROM categories, sub_categories WHERE id = ".self::my_id()." AND category_id = category $extra AND category_parent = 0 ORDER BY category_name ASC");
	}

	static public function get_categories($parent = false) {
		global $globals, $db;

		if ($parent !== false) {
			$extra = 'and category_parent = '.(int) $parent;
		} else {
			$extra = 'and category_parent != 0';
		}

		if ($globals['allowed_metas']) {
			$extra .= ' and category_parent in ('.implode(',',$globals['allowed_metas']).')';
		}

		return $db->get_results("SELECT SQL_CACHE category as id, category_name as name, category_uri as uri FROM categories, sub_categories WHERE id = ".self::my_id()." AND category_id = category $extra ORDER BY category_name ASC");
	}

	static public function get_category_ids($parent = false) {
		global $globals, $db;

		if ($parent !== false) {
			$extra = 'and category_parent = '.(int) $parent;
		} else {
			$extra = 'and category_parent != 0';
		}

		if ($globals['allowed_metas']) {
			$extra .= ' and category_parent in ('.implode(',',$globals['allowed_metas']).')';
		}

		return $db->get_col("SELECT SQL_CACHE category FROM categories, sub_categories WHERE id = ".self::my_id()." AND category_id = category $extra ORDER BY category_id ASC");
	}

	static public function get_active_sites($children = false) {
		global $db;

		return $db->get_col("select id from subs where parent = 0 and enabled");
	}

	static public function can_edit($id = -1) {
		global $current_user, $db;

		if (! $current_user->user_id) return false;

		if ($id > 0) {
			return $current_user->admin || $db->get_var("select owner from subs where id = $id") == $current_user->user_id;
		}

		if ($current_user->admin) return true;

		$n = $db->get_var("select count(*) from subs where owner = $current_user->user_id");
		
		return $n < 3;
	}

}
