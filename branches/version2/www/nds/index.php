<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and JP López <jpl at monobits dot net>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'link.php');

$page_size = 20;
$link = new Link;


header("Content-type: text/html; charset=utf-8");
meta_get_current();
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
echo '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">' . "\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$dblang.'">' . "\n";
echo '<head>' . "\n";
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
echo '<link rel="stylesheet" href="css/dsstyle.css" type="text/css" />';
echo "<title>Menéame - DS edition</title>\n";
echo '<meta name="generator" content="meneame" />' . "\n";
echo '</head>' . "\n";

echo '<body id="home">'."\n";

if ($globals['meta_current'] > 0) {
	$from_where = "FROM links WHERE link_status='published' and link_category in (".$globals['meta_categories'].") ";
	print_index_tabs();
} else {
	print_index_tabs(0);
	$from_where = "FROM links WHERE link_status='published' ";
}

echo '<div id="banner"><img src="images/banner.png" alt="MeneameDS" /></div>' . "\n";

echo '<div id="container">';

$links = $db->get_col("SELECT link_id $from_where order by link_published_date desc LIMIT $page_size");


if ($links) {
	foreach($links as $link_id) {
		$link->id=$link_id;
		$link->read();

		echo '<div class="news-summary">';
		echo '<div class="news-title"><a href="'.htmlspecialchars($link->url).'">'.$link->title.'</a></div>'."\n";
		echo '<p class="news-content">'.text_to_html($link->content).'</p>'."\n";
		echo '<div class="news-footer">'.$link->votes . ' votos &#187; ' . '<a href="'.htmlspecialchars($link->uri).'">en menéame</a>'."\n";
		echo '</div></div>';
	}
}
echo "</div>\n";
echo "</body></html>";

function print_index_tabs($option=-1) {
	global $globals, $db;

	echo '<div id="home-links">'."\n";
	$active = array();

	// NOTA: Sólo se usa tabsub-this para la seleccionada, las demás van vacías
	// y es sólo para la activa
	if ($option == 0) $class_tab = 'class="tab-active"';
	else $class_tab = '';

	echo '<span '.$class_tab.'><a href="'.$globals['base_url'].'nds/'.$globals['meta_skip'].'">'._('todas'). '</a></span>'."\n";

	// Do metacategories list
	$metas = $db->get_results("SELECT category_id, category_name, category_uri FROM categories WHERE category_parent = 0 ORDER BY category_id ASC");

	// Note that we are using tab-active only for active tab...
	if ($metas) {
		foreach ($metas as $meta) {
			if ($meta->category_id == $globals['meta_current']) $active_meta = 'class="tab-active"';
			else $active_meta = '';
			echo '| <span '.$active_meta.'><a href="'.$globals['base_url'].'nds/'.'?meta='.$meta->category_uri.'">'.$meta->category_name. '</a></span>' . "\n";
		}
	}

	echo '</div>'."\n";
}

?>
