<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'user.php');
include(mnminclude.'post.php');
include(mnminclude.'html1.php');

array_push($globals['post_js'], 'jquery-form.pack.js');

$user=new User();

if (!defined($_REQUEST['id']) && !empty($_SERVER['PATH_INFO'])) {
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
		$sql = "SELECT post_id FROM posts ORDER BY post_id desc limit $offset,$page_size";
		$rows = $db->get_var("SELECT count(*) FROM posts");
		break;
	case '_best':
		$tab_option = 2;
		$min_date = date("Y-m-d H:00:00", time() - 86000); //  about 24 hours
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
	default:
		$tab_option = 4;
		$user->username = $db->escape($option);
		if(!$user->read()) {
			not_found();
		}
		$rss_option="?user_id=$user->id";
		if ( $post_id > 0 ) {
			$sql = "SELECT post_id FROM posts WHERE post_id = $post_id";
			$rows = 1;
		} else {
			$sql = "SELECT post_id FROM posts WHERE post_user_id=$user->id ORDER BY post_id desc limit $offset,$page_size";
			$rows = $db->get_var("SELECT count(*) FROM posts WHERE post_user_id=$user->id");
		}
}

$globals['ads'] = true;

do_header(_('nótame'));
do_banner_top();
echo '<div id="container">'."\n";
do_sidebar(false);
echo '<div id="contents">';
do_posts_tabs($tab_option, $user->username);

echo '<div class="notes">';
$post = new Post;
$post->print_post_teaser($rss_option);

if ($option == '_geo') {
	echo '<div class="topheading"><h2>notas de las últimas 24 horas</h2></div>';
	echo '<div id="map" style="width: 100%; height: 500px;margin:0 0 0 20px;"></div></div>';
?>
<script type="text/javascript">
var baseicon;
var geo_marker_mgr = null;

function onLoad(lat, lng, zoom) {
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
			$post->id = $dbpost->post_id;
			$post->read();
			if ( $post_id > 0 && $user->id > 0 && $user->id != $post->author) {
				echo '<li>'. _('Error: nota no existente') . '</li>';
			} else {
				$post->print_summary();
			}
		}
		echo "</ol>\n";
	}
	echo '</div>';
	do_pages($rows, $page_size);
}

echo '</div>';
do_footer();

function do_posts_tabs($tab_selected, $username) {
	global $globals, $current_user;

	$reload_text = _('recargar');
	$active = ' class="tabmain-this"';

	echo '<ul class="tabmain">' . "\n";

	// All
	if ($tab_selected == 1) {
		echo '<li><a '.$active.' href="'.post_get_base_url().'" title="'.$reload_text.'"><em>'._('todos').'</em></a></li>' . "\n";
	} else {
		echo '<li><a  href="'.post_get_base_url().'">'._('todos').'</a></li>' . "\n";
	}

	// GEO
	if ($globals['google_maps_api']) {
		if ($tab_selected == 5) {
			echo '<li><a '.$active.' href="'.post_get_base_url('_geo').'" title="'.$reload_text.'"><em>'._('mapa').'</em></a></li>' . "\n";
		} else {
			echo '<li><a  href="'.post_get_base_url('_geo').'" title="'._('geo').'">'._('mapa').'</a></li>' . "\n";
		}
	}

	// Best
	if ($tab_selected == 2) {
		echo '<li><a '.$active.' href="'.post_get_base_url('_best').'" title="'.$reload_text.'"><em>'._('popular').'</em></a></li>' . "\n";
	} else {
		echo '<li><a  href="'.post_get_base_url('_best').'" title="'._('más votadas en 24 horas').'">'._('popular').'</a></li>' . "\n";
	}
	// Friends
	if ($tab_selected == 3) {
		echo '<li><a '.$active.' href="'.post_get_base_url('_friends').'" title="'.$reload_text.'"><em>'._('amigos').'</em></a></li>' . "\n";
	} elseif ($current_user->user_id > 0) {
		echo '<li><a href="'.post_get_base_url('_friends').'">'._('amigos').'</a></li>' . "\n";
	}

	// User
	if ($tab_selected == 4) {
		echo '<li><a '.$active.' href="'.post_get_base_url($username).'" title="'.$reload_text.'"><em>'.$username.'</em></a></li>' . "\n";
	} elseif ($current_user->user_id > 0) {
		echo '<li><a href="'.post_get_base_url($current_user->user_login).'">'.$current_user->user_login.'</a></li>' . "\n";
	}
	// END STANDARD TABS

	echo '</ul>' . "\n";
}

?>
