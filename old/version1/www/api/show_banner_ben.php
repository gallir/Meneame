<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

header('Content-Type: text/html; charset=UTF-8');
header('Pragma: no-cache');

$maxlen = 70;

$width = $_GET['width'];
$height = $_GET['height'];
$format = $_GET['format'];
$color_border = $_GET['color_border'];
$color_bg = $_GET['color_bg'];
$color_link = $_GET['color_link'];
$color_text = $_GET['color_text'];
$font_pt = $_GET['font_pt'];

echo '<html><body>';


$from = time() - 3600;
$res = $db->get_row("select link_id, link_title, count(*) as votes from links, votes where vote_date > FROM_UNIXTIME($from) and vote_value > 0 and link_id = vote_link_id group by link_id order by votes desc limit 1");
if ($res) {
	$votes_hour = $res->votes;
	$title['most'] = cut($res->link_title) . " [".$votes_hour." "._('votos/hora')."]";
	$url['most'] = "http://".get_server_name()."/story.php?id=$res->link_id";
}

$res = $db->get_row("select link_id, link_title, link_votes from links where link_status = 'published' order by link_published_date desc limit 1");
if ($res) {
	$title['published'] = cut($res->link_title) . " [".$res->link_votes." "._('votos')."]";
	$url['published'] = "http://".get_server_name()."/story.php?id=$res->link_id";
}

$res = $db->get_row("select link_id, link_title, link_votes from links where link_status = 'queued' order by link_date desc limit 1");
if ($res) {
	$title['sent'] = cut($res->link_title) . " [".$res->link_votes." "._('votos')."]";
	$url['sent'] = "http://".get_server_name()."/story.php?id=$res->link_id";
}

switch ($format) {
	case 'vertical':
		$div1 = '<div style="padding: 1px 1px 1px 1px; height: 30%; width: 100%; ">';
		$div2 = '<div style="padding: 1px 1px 1px 1px; height: 30%; width: 100%; border-top: 1px solid #'.$color_border.';">';
		$div3 = '<div style="padding: 1px 1px 1px 1px; height: 30%; width: 100%; border-top: 1px solid #'.$color_border.';">';
		$signature = _('Menéame');
		break;
	case 'horizontal':
	default:
		$div1 = '<div style="position: absolute; left: 2px; top: 2px; width: 32%;">';
		$div2 = '<div style="position: absolute; left: 33.3%; top: 2px; width: 32%;">';
		$div3 = '<div style="position: absolute; left: 66.7%; top: 2px; width: 32%;">';
		$signature = _('A public disservice spam by Menéaaaaaame');
}

?>
<div style="padding: 0 0 0 0 ; font-size: <? echo $font_pt;?>pt; color : #<? echo $color_text;?>; background: #<?echo $color_bg ?>; border: 1px solid #<?echo $color_border?>; width: <? echo $width-2; ?>px; height: <? echo $height-2; ?>px; ">

<?echo $div1;?>
<a href="<? echo $url['published']?>" style="color: #<? echo $color_link;?>" target="_parent"><? echo _('Última publicada'); ?></a><br />
<? echo $title['published'] ?>
</div>
<?echo $div2;?>
<a href="<? echo $url['sent']?>" style="color: #<? echo $color_link;?>" target="_parent"><? echo _('Última enviada'); ?> </a><br />
<? echo $title['sent'] ?>
</div>
<?echo $div3;?>
<a href="<? echo $url['most']?>" style="color: #<? echo $color_link;?>" target="_parent"><? echo _('Más menéada recientemente'); ?></a><br />
<? echo $title['most'] ?>
</div>

<div style="position: absolute; left: 0; bottom: 0px; font-size: 8pt; background: #<? echo $color_border;?>; color: #<?echo $color_bg ?>; height: 10pt; width: 100%; text-align: right;">
<a href="http://<?echo get_server_name();?>" style="color : #<?echo $color_bg ?>; text-decoration: none" target="_parent" ><?echo $signature;?></a>&nbsp;
<div>
</div>

<?
echo '</body></html>';

function cut($string) {
	global $maxlen;

	if (strlen($string) > $maxlen) {
		$string = substr($string, 0, $maxlen) . "..";
	}
	return $string .". ";
}
?>
