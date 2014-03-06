<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'link.php');

$page_size = 30;
$link = new Link;


header("Content-type: text/html; charset=utf-8");
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">' . "\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$dblang.'">' . "\n";
echo '<head>' . "\n";
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
echo '<link rel="stylesheet" href="css/wiistyle.css" type="text/css" />';
echo "<title>Menéame - Wii edition</title>\n";
echo '<meta name="generator" content="meneame" />' . "\n";
echo '</head>' . "\n";

echo '<body id="home"><a name="top"></a>'."\n";
echo '<div id="header">'."\n";
echo '<h1>Menéame</h1>'."\n";
echo '</div>'."\n";

meta_get_current();
// NOTA: Esta lista debería ir dentro de #container, no aquí
if ($globals['meta_current'] > 0) {
	$from_where = "FROM links WHERE link_status='published' and link_category in (".$globals['meta_categories'].") ";
	print_index_tabs();
} else {
	print_index_tabs(0);
	$from_where = "FROM links WHERE link_status='published' ";
}


echo '<div id="container">';

$links = $db->get_col("SELECT link_id $from_where order by link_date desc LIMIT $page_size");


if ($links) {
	foreach($links as $link_id) {
		$link->id=$link_id;
		$link->read();

		echo '<div class="news-summary"><div class="news-body">';
		echo '<div class="news-shakeit"><div class="mnm-published">'.$link->votes.'</div><div class="menealo">meneos</div></div>';
		echo '<h2 class="title">'.$link->title.'</h2>';
		echo '<p>'.$link->to_html($link->content);
		echo ' <a href="'.htmlspecialchars($link->url).'">'._('Ver noticia').' &gt;</a></p>';
		echo '<p class="news-submitted"><img src="'.get_avatar_url($link->author, $link->avatar, 40).'" class="senderimg" width="40" height="40" alt="avatar de '.$link->username.'" />';
		echo ' '._('por').' <strong>'.$link->username.'</strong> ';
		// Print dates
		if (time() - $link->date > 604800) { // 7 days
			echo _('el').get_date_time($link->sent_date);
			if($link->status == 'published')
				echo ', '  ._('publicado el').get_date_time($link->date);
		} else {
			echo _('hace').txt_time_diff($link->sent_date);
			if($link->status == 'published')
				echo ', '  ._('publicado hace').txt_time_diff($link->date);
		}
		echo '</p>' ."\n";

		echo '<p class="gotop"><a href="#top">Ir arriba</a></p>';
		echo '</div></div>';
		echo '<div class="separator">&nbsp;</div>';
	}
}
echo "</div>\n";
echo "</body></html>";

function print_index_tabs($option=-1) {
	global $globals, $db;

	echo '<div id="navbar">'."\n";
	$active = array();
	// NOTA: Sólo se usa tabsub-this para la seleccionada, las demás van vacías
	// y es sólo para la activa
	if ($option == 0) $class_all = 'class="tabsub-this"';
	else $class_all = 'class="tabsub-that"';

	echo '<ul class="tabsub-shakeit">'."\n";
	echo '<li '.$class_all.'><a href="'.$globals['base_url'].'wii/'.$globals['meta_skip'].'">'._('todas'). '</a></li>'."\n";
	// Do metacategories list
	$metas = $db->get_results("SELECT category_id, category_name, category_uri FROM categories WHERE category_parent = 0 ORDER BY category_id ASC");

	// NOTA: usamos "tabsub" en el "<a>" no el el "<li">
	if ($metas) {
		foreach ($metas as $meta) {
			if ($meta->category_id == $globals['meta_current']) $active_meta = 'class="tabsub-this"';
			else $active_meta = 'class="tabsub-that"';
			echo '<li '.$active_meta.'><a href="'.$globals['base_url'].'wii/'.'?meta='.$meta->category_uri.'">'.$meta->category_name. '</a></li>'."\n";
		}
	}

	echo '</ul>'."\n";
	echo '</div>'."\n";
}

?>
