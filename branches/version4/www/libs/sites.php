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

	static function __init($id = false) {
		global $globals, $db;

		if ($id > 0) {
			self::$id = $id;
		} elseif (!isset($globals['site_id'])) {
			$res = $db->get_row("select id, parent from subs where name = '".$globals['site_shortname']."'");
			self::$id = $res->id;
			self::$parent = $res->parent;
			if (! self::$id) echo "Error, site_shortname not found, check your global['site_shortname']: ". $globals['site_shortname'];
		} else {
			self::$id = $globals['site_id'];
		}

		if (self::$parent === false && self::$id > 0) {
			self::$parent = (int) $db->get_var("select parent from subs where id = ".self::$id);
		}
	}

	static public function my_id() {
		if (! self::$id ) self::__init();
		return self::$id;
	}

	static public function my_parent() {
		if (! self::$id ) self::__init();

		return self::$parent > 0 ? self::$parent : self::$id;
	}

	static public function get_info($id = false) {
		global $db;

		if (!$id) $id = self::$id;
		return $db->get_row("select * from subs where id = $id");
	}

	static public function deploy($link, $full = false) {
		global $db;

		if (! self::$id ) self::__init();

		$delete_others = false;

		$db->transaction();

		$me = self::get_status(self::$id, $link);
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
					if (! $full) $strict = true; // Change only to those that import the category (i.e. not to the parent)

					$me->karma = $link->karma;
					$me->status = $link->status;
					$me->date = $link->date;
					break;
				case 'metapublished':
					// TODO: check, also editor for admins
					$strict = true; // We don't change the status of our parent, publication is local

					$me->date = $link->date;
					$me->karma = $link->karma;
					$me->status = 'published';
				default:
					$strict = true;
					syslog(LOG_INFO, "Menéame, status unknown in link $link->id");
			}

		}

		if ($me->category != $link->category) {
			$delete_others = true;
			$me->category = $link->category;
		}

		$receivers = self::get_receivers($me->category, $strict);

		if (! $full) {
			$my_conf = self::get_category_configuration(self::$id, $link->category);
			if ($me->category == 0 || ! $my_conf->export) {
				// We don't have to export our links to other sites
				$receivers = array_intersect(array(self::$id), $receivers);
				$delete_others = false;
			}
		} else {
			$delete_others = true;
		}

		if ($receivers) {
			// echo "DEPLOY, $me->link cat: $me->category -> " . implode(', ', $receivers). " $me->status\n"; $delete_others = false;
			foreach ($receivers as $r) {
				self::store_status($r, $me);
			}
		}

		// We delete those old statues belong to the old category that were not changed before
		if ($delete_others) {
			$avoid = implode(',', $receivers);
			$db->query("delete from sub_statuses where link = $link->id and id not in ($avoid)");
		}

		$db->commit();
	}

	// Receivers are categories from other sub sites that have importe as true
	static public function get_receivers($category, $strict = false) {
		global $db;
		if (! self::$id ) self::__init();

		if ($strict) $extra = 'and (import or id = '.self::$id.')';
		else $extra = '';

		$receivers = $db->get_col("select distinct id from sub_categories where category = $category $extra and enabled");
		return array_unique($receivers);
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
			$status = new stdClass();
			$status->id = $id;
			$status->link = $link->id;
			$status->date = $link->date;
			$status->status = 'new';
			$status->category = 0;
			$status->origen = self::$id;
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

}
