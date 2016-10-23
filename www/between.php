<?php
// The Meneame source code is Free Software, Copyright (C) 2005-2011 by
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
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include(__DIR__.'/config.php');
include(mnminclude.'html1.php');
$globals['extra_js'][] = 'autocomplete/jquery.autocomplete.min.js';
$globals['extra_css'][] = 'jquery.autocomplete.css';
$globals['extra_js'][] = 'jquery.user_autocomplete.js';

$page_size = 20;
$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = true;

$u1 = User::get_valid_username(clean_input_string($_REQUEST['u1']));
$u2 = User::get_valid_username(clean_input_string($_REQUEST['u2']));

$id1 = User::get_user_id($u1);
$id2 = User::get_user_id($u2);

switch ($_REQUEST['type']) {
	case 'comments':
		$type = 'comments';
		$prefix = 'comment';
		break;
	case 'posts':
	default:
		$type = 'posts';
		$prefix = 'post';
}


do_header(sprintf(_('debate entre %s y %s'), $u1, $u2));
do_tabs('main',_('debate'), $globals['uri']);


/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

$options = array('u1' => $u1, 'u2' => $u2, 'type' => $type, 'types' => array('posts', 'comments'));

echo '<div id="newswrap">';

Haanga::Load('between.html', compact('options'));

if ($id1 > 0 && $id2 >0) {
	$all = array();
	$to = array();
	$sorted = array();
	$rows = 0;


	if (isset($_GET['id']) && ! empty($_GET['id']) ) {
		$sorted = explode(',', @gzuncompress(@base64_decode($_GET['id'])));
		$show_thread = true;
	} else {
		$show_thread = false;
		$rows = -1;
		$to[0] = between($id1, $id2, $type, $prefix, $page_size, $offset);
		$to[1] = between($id2, $id1, $type, $prefix, $page_size, $offset);

		foreach ($to as $e) {
			foreach ($e as $k => $v) {
				$all[$k] =  $v;
			}
		}

		$keys = array_keys($all);
		sort($keys, SORT_NUMERIC);
		foreach ($keys as $k) {
			$a = $all[$k];
			sort($a, SORT_NUMERIC);
			foreach ($a as $e) {
				if (! in_array($e, $sorted) && ! in_array($e, $keys)) {
					$sorted[] = $e;
				}
			}
			$sorted[] = $k;
		}
		$sorted = array_reverse($sorted);
	}

	$thread = array();
	$leaves = array();
	foreach($sorted as $id) {
		$id = intval($id); // Filter id, could be anything from $sorted
		if ( ! $show_thread ) {
			if (isset($all[$id])) {
				foreach ($all[$id] as $e) {
					$leaves[$e] = true;
				}
			}
		}
		if (isset($leaves[$id])) unset($leaves[$id]);

		//$obj->basic_summary = true;
		switch ($type) {
			case 'posts':
				$obj = Post::from_db($id);
				break;
			case 'comments':
				$obj = Comment::from_db($id);
				break;
		}
		if (! $obj || ($obj->type == 'admin' && !$current_user->admin)) continue;

		if ($obj->author == $id1) {
			echo '<div style="margin-top: -10;margin-left: 10px; width:70%">';
		} else {
			echo '<div style="margin-top: -10;margin-left:30%">';
		}
		$obj->print_summary();
		echo "</div>\n";
		$thread[] = $id;

		if ( $show_thread ) continue;

		if (! isset($all[$id]) && ! in_array($id, $leaves)) {
			$code = urlencode(base64_encode(gzcompress(implode(",", $thread))));
			echo '<div style="margin: -5px 0 15px 0;text-align:center; color: #888">';
			echo '[<a href="'.$globals['base_url'].'between?type='.$type.'&amp;u1='.$u1.'&amp;u2='.$u2.'&amp;id='.$code.'">'._('enlace permanente').'</a>]<br/>';
			echo '<strong style="font-size: 15pt;text-shadow: 1px 1px 3px #aaa">&bull; &bull; &bull;</strong>';
			echo '</div>';
			$thread = array();
			$leaves = array();
		}
	}


}

echo "</div>";

if ($rows) do_pages($rows, $page_size);
do_footer_menu();
do_footer();

function between($id1, $id2, $table, $prefix, $rows=25, $pos = 0) {
	global $db;

	$rels = array();
	$res = $db->get_results("select conversation_from as `from`, conversation_to as `to` from conversations, $table where conversation_from = ${prefix}_id and conversation_type = '$prefix' and conversation_user_to = $id1 and ${prefix}_user_id = $id2 order by conversation_time desc limit $pos, $rows");
	if ($res) {
		foreach ($res as $r) {
			if (! isset($rels[$r->from])) $rels[$r->from] = array();
			if (! in_array($r->to, $rels[$r->from])) $rels[$r->from][] = $r->to;
		}
	}
	return $rels;

}
