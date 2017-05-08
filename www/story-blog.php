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
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

defined('config_done') or die();

$link->related = $link->get_related();

$link->print_summary('full', 50, true, 'story/blog.html');

if ($link->status === 'discard') {
    return;
}

$link->related = false;
$link->best_comment = false;

$show_relevants = $no_page = false;

echo '<div class="story-blog-comments">';
echo '<div class="row">';
echo '<div class="col-md-8 col-md-offset-1">';

require __DIR__.'/story-comments.php';

echo '</div>';
echo '</div>';
echo '</div>';

$globals['tag_status'] = $globals['link']->status;
