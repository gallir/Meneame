<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//              http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
include(mnminclude.'comment.php');
include(mnminclude.'user.php');

$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = true;


if (!empty($globals['base_user_url']) && !empty($_SERVER['PATH_INFO'])) {
	$url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
	array_shift($url_args); // The first element is always a "/"
	$_REQUEST['login'] = clean_input_string($url_args[0]);
	$_REQUEST['view'] = $url_args[1];
} else {
	$_REQUEST['login'] = clean_input_string($_REQUEST['login']);
	if (!empty($globals['base_user_url']) && !empty($_REQUEST['login'])) {
		header('Location: ' . get_user_uri($_REQUEST['login'], clean_input_string($_REQUEST['view'])));
		die;
	}
}

$login = $_REQUEST['login'];
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

// Enable user AdSense
// do_user_ad: 0 = noad, > 0: probability n/100
// 100 if the user is the current one
if($globals['external_user_ads'] && !empty($user->adcode)) {
    $globals['user_adcode'] = $user->adcode;
	if ($current_user->user_id == $user->id || $current_user->user_level=='god') $globals['do_user_ad']  = 100; 
	else $globals['do_user_ad'] = $user->karma * 2;
}

$view = clean_input_string($_REQUEST['view']);
if(empty($view)) $view = 'profile';
do_header(_('perfil de usuario'). ': ' . $login);
do_navbar('<a href="'.$globals['base_url'].'topusers.php">'._('usuarios') . '</a> &#187; ' . $user->username);
echo '<div id="genericform-contents">'."\n";

// Tabbed navigation
if (strlen($user->names) > 0) {
	$display_name = $user->names;
}
else {
	$display_name = $user->username;
}
echo '<div class="topheading"><h2>'.$display_name.'</h2></div>'."\n";

$url_login = urlencode($login);
switch ($view) {
	case 'history':
		do_user_tabs(2, $login);
		do_history();
		do_pages($rows, $page_size);
		break;
	case 'commented':
		do_user_tabs(3, $login);
		do_commented();
		do_pages($rows, $page_size, false);
		break;
	case 'shaken':
		do_user_tabs(4, $login);
		do_shaken();
		do_pages($rows, $page_size);
		break;
	case 'preferred':
		do_user_tabs(5, $login);
		do_voters_preferred();
		break;
	case 'profile':
	default:
		do_user_tabs(1, $login);
		do_profile();
		break;
}

echo '</div>'."\n";

do_footer();

//echo '<div id="contents">';
//echo '</div>';



function do_profile() {
	global $user, $current_user, $login, $db, $globals;


	echo '<fieldset><legend>';
	echo _('información personal');
	if($login===$current_user->user_login) {
		echo ' (<a href="'.$globals['base_url'].'profile.php">'._('modificar').'</a>)';
	} elseif ($current_user->user_level == 'god') {
		echo ' (<a href="'.$globals['base_url'].'profile.php?login='.urlencode($login).'">'._('modificar').'</a>)';
	}
	echo '</legend>';
	echo '<img class="gravatar-sub" src="'.get_avatar_url($user->id, $user->avatar, 80).'" width="80" height="80" alt="'.$user->username.'" title="avatar" />';

	echo '<dl>';	
	if(!empty($user->username)) {
		echo '<dt>'._('usuario').':</dt><dd>'.$user->username;
		if ($login===$current_user->user_login || $current_user->user_level == 'god') {
			echo " (" . _('id'). ": <em>$user->id</em>)";
			echo " (<em>$user->level</em>)";
		}
		if($current_user->user_level=='god') {
			echo " (" . _('registro'). ": <em>$user->username_register</em>)";
		}

		echo '</dd>';
	}
	if(!empty($user->names))
		echo '<dt>'._('nombre').':</dt><dd>'.$user->names.'</dd>';
	if(!empty($user->url)) {
		if (!preg_match('/^http/', $user->url)) $url = 'http://'.$user->url;
		else $url = $user->url;
		echo '<dt>'._('sitio web').':</dt><dd><a href="'.$url.'"  rel="nofollow">'.$url.'</a></dd>';
	}
	echo '<dt>'._('desde').':</dt><dd>'.get_date($user->date).'</dd>';

	if($current_user->user_level=='god') {
		echo '<dt>'._('email').':</dt><dd>'.$user->email. ' (' .  _('registro'). ": <em>$user->email_register</em>)</dd>";
		if(!empty($user->adcode)) {
			echo '<dt>'._('AdSense').':</dt><dd>'.$user->adcode.'</dd>';
		}
	}

	echo '<dt>'._('karma').':</dt><dd>'.$user->karma.'</dd>';
	echo '</dl>';
	if ($user->id == $current_user->user_id || $current_user->user_level=='god' ) {
		echo '<div id="karma-details">(<a href="javascript:get_votes(\'get_karma_numbers.php\',\''.$user->id.'\',\'karma-details\',0,\''.$user->username.'\')" title="'._('detalles').'">'._('detalle cálculo karma').'</a>)</div>';
	}
	echo '</fieldset>';


	$user->all_stats();
	echo '<fieldset><legend>'._('estadísticas de meneos').'</legend><dl>';

        echo '<dt>'._('noticias enviadas').':</dt><dd>'.$user->total_links.'</dd>';
		if ($user->total_links > 0 && $user->published_links > 0) {
			$percent = intval($user->published_links/$user->total_links*100);

		} else {
			$percent = 0;
		}
		if ($user->total_links > 1) {
			$entropy = intval(($user->blogs() - 1) / ($user->total_links - 1) * 100);
        	echo '<dt><em>'._('entropía').'</em>:</dt><dd>'.$entropy.'%</dd>';
		}
        echo '<dt>'._('noticias publicadas').':</dt><dd>'.$user->published_links.' ('.$percent.'%)</dd>';
        echo '<dt>'._('comentarios').':</dt><dd>'.$user->total_comments.'</dd>';
        echo '<dt>'._('número de votos').':</dt><dd>'.$user->total_votes.'</dd>';
        echo '<dt>'._('votos de publicadas').':</dt><dd>'.$user->published_votes.'</dd>';

	echo '</dl></fieldset>';
}


function do_history () {
	global $db, $rows, $user, $offset, $page_size, $globals;

	$link = new Link;
	$rows = $db->get_var("SELECT count(*) FROM links WHERE link_author=$user->id AND link_votes > 0");
	$links = $db->get_col("SELECT link_id FROM links WHERE link_author=$user->id AND link_votes > 0 ORDER BY link_date DESC LIMIT $offset,$page_size");
	if ($links) {
		echo '<a href="'.$globals['base_url'].'link_bookmark.php?user_id='.$user->id.'&amp;option=history" title="'._('formato Mozilla bookmarks').'">'._('exportar').'</a>';
		foreach($links as $link_id) {
			$link->id=$link_id;
			$link->read();
			$link->print_summary('short');
		}
	}
}

function do_shaken () {
	global $db, $rows, $user, $offset, $page_size, $globals;

	$link = new Link;
	$rows = $db->get_var("SELECT count(*) FROM links, votes WHERE vote_type='links' and vote_user_id=$user->id AND vote_link_id=link_id and vote_value > 0");
	$links = $db->get_col("SELECT link_id FROM links, votes WHERE vote_type='links' and vote_user_id=$user->id AND vote_link_id=link_id  and vote_value > 0 ORDER BY link_date DESC LIMIT $offset,$page_size");
	if ($links) {
		echo '<a href="'.$globals['base_url'].'link_bookmark.php?user_id='.$user->id.'&amp;option=shaken" title="'._('formato Mozilla bookmarks').'">'._('exportar').'</a>';
		foreach($links as $link_id) {
			$link->id=$link_id;
			$link->read();
			$link->print_summary('short');
		}
	}
}


function do_commented () {
	global $db, $rows, $user, $offset, $page_size, $globals;

	$link = new Link;
	$comment = new Comment;
	$rows = $db->get_var("SELECT count(*) FROM comments WHERE comment_user_id=$user->id");
	$comments = $db->get_results("SELECT comment_id, link_id FROM comments, links WHERE comment_user_id=$user->id and link_id=comment_link_id ORDER BY comment_date desc LIMIT $offset,$page_size");
	if ($comments) {
		echo '<a href="'.$globals['base_url'].'link_bookmark.php?user_id='.$user->id.'&amp;option=commented" title="'._('formato Mozilla bookmarks').'">'._('exportar').'</a>';
		foreach ($comments as $dbcomment) {
			$link->id=$dbcomment->link_id;
			$comment->id = $dbcomment->comment_id;
			if ($last_link != $link->id) {
				$link->read();
				echo '<h4>';
				echo '<a href="'.$link->get_permalink().'">'. $link->title. '</a>';
				echo ' ['.$link->comments.']';
				echo '</h4>';
				$last_link = $link->id;
			}
			$comment->read();
			echo '<ol class="comments-list">';
			$comment->print_summary($link, 2000, false);
			echo "</ol>\n";
		}
	}
}

function do_voters_preferred() {
	global $db, $user;

	echo '<fieldset style="width: 45%; display: block; float: left;"><legend>';
	echo _('autores preferidos');
	echo '</legend>';
	$prefered_id = $user->id;
	$prefered_type = 'friends';
	echo '<div id="friends-container">'. "\n";
	require('backend/get_prefered_bars.php');
	echo '</div>'. "\n";
	echo '</fieldset>'. "\n";


	echo '<fieldset style="width: 45%; display: block; float: right;"><legend>';
	echo _('votado por');
	echo '</legend>';
	$prefered_id = $user->id;
	$prefered_type = 'voters';
	echo '<div id="voters-container">'. "\n";
	require('backend/get_prefered_bars.php');
	echo '</div>'. "\n";
	echo '</fieldset>'. "\n";

	echo '<br clear="all" />';

	// Show first numbers of the addresss if the user has god privileges
	if ($current_user->user_level == 'god' &&
			$user->level != 'god' && $user->level != 'admin' ) { // tops and admins know each other for sure, keep privacy
		$addresses = $db->get_results("select distinct INET_NTOA(vote_ip_int) as ip from votes where vote_type='links' and vote_user_id = $user->id and vote_date > date_sub(now(), interval 60 day) order by vote_date desc limit 20");

		// Try with comments
		if (! $addresses) {
			$addresses = $db->get_results("select distinct comment_ip as ip from comments where comment_user_id = $user->id and comment_date > date_sub(now(), interval 60 day) order by comment_date desc limit 20");
		}

		// Not addresses to show
		if (! $addresses) {
			return;
		}

		$clone_counter = 0;
		echo '<fieldset><legend>'._('últimas direcciones IP').'</legend>';
		echo '<ol>';
		foreach ($addresses as $dbaddress) {
			$ip_pattern = preg_replace('/\.[0-9]+$/', '', $dbaddress->ip);
			echo '<li>'. $ip_pattern . ': <span id="clone-container-'.$clone_counter.'"><!--<a href="javascript:get_votes(\'ip_clones.php\',\''.$ip_pattern.'\',\'clone-container-'.$clone_counter.'\',0,'.$user->id.')" title="'._('clones').'">&#187;&#187;</a>--></span></li>';
			$clone_counter++;
		}
		echo '</ol>';
		echo '</fieldset>';
	}


}

function do_user_tabs($option, $user) {

		$active = array();
		$active[$option] = 'class="tabsub-this"';

		echo '<ul class="tabsub">'."\n";
		echo '<li><a '.$active[1].' href="'.get_user_uri($user).'">'._('perfil'). '</a></li>';
		echo '<li><a '.$active[2].' href="'.get_user_uri($user, 'history').'">'._('enviadas'). '</a></li>';
		echo '<li><a '.$active[3].' href="'.get_user_uri($user, 'commented').'">'._('comentarios'). '</a></li>';
		echo '<li><a '.$active[4].' href="'.get_user_uri($user, 'shaken').'">'._('votadas'). '</a></li>';
		echo '<li><a '.$active[5].' href="'.get_user_uri($user, 'preferred').'">'._('autores preferidos'). '</a></li>';
		echo '</ul>';

}

?>
