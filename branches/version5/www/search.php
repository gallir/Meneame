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
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'search.php');

$globals['extra_js'][] = 'autocomplete/jquery.autocomplete.min.js';
$globals['extra_css'][] = 'jquery.autocomplete.css';
$globals['extra_js'][] = 'jquery.user_autocomplete.js';

$page_size = $globals['page_size'];
$offset=(get_current_page()-1)*$page_size;

$globals['noindex'] = true;

$response = do_search(false, $offset, $page_size);
do_header(sprintf(_('búsqueda de «%s»'), htmlspecialchars($_REQUEST['words'])));
do_tabs('main',_('búsqueda'), htmlentities($_SERVER['REQUEST_URI']));

switch ($_REQUEST['w']) {
	case 'posts':
		$rss_program = 'sneakme_rss2.php';
		break;
	case 'comments':
		$rss_program = 'comments_rss2.php';
		break;
	case 'links':
	default:
		$rss_program = 'rss2.php';
}

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_rss_box($rss_program);
echo '</div>' . "\n";
/*** END SIDEBAR ***/

$options = array(
	'w' => array('links', 'posts', 'comments'),
	'p' => array('' => _('campos...'), 'url', 'tags', 'title', 'site'),
	's' => array('' => _('estado...'), 'published', 'queued', 'discard', 'autodiscard', 'abuse'),
	'h' => array('' => _('período...'), 24 => _('24 horas'), 48 => _('48 horas'), 24*7 => _('última semana'), 24*30 => _('último mes'), 24*180 => _('6 meses'), 24*365 => _('1 año')),
	'o' => array('' => _('por relevancia'), 'date' => _('por fecha')),
);

$selected = array('w' => $_REQUEST['w'], 'p' => $_REQUEST['p'], 's' => $_REQUEST['s'], 'h'=> $_REQUEST['h'], 'o' => $_REQUEST['o']);

Haanga::Load('search.html', compact('options', 'selected', 'response', 'rss_program'));

do_footer_menu();
do_footer();

function print_result() {
	global $response, $page_size;
	if ($response['ids']) {
		$rows = min($response['rows'], 1000);
		foreach($response['ids'] as $id) {
			switch ($_REQUEST['w']) {
				case 'posts':
					$obj = Post::from_db($id);
					break;
				case 'comments':
					$obj = Comment::from_db($id);
					break;
				case 'links':
				default:
					$obj = Link::from_db($id);
			}

			if (!$obj) continue;

			$obj->basic_summary = true;
			switch ($_REQUEST['w']) {
				case 'posts':
					$obj->print_summary(800);
					break;
				case 'comments':
					if ($obj->type == 'admin' && !$current_user->admin) continue;
					$obj->print_summary(false, 800);
					break;
				case 'links':
				default:
					$obj->print_summary();
			}
		}
	}
	do_pages($rows, $page_size);
}

?>
