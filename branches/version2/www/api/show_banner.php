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
header('Cache-Control: max-age=10, must-revalidate');
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';

$width = intval($_GET['width']);
$height = intval($_GET['height']);
$format = clean_input_string($_GET['format']);
$color_border = get_hex_color($_GET['color_border']);
$color_bg = get_hex_color($_GET['color_bg']);
$color_link = get_hex_color($_GET['color_link']);
$color_text = get_hex_color($_GET['color_text']);
$font_pt = is_numeric($_GET['font_pt']) ? floatval($_GET['font_pt']) : 10;

echo '<html><head><title>banner</title></head><body>';


$res = $db->get_row("select link_id, link_title, count(*) as votes from links, votes where vote_type='links' and vote_date > date_sub(now(), interval 10 minute) and vote_value > 0 and link_id = vote_link_id group by link_id order by votes desc limit 1");
if ($res) {
	$votes_hour = $res->votes*6;
	$title['most'] = text_to_summary($res->link_title, 70) . ' <span style="font-size: 90%;">['.$votes_hour."&nbsp;"._('votos/hora')."]</span>";
	$url['most'] = "http://".get_server_name()."/story.php?id=$res->link_id";
}

$res = $db->get_row("select link_id, link_title, link_votes, link_anonymous from links where link_status = 'published' order by link_published_date desc limit 1");
if ($res) {
	$title['published'] = text_to_summary($res->link_title, 70) . ' <span style="font-size: 90%;">['.($res->link_votes+$res->link_anonymous)."&nbsp;"._('votos')."]</span>";
	$url['published'] = "http://".get_server_name()."/story.php?id=$res->link_id";
}

$res = $db->get_row("select link_id, link_title, link_votes, link_anonymous from links where link_status = 'queued' order by link_date desc limit 1");
if ($res) {
	$title['sent'] = text_to_summary($res->link_title, 70) . ' <span style="font-size: 90%;">['.($res->link_votes+$res->link_anonymous)."&nbsp;"._('votos')."]</span>";
	$url['sent'] = "http://".get_server_name()."/story.php?id=$res->link_id";
}

$res = $db->get_row("select link_id, link_title, link_votes, link_anonymous from links, comments where link_id = comment_link_id  order by comment_id desc limit 1");

if ($res) {
	$title['commented'] = text_to_summary($res->link_title, 70) . ' <span style="font-size: 90%;">['.($res->link_votes+$res->link_anonymous)."&nbsp;"._('votos')."]</span>";
	$url['commented'] = "http://".get_server_name()."/story.php?id=$res->link_id";
}

switch ($format) {
	case 'vertical':
		$div1 = '<div style="padding: 1px 1px 1px 1px; height: 23%; width: 100%; ">';
		$div2 = '<div style="padding: 1px 1px 1px 1px; height: 23%; width: 100%; border-top: 1px solid #'.$color_border.';">';
		$div3 = '<div style="padding: 1px 1px 1px 1px; height: 23%; width: 100%; border-top: 1px solid #'.$color_border.';">';
		$div4 = '<div style="padding: 1px 1px 1px 1px; height: 23%; width: 100%; border-top: 1px solid #'.$color_border.';">';
		$signature = _('Menéame');
		break;
	case 'horizontal':
	default:
		$div1 = '<div style="position: absolute; left: 2px; top: 2px; width: 24%;">';
		$div2 = '<div style="position: absolute; left: 25%; top: 2px; width: 24%;">';
		$div3 = '<div style="position: absolute; left: 50%; top: 2px; width: 24%;">';
		$div4 = '<div style="position: absolute; left: 75%; top: 2px; width: 24%;">';
		$signature = _('Menéame');
}

?>
<div style="padding: 0 0 0 0 ; font-family: Verdana, Arial, sans-serif ; font-size: <? echo $font_pt;?>pt; line-height: 1.1em; color : #<? echo $color_text;?>; background: #<?echo $color_bg ?>; border: 1px solid #<?echo $color_border?>; width: <? echo $width-2; ?>px; height: <? echo $height-2; ?>px; ">

<?echo $div1;?>
<a href="<? echo $url['published']?>" style="color: #<? echo $color_link;?>" target="_parent"><? echo _('Última publicada'); ?></a><br />
<? echo $title['published'] ?>
</div>
<?echo $div2;?>
<a href="<? echo $url['sent']?>" style="color: #<? echo $color_link;?>" target="_parent"><? echo _('Última enviada'); ?> </a><br />
<? echo $title['sent'] ?>
</div>
<?echo $div3;?>
<a href="<? echo $url['most']?>" style="color: #<? echo $color_link;?>" target="_parent"><? echo _('Menéandose'); ?></a><br />
<? echo $title['most'] ?>
</div>
<?echo $div4;?>
<a href="<? echo $url['commented']?>" style="color: #<? echo $color_link;?>" target="_parent"><? echo _('Última comentada'); ?></a><br />
<? echo $title['commented'] ?>
</div>

<div style="position: absolute; left: 0; bottom: 0px; font-size: 8pt; background: #<? echo $color_border;?>; color: #<?echo $color_bg ?>; height: 10pt; width: 100%; text-align: right;">
<a href="http://<?echo get_server_name();?>" style="color : #<?echo $color_bg ?>; text-decoration: none" target="_parent" ><?echo $signature;?></a>&nbsp;
</div>
</div>

<?
echo '</body></html>';
?>
