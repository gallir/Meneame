<?
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Menéame and Ricardo Galli <gallir at gallir dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


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

	if ($current_user->user_id > 0 ) {
		// Private messages
		if ($tab_selected == 5) {
			echo '<li'.$active.'><a href="'.post_get_base_url().'priv.php" title="'.$reload_text.'"><em>'._('privados').'</em></a></li>' . "\n";
		} elseif ($current_user->user_id > 0) {
			echo '<li><a href="'.post_get_base_url().'priv.php">'._('privados').'</a></li>' . "\n";
		}
	}

	// END STANDARD TABS
	echo '</ul>' . "\n";
}

function do_post_subheader($content, $selected = false, $rss = false, $rss_title = '') {
	global $globals, $current_user;

	// arguments: hash array with "button text" => "button URI"; Nº of the selected button
	echo '<ul class="subheader">'."\n";
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


function do_priv_subheader($content, $selected = false) {
	global $globals, $current_user;

	// arguments: hash array with "button text" => "button URI"; Nº of the selected button
	echo '<ul class="subheader">'."\n";

	echo '<li class="selected"><span><a class="toggler" href="javascript:priv_new(0)" title="'._('nuevo').'">'._('nuevo').'&nbsp;<img src="'.$globals['base_static'].'img/common/icon_add_post_002.png" alt="" width="13" height="12"/></a></span></li>';

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
