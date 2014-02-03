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

include('../config.php');
include(mnminclude.'html1.php');
include(mnminclude.'search.php');

$_REQUEST['words'] = substr(trim(strip_tags($_REQUEST['words'])), 0, 100);

$globals['extra_js'][] = 'jquery.flot.min.js';
$globals['extra_js'][] = 'jquery.flot.time.min.js';


$globals['noindex'] = true;

do_header(sprintf(_('tendencias de «%s»'), htmlspecialchars($_REQUEST['words'])));
//do_tabs('main',_('tendencias'), htmlentities($_SERVER['REQUEST_URI']));

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

$options = array();

Haanga::Load('trends.html', compact('options'));

do_footer_menu();
do_footer();

