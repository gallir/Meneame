<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//              http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1-mobile.php');


$offset=(get_current_page()-1)*$page_size;

$url_args = $globals['path'];

$login = clean_input_string($url_args[1]);

if(empty($login)){
	if ($current_user->user_id > 0) {
		header('Location: ' . get_user_uri($current_user->user_login));
		die;
	} else {
		header('Location: '.$globals['base_url']);
		die;
	}
}
$user=new User();
$user->username = $db->escape($login);
if(!$user->read()) {
	not_found();
}


do_header($login);

echo '<div id="singlewrap">'."\n";

$url_login = urlencode($login);
do_profile();

echo '</div>'."\n";

do_footer();

function do_profile() {
	global $user, $current_user, $login, $db, $globals;

	if(!empty($user->url)) {
		if ($user->karma < 10) $nofollow = 'rel="nofollow"';
		if (!preg_match('/^http/', $user->url)) $url = 'http://'.$user->url;
		else $url = $user->url;
	}

	echo '<fieldset><legend>';
	echo _('información personal');
	if($login===$current_user->user_login) {
		echo ' (<a href="'.$globals['base_url'].'profile.php">'._('modificar').'</a>)';
	} elseif ($current_user->user_level == 'god') {
		echo ' (<a href="'.$globals['base_url'].'profile.php?login='.urlencode($login).'">'._('modificar').'</a>)';
	}
	echo '</legend>';
	// Avatar
	echo '<img class="thumbnail" src="'.get_avatar_url($user->id, $user->avatar, 80).'" width="80" height="80" alt="'.$user->username.'" title="avatar" />';



	echo '<dl>';	
	if(!empty($user->username)) {
		echo '<dt>'._('usuario').':</dt><dd>';
		if (!empty($url)) {
			echo '<a href="'.$url.'" '.$nofollow.'>'.$user->username.'</a>';
		} else {
			echo $user->username;
		}
		// Print user detailed info
		if ($login===$current_user->user_login || $current_user->user_level == 'god') {
			echo " (" . _('id'). ": <em>$user->id</em>)";
			echo " (<em>$user->level</em>)";
		}
		echo '</dd>';
	}

	if(!empty($user->names)) {
		echo '<dt>'._('nombre').':</dt><dd>'.$user->names.'</dd>';
	}

	if(!empty($url)) {
		echo '<dt>'._('sitio web').':</dt><dd><a href="'.$url.'" '.$nofollow.'>'.$url.'</a></dd>';
	}
	echo '<dt>'._('desde').':</dt><dd>'.get_date_time($user->date).'</dd>';
	echo '<dt>'._('karma').':</dt><dd>'.$user->karma;
	echo '</dd>';

	echo '<dt>'._('ranking').':</dt><dd>#'.$user->ranking().'</dd>';

	$user->all_stats();
	echo '<dt>'._('enviadas').':</dt><dd>'.$user->total_links.'</dd>';
	if ($user->total_links > 0 && $user->published_links > 0) {
		$percent = intval($user->published_links/$user->total_links*100);
	} else {
		$percent = 0;
	}
	if ($user->total_links > 1) {
		$entropy = intval(($user->blogs() - 1) / ($user->total_links - 1) * 100);
		echo '<dt><em>'._('entropía').'</em>:</dt><dd>'.$entropy.'%</dd>';
	}
	echo '<dt>'._('publicadas').':</dt><dd>'.$user->published_links.' ('.$percent.'%)</dd>';
	echo '<dt>'._('comentarios').':</dt><dd>'.$user->total_comments.'</dd>';
	echo '<dt>'._('notas').':</dt><dd>'.$user->total_posts.'</dd>';
	echo '<dt>'._('votos').':</dt><dd>'.$user->total_votes.'</dd>';

	echo '</dl>';

	if ($current_user->user_id == $user->id) {
		echo '<div style="margin-top: 20px" align="center">';
		print_oauth_icons($_REQUEST['return']);
		echo '</div>'."\n";
	}

	echo '</fieldset>';
}

?>
