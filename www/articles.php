<?php
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
//	  http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once __DIR__.'/config.php';
require_once mnminclude.'html1.php';

meta_get_current();

$globals['tag_status'] = 'queued';

$page_size = $globals['page_size'] * 2;
$offset = (get_current_page() - 1) * $page_size;
$rows = -1;

Link::$original_status = true; // Show status in original sub

$pagetitle = _('Artículos');

if ($page > 1) {
    $pagetitle .= " ($page)";
}

do_header($pagetitle, _('artículos'), false);

/*** SIDEBAR ****/
echo '<div id="sidebar">';

do_sub_message_right();
do_banner_right();

if ($globals['show_popular_queued']) {
    do_best_queued();
}

do_last_subs('queued', 15, 'link_karma');
do_vertical_tags('queued');

echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

$site = SitesMgr::get_info();

$sql = '
    SELECT '.Link::SQL.' INNER JOIN (
        SELECT link
        FROM sub_statuses, subs, links
        WHERE (
            link_content_type = "article"
            AND sub_statuses.link = link_id
            AND sub_statuses.status = "queued"
            AND sub_statuses.id = sub_statuses.origen
            AND sub_statuses.date > "'.date('Y-m-d H:00:00', $globals['now'] - $globals['time_enabled_votes']).'"
            AND sub_statuses.origen = subs.id
            AND subs.owner > 0
            '.($site->sub ? ('AND subs.id = "'.$site->id.'"') : '').'
        )
        ORDER BY sub_statuses.date DESC
        LIMIT '.$offset.', '.$page_size.'
    ) AS ids ON (ids.link = link_id)
';

$links = $db->get_results($sql, 'Link');

if ($links) {
    $all_ids = array_map(function ($value) {
        return $value->id;
    }, $links);

    $pollCollection = new PollCollection;
    $pollCollection->loadSimpleFromRelatedIds('link_id', $all_ids);

    foreach ($links as $link) {
        if ($link->votes == 0 && $link->author != $current_user->user_id) {
            continue;
        }

        $link->poll = $pollCollection->get($link->id);
        $link->max_len = 600;
        $link->print_summary('full', ($offset < 1000) ? 16 : null, false);
    }
}

do_pages($rows, $page_size);

echo '</div>'."\n";

do_footer_menu();
do_footer();
