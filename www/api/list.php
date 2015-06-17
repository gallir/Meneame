<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


// Don't check the user is logged
$globals['no_auth'] = true;
// Use the alternate server for api, if it exists
$globals['alternate_db_server'] = 'api';

$globals['max_load'] = 6;
include('../config.php');

// Free connections fast
ini_set('default_socket_timeout', 2);
$db->connect_timeout = 2;



if(!empty($_REQUEST['rows'])) {
	$rows = min(2000, intval($_REQUEST['rows']));
} else {
	$rows = 100;
}


// Compatibility with the old "search" query string
if($_REQUEST['search']) $_REQUEST['q'] = $_REQUEST['search'];

// Sub
if($_REQUEST['sub']) {
	$sub_id =  SitesMgr::get_id(mb_substr($_REQUEST['sub'], 20));
	if ($sub_id) {
		SitesMgr::__init($sub_id);
	} else {
		die;
	}
} else {
	$site_id = SitesMgr::my_id();
}

$site_info = SitesMgr::get_info();

if ($site_info->sub && $site_info->owner > 0) {
	$site_info->name = $site_info->name;
}

if (! empty($_REQUEST['id']) && intval($_REQUEST['id']) > 0) {
	//
	// Comments
	//
	$id = intval($_REQUEST['id']);
	$comments = $db->get_col("SELECT comment_id FROM comments WHERE comment_link_id = $id ORDER BY comment_date DESC");
	if ($comments) {
		list_all('comments', $comments, $title, $site_info);
	}

} else {
	//
	// Links
	//
	if(!empty($_REQUEST['time'])) {
		/////
		// Prepare for times
		/////
		if(!($time = check_integer('time')))
			die;
		$sql = "SELECT link_id, link_votes as votes FROM links, sub_statuses WHERE id = $site_id AND link_id = link AND ";
		if ($time < 0 || $time > 86400*5) $time = 86400*2;
		$from = time()-$time;
		$sql .= "date > FROM_UNIXTIME($from) AND ";
		$sql .= "status = 'published' ORDER BY link_votes DESC LIMIT $rows";
		$title = $site_info->name.': '.sprintf(_('más votadas en %s'), txt_time_diff($from));
	} elseif (!empty($_REQUEST['favorites'])) {
		/////
		// users' favorites
		/////
		$user_id = guess_user_id($_REQUEST['favorites']);
		$sql = "SELECT link_id FROM links, favorites WHERE favorite_user_id=$user_id AND favorite_type='link' AND favorite_link_id=link_id ORDER BY favorite_date DESC limit $rows";
		$user_login = $db->get_var("select user_login from users where user_id=$user_id");
		$title = $site_info->name.': '.sprintf(_('favoritas de %s'), $user_login);
	} elseif (!empty($_REQUEST['voted_by'])) {
		// voted links
		$user_id = guess_user_id($_REQUEST['voted_by']);
		if (! $user_id > 0) die;
		$sql = "SELECT vote_link_id FROM votes WHERE vote_type='links' and vote_user_id = $user_id and vote_value > 0 ORDER BY vote_date DESC limit $rows";
		$user_login = $db->get_var("select user_login from users where user_id=$user_id");
		$title = $site_info->name.': '.sprintf(_('votadas por %s'), $user_login);
	} elseif (!empty($_REQUEST['friends_of'])) {
		/////
		// users' friends
		/////
		$user_id = guess_user_id($_REQUEST['friends_of']);
		$sql = "SELECT link_id FROM links, friends WHERE friend_type='manual' and friend_from = $user_id and friend_to=link_author and friend_value > 0 and link_status in ('queued', 'published') ORDER BY link_date DESC limit $rows";
		$user_login = $db->get_var("select user_login from users where user_id=$user_id");
		$title = $site_info->name.': '.sprintf(_('amigos de %s'), $user_login);
	} elseif (!empty($_REQUEST['sent_by'])) {
		/////
		// sent links
		/////
		$user_id = guess_user_id($_REQUEST['sent_by']);
		$sql = "SELECT link_id FROM links WHERE link_author=$user_id and link_votes > 0 ORDER BY link_id DESC limit $rows";
		$user_login = $db->get_var("select user_login from users where user_id=$user_id");
		$title = $site_info->name.': '.sprintf(_('noticias de %s'), $user_login);
	} elseif (isset($_REQUEST['active'])) {
		$title = $site_info->name.': '._('más activas');
		$top = new Annotation('top-actives-'.$globals['site_shortname']);
		if ($top->read()) {
			$links = explode(',',$top->text);
		}
	} elseif (isset($_REQUEST['popular'])) {
		$min_date = date("Y-m-d H:i:00", $globals['now'] - 129600); // 36 hours
		// The order is not exactly the votes
		// but a time-decreasing function applied to the number of votes
		$sql = "select link_id, (link_votes-link_negatives*2)*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.8/129600) as value from links, sub_statuses where id = ".SitesMgr::my_id()." AND link_id = link AND status='published' and date > '$min_date' order by value desc limit 25";
	} elseif (isset($_REQUEST['top_visited'])) {
		$min_date = date("Y-m-d H:i:00", $globals['now'] - 172800); // 48 hours
		// The order is not exactly the votes
		// but a time-decreasing function applied to the number of votes
		$sql = "select link_id, counter*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.5/172800) as value from links, link_clicks, sub_statuses where sub_statuses.id = ".SitesMgr::my_id()." AND link_id = link AND status='published' and date > '$min_date' and link_clicks.id = link order by value desc limit 25";
	} else {
		/////
		// All the others
		/////
		// The link_status to search
		if(!empty($_REQUEST['status'])) {
			$status = $db->escape(clean_input_string(trim($_REQUEST['status'])));
		} else {
			// By default it searches on all
			if($_REQUEST['q']) {
				$status = 'all';
				include(mnminclude.'search.php');
				$search_ids = do_search(true);
				if ($search_ids['ids']) {
					$search = ' link_id in (' . implode(',', $search_ids['ids']) . ')';
				}
			} else {
				$status = 'published';
			}
		}

		switch ($status) {
			case 'published':
				$order_field = 'date';
				$link_date = 'date';
				$title = $site_info->name.': '._('publicadas');
				break;
			case 'queued':
				$title = $site_info->name.': '._('en cola');
				$order_field = 'date';
				$link_date = "date";
				$home = "/queue";
				break;
			case 'all':
			case 'all_local':
			default:
				$title = $site_info->name.': '._('todas');
				$order_field = 'date';
				$link_date = "date";
				break;
		}


		$from_where = '';
		if ($_REQUEST['q']) {
			$order_field = 'link_date'; // Because sub_statuses is not used
			if($search) {
				$from_where = "FROM links WHERE $search ";
			} else {
				$from_where = "FROM links WHERE false "; // Force to return empty set
			}
			$title = $site_info->name . ": " . htmlspecialchars(strip_tags($_REQUEST['q']));
		} elseif ($status == 'all' || $status == 'all_local') {
			$from_where = "FROM links, sub_statuses WHERE id = $site_id AND status in ('published', 'queued') AND link_id = link";
		} elseif (($uid=check_integer('subs'))) {
			$subs = $db->get_col("SELECT pref_value FROM prefs WHERE pref_user_id = $uid and pref_key = 'sub_follow' order by pref_value LIMIT 1000");
			$user_login = $db->get_var("select user_login from users where user_id=$uid");
			$title .= " -$user_login-";
			if ($subs) {
				$subs = implode(',', $subs);
				$from_where = "FROM sub_statuses, links WHERE sub_statuses.id in ($subs) AND status='$status' AND link_id = link";
			}
		}
		if (empty($from_where)) {
			$from_where = "FROM sub_statuses, links WHERE id = $site_id AND status='$status' AND link_id = link";
		}

		$order_by = " ORDER BY $order_field DESC ";
		$sql = "SELECT link_id $from_where $order_by LIMIT $rows";
	}

	if (! empty($sql)) {
		$links = $db->get_col($sql);
	}

	if ($links) {
		list_all('links', $links, $false, $site_info);
	}
}

exit(0);

function list_all($what, $ids, $title, $site_info) {
	global $globals;

	header('Content-Type: application/json; charset=utf-8');

	$json = array();
	if ($title) {
		$json['title'] = $title;
	}
	$json['url'] = $globals['scheme'].'//'.get_server_name().$globals['base_url'].$site_info->name;
	// $json['logo'] = $globals['scheme'].'//'.get_static_server_name().$globals['base_url'].'img/mnm/eli-rss.png';

	$json['objects'] = array();
	foreach($ids as $id) {
		switch ($what) {
			case 'links':
				$obj = get_link($id);
				break;
			case 'comments':
				$obj = get_comment($id);
				break;
			default:
				return false;
		}
		if ($obj) {
			$json['objects'][] = $obj;
		}
	}
	echo @json_encode($json);
}

function get_comment($id) {
		global $globals;

		$comment = Comment::from_db($id);
		if (!$comment) return false;
		$obj = array();
		$obj['id'] = (int) $comment->id;
		$obj['date'] = (int) $comment->date;
		$obj['votes'] = intval($comment->votes);
		$obj['karma'] = intval($comment->karma);
		$obj['link_id'] = (int) $comment->link_id;
		$obj['order'] = (int) $comment->order;
		$obj['user'] = $comment->username;
		//$obj['content'] = $comment->to_html($comment->content);
		$obj['content'] = html_entity_decode($comment->content);
		return $obj;
}

function get_link($link_id) {
		global $globals;

		$link = Link::from_db($link_id);
		if (!$link) return false;

		$obj = array();
		$obj['id'] = (int) $link->id;
		$obj['permalink'] = $link->get_permalink();
		$obj['go'] = $globals['scheme'].'//'.get_server_name().$globals['base_url'].'go?id='.$link->id;
		$obj['url'] = $link->url;
		$obj['from'] = parse_url($link->url, PHP_URL_HOST);
		$obj['sub'] = $link->sub_name;
		$obj['status'] = $link->status;
		$obj['user'] = $link->username;
		$obj['clicks'] = (int) $link->clicks;
		$obj['votes'] = intval($link->votes+$link->anonymous);
		$obj['negatives'] = (int) $link->negatives;
		$obj['karma'] = intval($link->karma);
		$obj['comments'] = (int) $link->comments;
		$obj['title'] = html_entity_decode($link->title);
		$obj['tags'] = html_entity_decode($link->tags);
		$obj['sent_date'] = (int) $link->sent_date;
		if ($link->status == 'published') {
			$obj['date'] = (int) $link->date;
		} else {
			$obj['date'] = (int) $link->sent_date;
		}
		// $obj['content'] = $link->to_html($link->content);
		$obj['content'] = html_entity_decode($link->content);
		if($link->has_thumb()) {
			$obj['thumb'] = $globals['scheme'].'//'.get_server_name().$globals['base_url'].'backend/media?type=link&id='.$link->id;
		}
		return $obj;
}
