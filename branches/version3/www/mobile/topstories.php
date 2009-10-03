<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1-mobile.php');
include(mnminclude.'linkmobile.php');

$globals['ads'] = false;

$page_size = 15;

$from = 1; // For 48 hours
$range_names  = array(_('24 horas'), _('48 horas'), _('una semana'), _('un mes'), _('un año'), _('todas'));
$range_values = array(1, 2, 7, 30, 365, 0);

$current_page = get_current_page();
$offset=($current_page-1)*$page_size;


// Use memcache if available
if ($globals['memcache_host'] && $current_page < 4) {
	$memcache_key = 'topstories_mobile_'.$from.'_'.$current_page;
}

// we use this to allow sql caching
$from_time = '"'.date("Y-m-d H:i:00", time() - 86400 * $range_values[$from]).'"';
$sql = "SELECT SQL_CACHE link_id, link_votes+link_anonymous-link_negatives as votes FROM links WHERE  link_date > $from_time AND  link_status = 'published' ORDER BY votes DESC ";
$time_link = "link_date > $from_time AND";

if (!($memcache_key && ($rows = memcache_mget($memcache_key.'rows')) && ($links = memcache_mget($memcache_key))) ) {
	// Itr's not in cache, or memcache is disabled
	$rows = $db->get_var("SELECT count(*) FROM links WHERE $time_link link_status = 'published'");
	if ($rows == 0) {
		not_found();
	}
	$links = $db->get_results("$sql LIMIT $offset,$page_size");
	if ($memcache_key) {
		memcache_madd($memcache_key.'rows', $rows, 1800);
		memcache_madd($memcache_key, $links, 1800);
	}
}


do_header(_('populares').' '.$range_names[$from].' | men&eacute;ame mobile');
$globals['tag_status'] = 'published';
do_tabs('main', 'popular');

echo '<div id="newswrap">'."\n";


$link = new LinkMobile;
if ($links) {
	foreach($links as $dblink) {
		$link->id=$dblink->link_id;
		$link->read();
		$link->print_summary();
	}
}
do_pages($rows, $page_size);
echo '</div>'."\n";

do_footer();

?>
