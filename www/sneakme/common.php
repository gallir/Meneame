<?php
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Menéame and Ricardo Galli <gallir at gallir dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function get_posts_menu($tab_selected, $username) {
	global $globals, $current_user;

	if ($tab_selected != 4 && $current_user->user_id > 0) {
		$username = $current_user->user_login;
	}

	switch ($tab_selected) {
		case 2:
			$id = _('popular');
			break;
		case 3:
			$id = _('mapa');
			break;
		case 4:
			$id = $username;
			break;
		case 5:
			$id = _('privados');
			break;
		case 1:
		default:
			$id = _('todas');
			break;

	}

	$items = array();
	$items[] = new MenuOption(_('todas'), post_get_base_url(''), $id, _('todas las notas'));
	$items[] = new MenuOption(_('popular'), post_get_base_url('_best'), $id, _('notas populares'));
	if ($globals['google_maps_api']) {
		$items[] = new MenuOption(_('mapa'), post_get_base_url('_geo'), $id, _('mapa animado'));
	}
	if (! empty($username)) {
		$items[] = new MenuOption($username, post_get_base_url($username), $id, $username);
	}

	if ($current_user->user_id > 0 ) {
		$items[] = new MenuOption(_('privados'), post_get_base_url('_priv'), $id, _('mensajes privados'));
	}

	return $items;
}


function do_post_subheader($content, $selected = false, $rss = false, $rss_title = '') {
	global $globals, $current_user;

	// arguments: hash array with "button text" => "button URI"; Nº of the selected button
	echo '<ul class="subheader">'."\n";

	if ($current_user->user_id > 0 ) {
		if (Post::can_add()) {
			echo '<li><span><a class="toggler" href="javascript:post_new()" title="'._('nueva').'">&nbsp;'._('nota').'&nbsp;<i class="fa fa-pencil-square-o"></i></a></span></li>';
		} else {
			echo '<li><span><a href="javascript:return;">'._('nota').'</a></span></li>';
		}
	}

	if (is_array($content)) {
		$n = 0;
		foreach ($content as $text => $url) {
	   		if ($selected === $n) $class_b = ' class = "selected"';
			else {
				if ($n > 4) $class_b=' class="wideonly"';
				else $class_b='';
			}
	   		echo '<li'.$class_b.'>'."\n";
	   		echo '<a href="'.$url.'">'.$text."</a>\n";
	   		echo '</li>'."\n";
	   		$n++;
		}
	} elseif (! empty($content)) {
	    echo '<li>'.$content.'</li>';
	}

	if ($rss && ! empty ($content)) {
		if (!$rss_title) $rss_title = 'rss2';
	}
		echo '<li class="icon wideonly"><a href="'.$globals['base_url'].$rss.'" title="'.$rss_title.'"><i class="fa fa-rss-square"></i></a></li>';

	echo '</ul>'."\n";
}


function do_priv_subheader($content, $selected = false) {
	global $globals, $current_user;

	// arguments: hash array with "button text" => "button URI"; Nº of the selected button
	echo '<ul class="subheader">'."\n";

	echo '<li><span><a class="toggler" href="javascript:priv_new(0)" title="'._('nuevo').'">'._('nuevo').'&nbsp;<i class="fa fa-pencil-square-o"></i></a></span></li>';

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
