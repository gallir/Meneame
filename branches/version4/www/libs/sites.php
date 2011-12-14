<?
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class SitesMgr {
	static $id = 0;

	static function __init() {
		global $globals, $db;

		if (!isset($globals['site_id'])) {
			self::$id = $db->get_var("select id from subs where name = '".$globals['site_shortname']."'");
			if (! self::$id) echo "Error, site_shortname not found: ". $globals['site_shortname'];
		} else {
			self::$id = $globals['site_id'];
		}

	}

	static function deploy($link) {
		global $db;

		if (! self::$id ) self::__init();

		$copy = false;

		$db->transaction();

		$me = self::get_status(self::$id, $link);
		if ($me->status != $link->status) {
			switch ($link->status) {
				case 'discard':
				case 'autodiscard':
				case 'abuse':
					$copy = true;
					$me->date = $link->sent_date;
					$me->status = $link->status;
					break;
				case 'queued':
					$copy = true;
					switch ($me->status) {
						case 'published':
						case 'discard':
						case 'autodiscard':
						case 'abuse':
							$me->date = $link->sent_date;
							$me->status = $link->status;
							break;
						default:
							$me->date = time();
					}
					$me->status = $link->status;
					break;
				case 'published':
					$copy = true;
					$me->karma = $link->karma;
					$me->status = $link->status;
					$me->date = $link->date;
					break;
				case 'metapublished':
					$copy = false;
					$me->date = $link->date;
					$me->karma = $link->karma;
					$me->status = 'published';
				default:
					$copy = false;
					syslog(LOG_INFO, "Menéame, status unknown in link $link->id");
			}

		}

		$old_category = false;
		if ($me->category != $link->category) {
			if (! $me->category == 0 ) {
				$old_category = $me->category;
			}
			$copy = true;
			$me->category = $link->category;
		}

		if ($copy || $old_category) {
			self::store_status($me->id, $me);
			$my_conf = self::get_category_configuration(self::$id, $link->category);
			if ($me->category == 0 || ! $my_conf->export) {
				// We don't have to export our links to other sites
				$db->commit();
				return;
			}
		}

		$receivers = self::get_receivers($me);
		if ($copy) {
			if ($receivers) {
				foreach ($receivers as $r) {
					self::store_status($r, $me);
				}
			}
		}

		// We delete those old statues belong to the old category that were not changed before
		if ($old_category > 0) {
			$receivers[] = self::$id;
			$avoid = implode(',', $receivers);
			$db->query("delete from sub_statuses where link = $link->id and id not in ($avoid)");
		}

		$db->commit();

	}

	// Receivers are categories from other sub sites that have importe as true
	static function get_receivers($s) {
		global $db;

		return $db->get_col("select id from sub_categories where category = $s->category and import and enabled");
	}

	static function get_category_configuration($id, $category) {
		global $db;
		return $db->get_row("select * from sub_categories where id = $id and category = $category");
	}

	static function get_status($id, $link) {
		global $db;

		$status = $db->get_row("select id, status, unix_timestamp(date) as date, category, link, origen, karma from sub_statuses where id = $id and link = $link->id");

		if (! $status) {
			// Create and object that can be later stores
			$status = new stdClass();
			$status->id = $id;
			$status->link = $link->id;
			$status->date = $link->date;
			$status->category = 0;
			$status->origen = self::$id;
			$status->karma = 0;
		}

		return $status;
	}

	static function store_status($id, $s) {
		global $db;

		$db->query("replace into sub_statuses (id, status, date, category, link, origen, karma) values ($id, '$s->status', from_unixtime($s->date), $s->category, $s->link, $s->origen, $s->karma)");
	}
}
