<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function check_stats($string) {
	global $globals, $current_user;
	if (preg_match('/^!top/', $string)) return do_top($string);
	if (preg_match('/^!statsu/', $string)) return do_statsu($string);
	if (preg_match('/^!stats2/', $string)) return do_stats2($string);
	if (preg_match('/^!stats3/', $string)) return do_stats3($string);
	if (preg_match('/^!stats1{0,1}/', $string)) return do_stats1($string);
	if (preg_match('/^!time/', $string)) return date(" d-m-Y H:i:s e");
	if (preg_match('/^!help/', $string)) return _('comandos') . ': http://meneame.wikispaces.com/Comandos';
	if (preg_match('/^!cabal/', $string)) return do_cabal($string);
	if (preg_match('/^!dariaunojo/', $string)) return do_ojo($string);
	if (preg_match('/^!wiki/', $string)) return 'wiki: http://meneame.wikispaces.com/';
	if (preg_match('/^!promote/', $string)) return 'http://' . get_server_name().$globals['base_url']. 'promote.php';
	if (preg_match('/^!hoygan/', $string)) return '¡HOYGAN! BISITEN http://' . get_server_name().$globals['base_url']. 'sneak.php?hoygan=1 GRASIAS DE HANTEMANO';
	if (preg_match('/^!webstats/', $string)) return 'http://www.quantcast.com/'.get_server_name();
	if (preg_match('/^!ignore/', $string)) return do_ignore($string);
	if (preg_match('/^!admins/', $string)) return do_admins($string);
	if (preg_match('/^!last/', $string)) return do_last($string);
	if (preg_match('/^!gs/', $string)) return do_fon_gs($string);
	return false;
}

function do_admins($string) {
	global $db, $current_user;

	if (! $current_user->admin) return false;
	foreach (array('god', 'admin') as $level) {
		$res = $db->get_col("select user_login from users where user_level = '$level' and user_id != $current_user->user_id order by user_login asc");
		if ($res) {
			$comment .= "<strong>$level</strong>: ";
			foreach ($res as $user) {
				$comment .= $user . ' ';
			}
		}
		$comment .= "<br>";
	}
	return $comment;
}

function do_last($string) {
	global $db, $current_user;

	if (! $current_user->admin) return false;
	$list = '';
	$res = $db->get_col("select user_login from users order by user_id desc limit 20");
	if ($res) {
		foreach ($res as $user) {
			$list .= 'http://'.get_server_name().get_user_uri($user) . ' ';
		}
	}
	return $list;
}

function do_stats1($string) {
	global $db;
	$comment = '<strong>'._('Estadísticas globales'). '</strong>. ';
	$comment .= _('usuarios activos') . ':&nbsp;' . $db->get_var("select count(*) from users where user_level not in ('disabled', 'autodisabled')") . ', ';
	$votes = (int) $db->get_var('select count(*) from votes') + (int) $db->get_var('select sum(votes_count) from votes_summary');
	$comment .= _('votos') . ':&nbsp;' . $votes . ', ';
	$comment .= _('artículos') . ':&nbsp;' . $db->get_var('select count(*) from links') . ', ';
	$comment .= _('publicados') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="published"') . ', ';
	$comment .= _('pendientes') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="queued"') . ', ';
	$comment .= _('descartados') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="discard"') . ', ';
	$comment .= _('comentarios') . ':&nbsp;' . $db->get_var('select count(*) from comments');
	return $comment;
}

function do_stats2($string) {
	global $db;
	$array = preg_split('/\s+/', $string);
	if (count($array) >= 2 && ((int)$array[1] > 0)) {
		$hours = min((int) $array[1], 72); // Up to 72 hours
	} else {
		$hours = 24;
	}

	$comment = '<strong>'._('Estadísticas')." $hours ";
	if ($hours > 1) $comment .= _('horas');
	else $comment .= _('hora');
	$comment .= '</strong>. ';

	$comment .= _('votos') . ':&nbsp;' . $db->get_var("select count(*) from votes where vote_type='links' and vote_date > date_sub(now(), interval $hours hour)") . ', ';
	$comment .= _('votos comentarios') . ':&nbsp;' . $db->get_var("select count(*) from votes where vote_type='comments' and vote_date > date_sub(now(), interval $hours hour)") . ', ';
	$comment .= _('votos notas') . ':&nbsp;' . $db->get_var("select count(*) from votes where vote_type='posts' and vote_date > date_sub(now(), interval $hours hour)") . ', ';
	$comment .= _('artículos') . ':&nbsp;' . $db->get_var("select count(*) from links where link_date > date_sub(now(), interval $hours hour)") . ', ';
	$comment .= _('publicados') . ':&nbsp;' . $db->get_var("select count(*) from links where link_status='published' and link_date > date_sub(now(), interval $hours hour)") . ', ';
	$comment .= _('descartados') . ':&nbsp;' . $db->get_var("select count(*) from links where link_status='discard' and link_date > date_sub(now(), interval $hours hour)") . ', ';
	$comment .= _('comentarios') . ':&nbsp;' . $db->get_var("select count(*) from logs where log_type = 'comment_new' and log_date > date_sub(now(), interval $hours hour)")  . ', ';
	$comment .= _('notas') . ':&nbsp;' . $db->get_var("select count(*) from logs where log_type = 'post_new' and log_date > date_sub(now(), interval $hours hour)")  . ', ';
	$comment .= _('usuarios nuevos') . ':&nbsp;' . $db->get_var("select count(*) from logs, users where log_type = 'user_new' and log_date > date_sub(now(), interval $hours hour) and user_id = log_ref_id and user_validated_date is not null");
	return $comment;
}

function do_stats3($string) {
	return do_stats2('!stats3 1');
}

function do_statsu($string) {
	global $db, $current_user;
	require_once(mnminclude.'user.php');
	$array = preg_split('/\s+/', $string);
	if (count($array) >= 2) {
		$user_login = $db->escape($array[1]);
		$user_id = $db->get_var("select user_id from users where user_login='$user_login'");
	}
	if (!$user_id > 0) { 
		$user_id = $current_user->user_id;
		$user_login = $current_user->user_login;
	}
	$user = new User();
	$user->id = $user_id;
	$user->read();
	$user->all_stats();
	
	$comment = '<strong>'._('Estadísticas de'). ' ' . $user_login. '</strong>. ';
	$comment .= _('karma') . ':&nbsp;' . $user->karma . ', ';
	if ($user->total_links > 1) {
		$comment .= _('entropía') . ':&nbsp;' . intval(($user->blogs() - 1) / ($user->total_links - 1) * 100) . '%, ';
	}
	$comment .= _('votos') . ':&nbsp;' . $user->total_votes . ', ';
	$comment .= _('artículos') . ':&nbsp;' . $user->total_links . ', ';
	$comment .= _('publicados') . ':&nbsp;' . $user->published_links . ', ';
	$comment .= _('pendientes') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="queued" and link_author='.$user_id) . ', ';
	$comment .= _('descartados') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="discard" and link_author='.$user_id) . ', ';
	$comment .= _('comentarios') . ':&nbsp;' . $user->total_comments;
	return $comment;
}

function do_top($string) {
	require_once(mnminclude.'link.php');
	global $db, $globals;
	$rank = "<strong>Top</strong> ";
	$sql = "select link_id from links where link_date > date_sub(now(), interval 4 day) and link_status='queued' order by link_karma desc limit 3";
	$result = $db->get_results($sql);
	foreach ($result as $linkid) {
		$link = new Link();
		$link->id = $linkid->link_id;
		$link->read_basic();
		$rank .= '<br/> ' . $link->get_permalink() . " ($link->karma)";
	}
	return $rank;
}

function do_cabal($string) {
	require_once('../libs/cabal.php');
	$i = rand(0, count($cabal_messages) -1);
	$comment = '<b>'. _('el cabal dice'). '</b>: <i>' . $cabal_messages[$i] . '</i>';
	return $comment;
}

function do_ojo($string) {
	require_once('./ojo.php');
	$i = rand(0, count($ojo_messages) -1);
	$comment = '<i>'._('Daría un ojo por saber cuánto es de leyenda y cuanto de verdad '). ' ' . text_to_html($ojo_messages[$i]) . '. <b>En serio.</b></i>';
	return $comment;
}

function do_ignore($string) {
	global $db, $current_user;
	require_once(mnminclude.'user.php');
	$array = preg_split('/\s+/', $string);
	if (count($array) >= 2) {
		$user_login = $db->escape($array[1]);
		$user_id = $db->get_var("select user_id from users where user_login='$user_login'");
		if ($user_id > 0 && $user_id != $current_user->user_id) {
			friend_insert($current_user->user_id, $user_id, -1);
			$comment = _('Usuario') . ' <em>' . htmlentities($array[1]). '</em> ' . _('agregado a la lista de ignorados');
		}
	} else {
		$users = $db->get_col("select user_login from users, friends where friend_type='manual' and friend_from=$current_user->user_id and friend_value < 0 and user_id = friend_to order by user_login asc");
		$comment = '<strong>' . _('Usuarios ignorados') . '</strong>: ';
		if ($users) {
			foreach ($users as $user) {
				$comment .= $user . ' ';
			}
		}
	}
	return $comment;
}

function do_fon_gs($string) {
	$array = preg_split('/\s+/', $string);
	if (count($array) >= 2 && preg_match('/https*:\/\/.+/',$array[1])) {
		if ($array[2]) {
			$tag = '&linkname='.urlencode($array[2]);
		} else {
			$tag = '';
		}
		$url = 'http://fon.gs/create.php?url='.urlencode($array[1]).$tag;
		$res = get_url($url);
		if ($res && $res['content']) {
			return $res['content'];
		} else {
			return _('<strong>Error</strong> en la comunicación con fon.gs, inténtalo otra vez');
		}
	} else {
		return _('<strong>Error</strong>, el segundo argumento debe ser un URL válido: <em>!gs url [nombre]</em>');
	}
}

?>
