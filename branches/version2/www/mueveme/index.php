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
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$dblang.'" lang="'.$dblang.'">' . "\n";
echo '<head>' . "\n";
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
echo '<link rel="stylesheet" href="mueveme.css" type="text/css" />';
echo "<title>Mu&eacute;veme</title>\n";
echo '<meta name="generator" content="meneame" />' . "\n";
echo '</head>' . "\n";
echo '<body>';

echo '<div class="header">Muéveme</div>'."\n";

echo '<div class="links">';
echo '<a href="notame/">Nótame</a>';
echo '</div>';

echo "<ul>\n";
$links = $db->get_col("SELECT link_id from links where link_status='published' order by link_published_date desc LIMIT $page_size");
if ($links) {
	foreach($links as $link_id) {
		$link->id=$link_id;
		$link->read();
		$title_short = wordwrap($link->title, 36, " ", 1);
		echo '<li><a href="'.htmlspecialchars($link->url).'">'.$title_short.'</a> ';
		echo "<em>[$link->votes ". _('meneos').", $link->comments " . _('comentarios') ."]</em>. ";
		echo '<span class="text">'.text_to_html($link->content).'</span>';
		echo '&nbsp;&#187;&nbsp;<a href="http://meneame.net'.$link->get_relative_permalink().'"><em>'._('en menéame').'</em></a>';
		echo "</li>\n";
	}
}
echo "</ul>\n";
echo "</body></html>";

?>
