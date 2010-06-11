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
	$url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
	$option = $url_args[1]; // The first element is always a "/"
	$post_id = intval($url_args[2]);
} else {
	$url_args = preg_split('/\/+/', $_REQUEST['id']);
	$option = $url_args[0];
	$post_id = intval($url_args[1]);
}

$min_date = date("Y-m-d H:00:00", time() - 192800); //  about 48 hours
$page_size = 50;
$offset=(get_current_page()-1)*$page_size;
switch ($option) {
	case '_geo':
		require_once(mnminclude.'geo.php');
		$tab_option = 5;
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
		break;

	case '_best':
		$tab_option = 2;
		$min_date = date("Y-m-d H:00:00", time() - 86400); //  about 24 hours
		$sql = "SELECT post_id FROM posts where post_date > '$min_date' ORDER BY post_karma desc limit $offset,$page_size";
		$rows = $db->get_var("SELECT count(*) FROM posts where post_date > '$min_date'");
		break;

	case '_friends':
		if ($current_user->user_id > 0) {
			$tab_option = 3;
			$sql = "SELECT post_id FROM posts, friends WHERE friend_type='manual' and friend_from = $current_user->user_id and friend_to=post_user_id and friend_value > 0 ORDER BY post_id desc limit $offset,$page_size";
			$rows = $db->get_var("SELECT count(*) FROM posts, friends WHERE friend_type='manual' and friend_from = $current_user->user_id and friend_to=post_user_id and friend_value > 0");
		} else {
			$tab_option = 1;	
			$sql = "SELECT post_id FROM posts ORDER BY post_id desc limit $offset,$page_size";
			$rows = $db->get_var("SELECT count(*) FROM posts");
		}
		$rss_option="?friends_of=$current_user->user_id";
		break;

	case '_favorites':
		if ($current_user->user_id > 0) {
			$tab_option = 7;
			$sql = "SELECT post_id FROM posts, favorites WHERE favorite_user_id=$current_user->user_id AND favorite_type='post' AND favorite_link_id=post_id ORDER BY post_id DESC LIMIT $offset,$page_size";
			$rows = $db->get_var("SELECT count(*) FROM favorites WHERE favorite_user_id=$current_user->user_id AND favorite_type='post'");
		} else {
			$tab_option = 1;	
			$sql = "SELECT post_id FROM posts ORDER BY post_id desc limit $offset,$page_size";
			$rows = $db->get_var("SELECT count(*) FROM posts");
		}
		$rss_option="?favorites_of=$current_user->user_id";
		break;

	case '_conversation':
		if ($current_user->user_id > 0) {
			$tab_option = 6;
			$sql = "SELECT conversation_from as post_id FROM conversations, posts WHERE conversation_user_to=$current_user->user_id and conversation_type='post' and post_id = conversation_from ORDER BY conversation_time desc LIMIT $offset,$page_size";
			$rows =  $db->get_var("SELECT count(*) FROM conversations, posts WHERE conversation_user_to=$current_user->user_id and conversation_type='post' and post_id = conversation_from ");
		} else {
			$tab_option = 1;	
			$sql = "SELECT post_id FROM posts ORDER BY post_id desc limit $offset,$page_size";
			$rows = $db->get_var("SELECT count(*) FROM posts");
		}
		$rss_option="?conversation_of=$current_user->user_id";
		break;

	default:
		$tab_option = 4;
		if ( $post_id > 0 ) {
			$user->id = $db->get_var("select post_user_id from posts where post_id=$post_id");
			if(!$user->read()) {
				do_error(_('usuario no encontrado'), 404);
			}
			if ($user->username != $option) { // $option == username
				header('Location: '.post_get_base_url($user->username).'/'.$post_id);
				die;
			}
			array_push($globals['search_options']['u'] = $user->username);
			$sql = "SELECT post_id FROM posts WHERE post_id = $post_id";
			$rows = 1;
		} else {
			$user->username = $db->escape($option);
			if(!$user->read()) {
				do_error(_('usuario no encontrado'), 404);
			}
			array_push($globals['search_options']['u'] = $user->username);
			$sql = "SELECT post_id FROM posts WHERE post_user_id=$user->id ORDER BY post_id desc limit $offset,$page_size";
			$rows = $db->get_var("SELECT count(*) FROM posts WHERE post_user_id=$user->id");
		}
		$rss_option="?user_id=$user->id";
}

$globals['ads'] = true;

do_header(_('nótame') . ' | men&eacute;ame');
do_posts_tabs($tab_option, $user->username);

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
$post = new Post;
$post->print_post_teaser($rss_option);

if ($option == '_geo') {
	echo '<div class="topheading"><h2>notas de las últimas 24 horas</h2></div>';
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
		foreach ($posts as $dbpost) {
			$post = Post::from_db($dbpost->post_id);
			if ( $post_id > 0 && $user->id > 0 && $user->id != $post->author) {
				echo '<li>'. _('Error: nota no existente') . '</li>';
			} else {
				echo '<li>';
				$post->print_summary();
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

	// GEO
	if ($globals['google_maps_api']) {
		if ($tab_selected == 5) {
			echo '<li'.$active.'><a href="'.post_get_base_url('_geo').'" title="'.$reload_text.'"><em>'._('mapa').'</em></a></li>' . "\n";
		} else {
			echo '<li><a href="'.post_get_base_url('_geo').'" title="'._('geo').'">'._('mapa').'</a></li>' . "\n";
		}
	}

	// Best
	if ($tab_selected == 2) {
		echo '<li'.$active.'><a href="'.post_get_base_url('_best').'" title="'.$reload_text.'"><em>'._('popular').'</em></a></li>' . "\n";
	} else {
		echo '<li><a href="'.post_get_base_url('_best').'" title="'._('más votadas en 24 horas').'">'._('popular').'</a></li>' . "\n";
	}

	// favorites
	if ($tab_selected == 7) {
		echo '<li'.$active.'><a href="'.post_get_base_url('_favorites').'" title="'.$reload_text.'">&nbsp;'.FAV_YES.'&nbsp;</a></li>' . "\n";
	} elseif ($current_user->user_id > 0) {
		echo '<li><a href="'.post_get_base_url('_favorites').'">&nbsp;'.FAV_YES.'&nbsp;</a></li>' . "\n";
	}

	// Conversation
	if ($tab_selected == 6) {
		echo '<li'.$active.'><a href="'.post_get_base_url('_conversation').'" title="'.$reload_text.'"><em>'._('conversación').'</em></a></li>' . "\n";
	} elseif ($current_user->user_id > 0) {
		echo '<li><a href="'.post_get_base_url('_conversation').'">'._('conversación').'</a></li>' . "\n";
	}

	// Friends
	if ($tab_selected == 3) {
		echo '<li'.$active.'><a href="'.post_get_base_url('_friends').'" title="'.$reload_text.'"><em>'._('amigos').'</em></a></li>' . "\n";
	} elseif ($current_user->user_id > 0) {
		echo '<li><a href="'.post_get_base_url('_friends').'">'._('amigos').'</a></li>' . "\n";
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

?>
