<?
// The Meneame source code is Free Software, Copyright (C) 2005-2009 by
// Ricardo Galli <gallir at gmail dot com> and Menéame Comunicacions S.L.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
// 
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.

// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'search.php');

// Manage "search" url and redirections accordingly
if (!empty($globals['base_search_url'])) {
	if (!empty($_SERVER['PATH_INFO']) ) {
		$q = preg_quote($globals['base_url'].$globals['base_search_url']);
		if(preg_match("{^$q}", $_SERVER['SCRIPT_URL'])) {
			$_REQUEST['q'] = urldecode(substr($_SERVER['PATH_INFO'], 1));
		}
	} elseif (!empty($_REQUEST['q'])) {
		$_REQUEST['q'] = substr(trim(strip_tags($_REQUEST['q'])), 0, 300);
		if (!preg_match('/\//', $_REQUEST['q']) ) {  // Freaking Apache rewrite that translate //+ to just one /
														// for example "http://" is converted to http:/
														// also it cheats the paht_info and redirections, so don't redirect
			header('Location: http://'. get_server_name().$globals['base_url'].$globals['base_search_url'].urlencode($_REQUEST['q']));
			die;
		}
	} elseif (isset($_REQUEST['q'])) {
		header('Location: http://'. get_server_name().$globals['base_url']);
		die;
	}
}


$page_size = 20;
$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = true;

$globals['noindex'] = true;

$response = do_search(false, $offset, $page_size);
do_header(_('búsqueda de'). ' "'.htmlspecialchars($_REQUEST['words']).'"');
do_tabs('main',_('búsqueda'), htmlentities($_SERVER['REQUEST_URI']));

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_rss_box();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

// Search form
echo '<div class="genericform" style="text-align: center">';
echo '<fieldset>';

print_search_form();
if(!empty($_REQUEST['q'])) {
	echo '<div style="font-size:85%;margin-top: 5px">';
	echo _('encontrados').': '.$response['rows'].', '._('tiempo total').': '.sprintf("%1.3f",$response['time']).' '._('segundos');
	echo '&nbsp;<a href="'.$globals['base_url'].'rss2.php?'.htmlspecialchars($_SERVER['QUERY_STRING']).'" rel="rss"><img src="'.$globals['base_static'].'img/common/feed-icon-12x12.png" alt="rss2" height="12" width="12"  style="vertical-align:top"/></a>';
	echo '</div>';
}

echo '</fieldset>';
echo '</div>';

switch ($_REQUEST['w']) {
	case 'posts':
		$obj = new Post;
		break;
	case 'comments':
		$obj = new Comment;
		break;
	case 'links':
	default:
		$obj = new Link;
}


if ($response['ids']) {
	$rows = min($response['rows'], 1000);
	foreach($response['ids'] as $id) {
		$obj->id=$id;
		$obj->read();
		$obj->basic_summary = true; 
		switch ($_REQUEST['w']) {
			case 'posts':
				$obj->print_summary(300);
				break;
			case 'comments':
				$obj->print_summary(false, 300);
				break;
			case 'links':
			default:
				$obj->print_summary();
		}
	}
}

do_pages($rows, $page_size);
echo '</div>';
do_footer_menu();
do_footer();

function print_search_form() {
	echo '<form id="thisform" action="">';
	echo '<input type="text" name="q" value="'.htmlspecialchars($_REQUEST['words']).'" class="form-full"/>';
	echo '<input class="button" type="submit" value="'._('buscar').'" />';

	// Print field options
	echo '<br />';


	echo '<select name="w" id="w">';
	switch ($_REQUEST['w']) {
		case 'posts':
		case 'comments':
			echo '<option value="'.$_REQUEST['w'].'" selected="selected">'.$_REQUEST['w'].'</option>';
			$what = $_REQUEST['w'];
			break;
		case 'links':
		default:
			$what = 'links';
			echo '<option value="" selected="selected">'.$what.'</option>';
	}
	foreach (array('links', 'posts', 'comments') as $w) {
		if ($w != $what) {
			echo '<option value="'.$w.'">'.$w.'</option>';
		}
	}
	echo '</select>';
		
	$visibility = $_REQUEST['w'] != 'links' ? ' disabled="disabled"' : '';
	echo '<select name="p" id="p" '.$visibility.'>';
	switch ($_REQUEST['p']) {
		case 'url':
		case 'tags':
		case 'title':
		case 'site':
			echo '<option value="'.$_REQUEST['p'].'" selected="selected">'.$_REQUEST['p'].'</option>';
			break;
		default:
			echo '<option value="" selected="selected">'._('campos...').'</option>';
			break;
	}
	foreach (array('url', 'tags', 'title', 'site') as $p) {
		if ($p != $_REQUEST['p']) {
			echo '<option value="'.$p.'">'.$p.'</option>';
		}
	}
	echo '<option value="">'._('todo el texto').'</option>';
	echo '</select>';

	// Print status options
	echo '&nbsp;&nbsp;<select name="s" id="s"'.$visibility.'>';
	switch ($_REQUEST['s']) {
		case 'published':
		case 'queued':
		case 'discard':
		case 'autodiscard':
		case 'abuse':
			echo '<option value="'.$_REQUEST['s'].'" selected="selected">'.$_REQUEST['s'].'</option>';
			break;
		default:
			echo '<option value="" selected="selected">'._('estado...').'</option>';
			break;
	}
	foreach (array('published', 'queued', 'discard', 'autodiscard', 'abuse') as $p) {
		if ($p != $_REQUEST['s']) {
			echo '<option value="'.$p.'">'.$p.'</option>';
		}
	}
	echo '<option value="">'._('todas').'</option>';
	echo '</select>';

	// Select period
	echo '&nbsp;&nbsp;<select name="h">';
	if($_REQUEST['h'] > 0) {
		$date = get_date(time()-$_REQUEST['h']*3600);
		echo '<option value="'.$_REQUEST['h'].'" selected="selected">'.$date.'</option>';
	} else {
		echo '<option value="" selected="selected">'._('período...').'</option>';
	}
	echo '<option value="'.intval(24).'">'._('24 horas').'</option>';
	echo '<option value="'.intval(48).'">'._('48 horas').'</option>';
	echo '<option value="'.intval(24*7).'">'._('última semana').'</option>';
	echo '<option value="'.intval(24*30).'">'._('último mes').'</option>';
	echo '<option value="'.intval(24*180).'">'._('6 meses').'</option>';
	echo '<option value="'.intval(24*365).'">'._('1 año').'</option>';
	echo '<option value="">'._('todas').'</option>';
	echo '</select>';


	echo '&nbsp;&nbsp;<select name="o">';
	if($_REQUEST['o'] == 'date') {
		echo '<option value="date">'._('por fecha').'</option>';
		echo '<option value="">'._('por relevancia').'</option>';
	} else {
		echo '<option value="">'._('por relevancia').'</option>';
		echo '<option value="date">'._('por fecha').'</option>';
	}
	echo '</select>';
	echo '</form>';

	echo '<script type="text/javascript">';
	echo '$(document).ready(function() {';
	echo '    $("#w").change(function() {'; 
	echo '        type = $("#w").val();';
//	echo '        if (type == "links") $("#link_options").css("visibility", "visible");';
//	echo '        else $("#link_options").css("visibility", "hidden");';
	echo '        if (type == "links") { $("#p").attr("disabled", false); $("#s").attr("disabled", false); }';
	echo '        else { $("#p").attr("disabled", true); $("#s").attr("disabled", true); }';
	echo '    });';
	echo '});';
	echo '</script>';
}

?>
