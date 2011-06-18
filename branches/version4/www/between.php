<?
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

include('config.php');
include(mnminclude.'html1.php');


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
do_tabs('main',_('debate'), htmlentities($_SERVER['REQUEST_URI']));


/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

$options = array('u1' => $u1, 'u2' => $u2, 'type' => $type, 'types' => array('posts', 'comments'));

echo '<div id="newswrap">';

Haanga::Load('between.html', compact('options'));

if ($id1 > 0 && $id2 >0) {
	$to2 = between($id1, $id2, $type, $prefix, $page_size, $offset);
	$to1 = between($id2, $id1, $type, $prefix, $page_size, $offset);

	$all = array_merge(array_keys($to1), array_keys($to2));

	foreach ($to1 as $k => $v) {
		$all = array_merge($all, $v);
	}
	foreach ($to2 as $k => $v) {
		$all = array_merge($all, $v);
	}

	rsort($all, SORT_NUMERIC);
	$uniques = array_unique($all);

		foreach($uniques as $id) {
			//$obj->basic_summary = true;
			switch ($type) {
				case 'posts':
					$obj = Post::from_db($id);
					break;
				case 'comments':
					$obj = Comment::from_db($id);
					break;
			}
			if ($obj->type == 'admin' && !$current_user->admin) continue;

			if ($obj->author == $id1) {
				echo '<div style="margin-top: -10;margin-left: 10px; width:70%">';
			} else {
				echo '<div style="margin-top: -10;margin-left:30%">';
			}
			$obj->print_summary();
			echo "</div>\n";
			if (! isset($to1[$id]) && ! isset($to2[$id])) {
				echo '<div style="font-size: 15pt; margin: -5px 0 15px 0;text-align:center; color: #888; text-shadow: 1px 1px 3px #aaa"><strong>&bull; &bull; &bull;</strong></div>';
			}
		}


}

echo "</div>";

do_pages(-1, $page_size);
do_footer_menu();
do_footer();

function between($id1, $id2, $table, $prefix, $rows=20, $pos = 0) {
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
?>
