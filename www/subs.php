<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//				http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

if (empty($routes)) die; // Don't allow to be called bypassing dispatcher

if (isset($_GET['all']) || array_key_exists('q', (array)$_GET)) {
	$option = 2; // Show all subs
} elseif (! $current_user->user_id || isset($_GET['active']))  {
	$option = 1; // Show active
} elseif (isset($_GET['subscribed'])) {
	$option = 0; // Show suscribed
} else {
	$option = count(SitesMgr::get_subscriptions($current_user->user_id)) > 0 ? 0 : 1;
}

if (!empty($_GET['q'])) {
	$q = trim(preg_replace('/[^a-zA-Zá-úÁ-ÚñÑ0-9\s]/', '', strip_tags($_GET['q'])));
} else {
	$q = null;
}

if ($q) {
	$option = 2;
}

$char_selected = $chars = false; // User for index by first letter

do_header(_("subs menéame"), 'm/');

echo '<div id="sidebar" class="sidebar-with-section">';
	do_banner_right();
	do_banner_promotions();
echo '</div>';

switch ($option) {
	case 0:
		$all_subs = SitesMgr::get_subscriptions($current_user->user_id);

		break;
	case 1:
		$sql = 'SELECT subs.*, user_id, user_login, user_avatar, COUNT(*) AS c FROM subs LEFT JOIN users ON (user_id = owner), sub_statuses WHERE date > DATE_SUB(NOW(), INTERVAL 5 DAY) AND subs.id = sub_statuses.id AND sub_statuses.id = sub_statuses.origen AND sub_statuses.status = "published" AND subs.sub = 1 AND subs.enabled = 1 GROUP BY subs.id ORDER BY c DESC LIMIT 50;';
		$all_subs = $db->get_results($sql);

		break;
	default:
		$chars = $db->get_col('SELECT DISTINCT(LEFT(UCASE(name), 1)) letter FROM subs WHERE enabled = 1 ORDER BY letter ASC;');

		if (!$q && !empty($_GET['c'])) {
			$char_selected = preg_replace('/[^A-Z]/', '', substr($_GET['c'], 0, 1));
		}

		$extra = 'subs.enabled = 1 AND ';

		if ($q) {
			$q_sql = '%'.str_replace(' ', '%', $q).'%';
			$extra .= '(subs.name LIKE "'.$q_sql.'" OR subs.name_long LIKE "'.$q_sql.'") AND ';
		} elseif ($char_selected) {
			$extra .= 'subs.name LIKE "'.$char_selected.'%" AND ';
		}

		$rows = $db->get_var('SELECT COUNT(*) FROM subs WHERE '.$extra.' subs.sub = 1 AND created_from = '.SitesMgr::my_id());

		$page_size = 20;
		$page = get_current_page();
		$offset = ($page -1 ) * $page_size;

		$sql = 'SELECT subs.*, user_id, user_login, user_avatar FROM subs, users WHERE '.$extra.' subs.sub = 1 AND created_from = "'.SitesMgr::my_id().'" AND user_id = owner ORDER BY name ASC LIMIT '.$offset.', '.$page_size.';';
		$all_subs = $db->get_results($sql);
}

$ids_subs = array_map(function($row) {
	return (int)$row->id;
}, $all_subs);

$followers = $db->get_results('SELECT subs.id, COUNT(*) AS c FROM subs, prefs WHERE subs.id IN ('.implode(',', $ids_subs).') AND pref_key = "sub_follow" AND subs.id = pref_value GROUP BY subs.id ORDER BY c DESC;');

$subs = array();

foreach ($all_subs as $sub) {
	if (!$sub->enabled) {
		continue;
	}
	$sub->site_info = SitesMgr::get_info($sub->id);

	// Check if the sub has a logo and calculate the width
	if ($sub->site_info->media_id > 0 && $sub->site_info->media_dim1 > 0 && $sub->site_info->media_dim2 > 0) {
		$r = $sub->site_info->media_dim1/$sub->site_info->media_dim2;
		if ( $globals['mobile']) {
			$sub->site_info->logo_height = $globals['media_sublogo_height_mobile'];
		} else {
			$sub->site_info->logo_height = $globals['media_sublogo_height'];
		}
		$sub->site_info->logo_width = round($r * $sub->site_info->logo_height);
		$sub->site_info->logo_url = Upload::get_cache_relative_dir($sub->site_info->id).'/media_thumb-sub_logo-'.$sub->site_info->id.'.'.$sub->site_info->media_extension.'?'.$sub->site_info->media_date;
	}

	$sub->followers = 0;

	foreach ($followers as $row) {
		if ($sub->id == $row->id) {
			$sub->followers = $row->c;
		}
	}

	$subs[] = $sub;
}

$can_edit = (SitesMgr::my_id() == 1 && SitesMgr::can_edit(0));


// Official subs

$official_subs = array();

if ($globals['official_subs']) {

	foreach (array_keys($globals['official_subs']) as $sub_name) {
		$sub = SitesMgr::get_info(SitesMgr::get_id($sub_name));
		$sub->extra_info = $globals['official_subs'][$sub_name];
		$official_subs[] = $sub;
	}

	$ids_official_subs = array_map(function($row) {
		return (int)$row->id;
	}, $official_subs);

	$followers_official_subs = $db->get_results('SELECT subs.id, COUNT(*) AS c FROM subs, prefs WHERE subs.id IN ('.implode(',', $ids_official_subs).') AND pref_key = "sub_follow" AND subs.id = pref_value GROUP BY subs.id ORDER BY c DESC;');

	foreach ($official_subs as $sub) {

		foreach ($followers_official_subs as $row) {
			if ($sub->id == $row->id) {
				$sub->followers = $row->c;
			}
		}
	}

}

// Recommended subs

$recommended_subs = array();

if ($globals['recommended_subs']) {

	foreach (array_keys($globals['recommended_subs']) as $sub_name) {
		$sub = SitesMgr::get_info(SitesMgr::get_id($sub_name));
		$sub->extra_info = $globals['recommended_subs'][$sub_name];
		$recommended_subs[] = $sub;
	}

	$ids_recommended_subs = array_map(function($row) {
		return (int)$row->id;
	}, $recommended_subs);

	$followers_recommended_subs = $db->get_results('SELECT subs.id, COUNT(*) AS c FROM subs, prefs WHERE subs.id IN ('.implode(',', $ids_recommended_subs).') AND pref_key = "sub_follow" AND subs.id = pref_value GROUP BY subs.id ORDER BY c DESC;');

	foreach ($recommended_subs as $sub) {

		foreach ($followers_recommended_subs as $row) {
			if ($sub->id == $row->id) {
				$sub->followers = $row->c;
			}
		}
	}

}

Haanga::Load('subs.html', compact(
	'title', 'subs', 'chars', 'char_selected', 'option', 'rows',
	'page_size', 'q', 'can_edit', 'official_subs', 'recommended_subs'
));

do_footer();
