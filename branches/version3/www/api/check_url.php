<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

$mnm_over = "/img/mnm/api/mnm-over-01.png";
$mnm_vote = "/img/mnm/api/mnm-vote-01.png";
$mnm_add = "/img/mnm/api/mnm-add-01.png";

if(empty($_GET['url'])) die;

header('Content-Type: text/html; charset=UTF-8');
stats_increment('ajax', true);

echo '<html>'."\n";

echo '<script type="text/javascript" language="Javascript">'."\n";
echo '   mnm_yesover = new Image;'."\n";
echo '   mnm_vote_notover = new Image;'."\n";
echo '   mnm_add_notover = new Image;'."\n";
echo '   mnm_yesover.src = "'.$mnm_over.'";'."\n";
echo '   mnm_vote_notover.src = "'.$mnm_vote.'";'."\n";
echo '   mnm_add_notover.src = "'.$mnm_add.'";'."\n";
echo '   function changebutton(where,what) {'."\n";
echo '      if (document.images) document.images[where].src = eval(what + ".src");'."\n";
echo '   }'."\n";
echo '</script>'."\n";

echo '<body>'."\n";

$url = $db->escape($_GET['url']);
$res = $db->get_row("select link_id, link_votes, link_anonymous from links where link_url='$url'");
if ($res) {
	echo '<a href="/story.php?id='.$res->link_id.'" title="'.($res->link_votes+$res->link_anonymous).' '._('votos').'" target="_parent" onmouseover="changebutton(\'mnm_vote\',\'mnm_yesover\')" onmouseout="changebutton(\'mnm_vote\',\'mnm_vote_notover\')"><img style="border: 0" src="'.$mnm_vote.'" target="_parent" name="mnm_vote"/></a>';
} else {
	echo '<a href="/submit.php?url='.urlencode($url).'" title="'._('enviar esta historia').'" target="_parent" onmouseover="changebutton(\'mnm_add\',\'mnm_yesover\')" onmouseout="changebutton(\'mnm_add\',\'mnm_add_notover\')"><img style="border: 0" src="'.$mnm_add.'" name="mnm_add"/></a>';
}
echo '</body></html>';
?>
