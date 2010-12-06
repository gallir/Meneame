<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'html1.php');
include(mnminclude.'favorites.php');

$globals['search_options'] = array('w' => 'posts');

if ($current_user->user_id > 0) {
	array_push($globals['extra_js'], 'jquery-form.pack.js');
}



$user=new User();

if (!isset($_REQUEST['id']) && !empty($_SERVER['PATH_INFO'])) {
	$argv = preg_split('/\/+/', $_SERVER['PATH_INFO'], 4, PREG_SPLIT_NO_EMPTY);
	$argv[0] = clean_input_string($argv[0]);
} else {
	$argv = preg_split('/\/+/', $_REQUEST['id'], 4, PREG_SPLIT_NO_EMPTY);
	$argv[0] = clean_input_string($argv[0]);
}

$min_date = date("Y-m-d H:00:00", time() - 192800); //  about 48 hours
$page_size = 50;
$offset=(get_current_page()-1)*$page_size;
$page_title = _('nótame') . ' | '._('menéame');
$view = false;
switch ($argv[0]) {
	case '_best':
		$tab_option = 2;
		$page_title = _('mejores notas') . ' | ' . _('menéame');
		$min_date = date("Y-m-d H:00:00", time() - 86400); //  about 24 hours
		$sql = "SELECT post_id FROM posts where post_date > '$min_date' ORDER BY post_karma desc limit $offset,$page_size";
		$rows = $db->get_var("SELECT count(*) FROM posts where post_date > '$min_date'");
		break;

	case '_geo':
		$tab_option = 3;
		$page_title = _('nótame') . ' geo | '._('menéame');
		require_once(mnminclude.'geo.php');
		if ($current_user->user_id > 0 && ($latlng = geo_latlng('user', $current_user->user_id))) {
			geo_init('onLoad', $latlng, 5);
		} else {
			geo_init('onLoad', false, 2);
		}
		break;

	case '':
	case '_all':
		$tab_option = 1;
		$sql = "SELECT SQL_CACHE post_id FROM posts ORDER BY post_id desc limit $offset,$page_size";
		//$rows = $db->get_var("SELECT count(*) FROM posts");
		$min_date = date("Y-m-d 00:00:00", time() - 86400*10);
		$rows = $db->get_var("SELECT SQL_CACHE count(*) FROM posts where post_date > '$min_date'");
		$rss_option="sneakme_rss2.php";
		break;

	default:
		$tab_option = 4;
		if ( (is_numeric($argv[0]) && ($post_id = intval($argv[0])) > 0)
				|| (is_numeric($argv[1]) && ($post_id = intval($argv[1])) > 0)  ) {
			// Individual post
			$user->id = $db->get_var("select post_user_id from posts where post_id=$post_id");
			if(!$user->read()) {
				do_error(_('usuario no encontrado'), 404);
			}
			$page_title = sprintf(_('nota de %s'), $user->username) . " ($post_id)";
			$globals['search_options']['u'] = $user->username;
			$sql = "SELECT post_id FROM posts WHERE post_id = $post_id";
			$rows = 1;
		} else {
			// User is specified
			$user->username = $db->escape($argv[0]);
			if(!$user->read()) {
				do_error(_('usuario no encontrado'), 404);
			}
			switch($argv[1]) {
				case '_friends':
					$view = 1;
					$page_title = sprintf(_('amigos de %s'), $user->username);
					$sql = "SELECT post_id FROM posts, friends WHERE friend_type='manual' and friend_from = $user->id and friend_to=post_user_id and friend_value > 0 ORDER BY post_id desc limit $offset,$page_size";
					$rows = $db->get_var("SELECT count(*) FROM posts, friends WHERE friend_type='manual' and friend_from = $user->id and friend_to=post_user_id and friend_value > 0");
					$rss_option="sneakme_rss2.php?friends_of=$user->id";
					break;

				case '_favorites':
					$view = 2;
					$page_title = sprintf(_('favoritas de %s'), $user->username);
					$sql = "SELECT post_id FROM posts, favorites WHERE favorite_user_id=$user->id AND favorite_type='post' AND favorite_link_id=post_id ORDER BY post_id DESC LIMIT $offset,$page_size";
					$rows = $db->get_var("SELECT count(*) FROM favorites WHERE favorite_user_id=$user->id AND favorite_type='post'");
					$rss_option="sneakme_rss2.php?favorites_of=$user->id";
					break;

				case '_conversation':
					$view = 3;
					$page_title = sprintf(_('conversación de %s'), $user->username);
					$sql = "SELECT distinct conversation_from as post_id FROM conversations, posts WHERE conversation_user_to=$user->id and conversation_type='post' and post_id = conversation_from ORDER BY conversation_time desc LIMIT $offset,$page_size";
					$rows =  $db->get_var("SELECT count(distinct(conversation_from)) FROM conversations, posts WHERE conversation_user_to=$user->id and conversation_type='post' and post_id = conversation_from ");
					$rss_option="sneakme_rss2.php?conversation_of=$user->id";
					break;

				default:
					$view = 0;
					$page_title = sprintf(_('notas de %s'), $user->username);
					$globals['search_options']['u'] = $user->username;
					$sql = "SELECT post_id FROM posts WHERE post_user_id=$user->id ORDER BY post_id desc limit $offset,$page_size";
					$rows = $db->get_var("SELECT count(*) FROM posts WHERE post_user_id=$user->id");
					$rss_option="sneakme_rss2.php?user_id=$user->id";
			}
		}
}

$globals['ads'] = true;

do_header($page_title);
do_posts_tabs($tab_option, $user->username);
$post = new Post;

$conversation_extra = '';
if ($tab_option == 4) {
	if ($current_user->user_id == $user->id) {
		$conversation_extra = ' ['.Post::get_unread_conversations($user->id).']';
	}
	$options = array(
		_('todas') => post_get_base_url($user->username),
		_('amigos') => post_get_base_url("$user->username/_friends"),
		_('favoritos') => post_get_base_url("$user->username/_favorites"),
		_('conversación').$conversation_extra => post_get_base_url("$user->username/_conversation"),
		sprintf(_('perfil de %s').'&nbsp;&rarr;', $user->username) => get_user_uri($user->username),

	);
}  elseif ($tab_option == 1 && $current_user->user_id > 0) {
	$conversation_extra = ' ['.Post::get_unread_conversations($user->id).']';

	$options = array(
		_('amigos') => post_get_base_url("$current_user->user_login/_friends"),
		_('favoritos') => post_get_base_url("$current_user->user_login/_favorites"),
		_('conversación').$conversation_extra => post_get_base_url("$current_user->user_login/_conversation"),
		_('últimas imágenes') => '" onclick="javascript:$(\'#gallery\').load(base_url+\'backend/gallery.php?type=post\');return false',
	);
	echo '<div id="gallery" style="display:none"></div>'; // Hidden div to load fancybox gallery
} else $options = false;
do_post_subheader($options, $view, $rss_option);


/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
//do_best_stories();
if ($rows > 20) {
	do_best_posts();
	do_best_comments();
}
do_banner_promotions();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";
do_pages($rows, $page_size);

echo '<div class="notes">';


if ($current_user->user_id > 0) {
	echo '<div id="addpost"></div>';
	echo '<ol class="comments-list"><li id="newpost"></li></ol>'."\n";
}

if ($argv[0] == '_geo') {
	echo '<div class="topheading"><h2>'._('notas de las últimas 24 horas').'</h2></div>';
	echo '<div id="map" style="width: 95%; height: 500px;margin:0 0 0 20px;"></div></div>';
?>
<script type="text/javascript">
var baseicon;
var geo_marker_mgr = null;

function onLoad(lat, lng, zoom, icon) {
	baseicon = new GIcon();
	baseicon.iconSize = new GSize(20, 25);
	baseicon.iconAnchor = new GPoint(10, 25);
	baseicon.infoWindowAnchor = new GPoint(10, 10);
	if (geo_basic_load(lat||18, lng||15, zoom||2)) {
		geo_map.addControl(new GLargeMapControl());
		geo_marker_mgr = new GMarkerManager(geo_map);
		geo_load_xml('post', '', 0, base_url+"img/geo/common/geo-newnotame01.png");
		GEvent.addListener(geo_map, 'click', function (overlay, point) {
			if (overlay && overlay.myId > 0) {
				GDownloadUrl(base_url+"geo/"+overlay.myType+".php?id="+overlay.myId, function(data, responseCode) {
				overlay.openInfoWindowHtml(data);
				});
			} //else if (point) geo_map.panTo(point);
		});
	}
}
</script>
<?
} else {
	$posts = $db->get_results($sql);
	if ($posts) {
		echo '<ol class="comments-list">';
		$time_read = 0;
		foreach ($posts as $dbpost) {
			$post = Post::from_db($dbpost->post_id);
			if ( $post_id > 0 && $user->id > 0 && $user->id != $post->author) {
				echo '<li>'. _('Error: nota no existente') . '</li>';
			} else {
				echo '<li>';
				$post->print_summary();
				if ($post->date > $time_read) $time_read = $post->date;
				echo '</li>';
			}
		}
		echo "</ol>\n";

		// Print "conversation" for a given note
		if ($post_id > 0) {
			$sql = "SELECT conversation_from as post_id FROM conversations, posts WHERE conversation_type='post' and conversation_to = $post_id and post_id = conversation_from ORDER BY conversation_from asc LIMIT $page_size";
			$answers = $db->get_results($sql);
			if ($answers) {
				$answer = new Post;
				echo '<div style="padding-left: 40px; padding-top: 10px">'."\n";
				//echo '<h3>'._('Respuestas').'</h3>';
				echo '<ol class="comments-list">';
				foreach ($answers as $dbanswer) {
					$answer->id = $dbanswer->post_id;
					$answer->read();
					echo '<li>';
					$answer->print_summary();
					echo '</li>';
				}
				echo "</ol>\n";
				echo '</div>'."\n";
			}
		}

		// Update conversation time
		if ($view == 3 && $time_read > 0 && $user->id == $current_user->user_id) {
			Post::update_read_conversation($time_read);
		}
	}
	echo '</div>';
	do_pages($rows, $page_size);
}

echo '</div>';
if ($rows > 15) do_footer_menu();
do_footer();

function do_posts_tabs($tab_selected, $username) {
	global $globals, $current_user;

	$reload_text = _('recargar');
	$active = ' class="tabmain-this"';

	echo '<ul class="tabmain">' . "\n";

	// All
	if ($tab_selected == 1) {
		echo '<li'.$active.'><a href="'.post_get_base_url().'" title="'.$reload_text.'"><em>'._('todos').'</em></a></li>' . "\n";
	} else {
		echo '<li><a href="'.post_get_base_url().'">'._('todos').'</a></li>' . "\n";
	}

	// Best
	if ($tab_selected == 2) {
		echo '<li'.$active.'><a href="'.post_get_base_url('_best').'" title="'.$reload_text.'"><em>'._('popular').'</em></a></li>' . "\n";
	} else {
		echo '<li><a href="'.post_get_base_url('_best').'" title="'._('más votadas en 24 horas').'">'._('popular').'</a></li>' . "\n";
	}

	// GEO
	if ($globals['google_maps_api']) {
		if ($tab_selected == 3) {
			echo '<li'.$active.'><a href="'.post_get_base_url('_geo').'" title="'.$reload_text.'"><em>'._('mapa').'</em></a></li>' . "\n";
		} else {
			echo '<li><a href="'.post_get_base_url('_geo').'" title="'._('geo').'">'._('mapa').'</a></li>' . "\n";
		}
	}

	// User
	if ($tab_selected == 4) {
		echo '<li'.$active.'><a href="'.post_get_base_url($username).'" title="'.$reload_text.'"><em>'.$username.'</em></a></li>' . "\n";
	} elseif ($current_user->user_id > 0) {
		echo '<li><a href="'.post_get_base_url($current_user->user_login).'">'.$current_user->user_login.'</a></li>' . "\n";
	}

	// END STANDARD TABS
	echo '</ul>' . "\n";
}

function do_post_subheader($content, $selected = false, $rss = false, $rss_title = '') {
	global $globals, $current_user;

	// arguments: hash array with "button text" => "button URI"; Nº of the selected button
	echo '<ul class="subheader" style="margin-bottom: 20px">'."\n";
	if ($rss) {
		if (!$rss_title) $rss_title = 'rss2';
		echo '<li class="icon"><a href="'.$globals['base_url'].$rss.'" title="'.$rss_title.'" rel="rss"><img src="'.$globals['base_static'].'img/common/feed-icon-001.png" width="18" height="18" alt="rss2"/></a></li>';
	} else {
		echo '<li class="icon"><img src="'.$globals['base_static'].'img/common/feed-icon-gy-001.png" width="18" height="18" alt=""/></li>';
	}

	if ($current_user->user_id > 0 ) {
		if (Post::can_add()) {
			echo '<li class="selected"><span><a class="toggler" href="javascript:post_new()" title="'._('nueva nota').'">'._('nueva nota').'&nbsp;<img src="'.$globals['base_static'].'img/common/icon_add_post_002.png" alt="" width="13" height="12"/></a></span></li>';
		} else {
			echo '<li><span><a href="javascript:return false;">'._('nueva nota').'</a></span></li>';
		}
	}

	if (is_array($content)) {
		$n = 0;
		foreach ($content as $text => $url) {
	   		if ($selected === $n) $class_b = ' class = "selected"';
			else $class_b='';
	   		echo '<li'.$class_b.'>'."\n";
	   		echo '<a href="'.$url.'">'.$text."</a>\n";
	   		echo '</li>'."\n";
	   		$n++;
		}
	} elseif (! empty($content)) {
	    echo '<li>'.$content.'</li>';
	}
	echo '</ul>'."\n";
}

?>
