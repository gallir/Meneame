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

	static public function get_info($id = false) {
		global $db;

		if ($id == false || $id == self::$id) {
			if (! self::$id ) self::__init();
			return self::$info;
		} else {
			return $db->get_row("select * from subs where id = $id");
		}
	}

	static public function deploy($link, $full = false) {
		global $db;

		if (! self::$id ) self::__init();

		$delete_others = false;


		$me = self::get_status(self::$id, $link);
		if (! $full && $me->status == $link->status && $me->category == $link->category) {
			return;
		}
		if ($me->category < 0 ) $me->category = 0; // TODO: check later

		$db->transaction();

		$strict = false;

		if ($me->status != $link->status) {
			switch ($link->status) {
				case 'discard':
				case 'autodiscard':
				case 'abuse':
					$me->date = $link->sent_date;
					$me->status = $link->status;
					break;
				case 'queued':
					switch ($me->status) {
						case 'published':
						case 'discard':
						case 'autodiscard':
						case 'abuse':
							$me->date = $link->sent_date;
							break;
						default:
							$me->date = $link->date;
					}
					$me->status = $link->status;
					break;
				case 'published':
					// TODO: check, also editor for admins
					if (! $full) $strict = 1; // Change only to those that import the category (i.e. not to the parent)

					$me->karma = $link->karma;
					$me->status = $link->status;
					$me->date = $link->date;
					break;
				case 'metapublished':
					// TODO: check, also editor for admins
					$strict = 1; // We don't change the status of our parent, publication is local

					$me->date = $link->date;
					$me->karma = $link->karma;
					$me->status = 'published';
				default:
					$strict = 1;
					syslog(LOG_INFO, "Menéame, status unknown in link $link->id");
			}

		}

		if ($me->category != $link->category) {
			$delete_others = true;
			$me->category = $link->category;
			$me->origen = self::get_real_origen(self::$id, $link);
		}

		// TODO: ALERT: check what's would do in case on pubslish
		if ($strict) $id = self::$id;
		else $id = self::get_real_origen(self::$id, $link);

		if ($strict && $link->is_sub && $link->sub_id == $id) {
			$strict = 2;
		}
		$receivers = self::get_receivers($id, $me->category, $strict);


		if (! $full && ! $link->is_sub) {
			$my_conf = self::get_category_configuration(self::$id, $link->category);
			if ($me->category <= 0 || ! $my_conf->export) {
				// We don't have to export our links to other sites
				$receivers = array_intersect(array(self::$id), $receivers);
				$delete_others = false;
			}
		} else {
			$delete_others = true;
		}

		if ($receivers) {
			foreach ($receivers as $r) {
				$db->query("replace into sub_statuses (id, status, date, category, link, origen) values ($r, '$me->status', from_unixtime($me->date), $me->category, $me->link, $me->origen)");
				$db->query("update sub_statuses set karma=$link->karma where link = $link->id and (status != 'published' or id = $me->id)");
				//self::store_status($r, $me);
			}
		}

		// We delete those old statuses belong to the old category that were not changed before
		if ($delete_others) {
			$keep = array_merge(self::get_receivers($id, $me->category, false), self::get_receivers(self::get_real_origen(self::$id, $link), $me->category, false));
			$keep = array_unique($keep);
			$avoid = implode(',', $keep);
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
				$origin = $transition[$meta];
				if ($db->get_var("select count(*) from subs_copy where src = $origin and dst = $id") ){
					return $origin;
				}
			}
		}
		if ($link->sub_id > 0) return $link->sub_id;
		return $id;
	}

	// Receivers are categories from other sub sites that have importe as true
	static public function get_receivers($id, $category, $strict = false) {
		global $db;

		$receivers = array($id);
		if ($category > 0 && $strict != 2) {
			if ($strict) {
				$extra = "and (import or id = $id)";
			} else $extra = '';
			$receivers = array_merge($receivers, $db->get_col("select distinct id from sub_categories where category = $category $extra and enabled"));
		}

		// it serves for submnm
		if (! $strict) { // Don't "publish" to parents
			$receivers = array_merge($receivers, $db->get_col("select dst from subs_copy where src=$id"));
		}

		$receivers = array_unique($receivers);
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

	static public function can_edit($id) {
		global $current_user, $db;

		if (! $current_user->user_id) return false;

		if ($id > 0) {
			return $current_user->admin || $db->get_var("select owner from subs where id = $id") == $current_user->user_id;
		}

		if ($current_user->admin) return true;

		$n = $db->get_var("select count(*) from subs where owner = $current_user->user_id");
		
		return $n < 2 && ($current_user->user_level == 'blogger' || time() - $current_user->user_date > 86400*4*365);
	}

}
