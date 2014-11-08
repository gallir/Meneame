<?php
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
if ($globals['mobile']) $width = min(400, $width);

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

$res = $db->get_row("select link_id, link_title, link_votes, link_anonymous from links where link_status = 'published' order by link_date desc limit 1");
if ($res) {
	$title['published'] = text_to_summary($res->link_title, 70) . ' <span style="font-size: 90%;">['.($res->link_votes+$res->link_anonymous)."&nbsp;"._('votos')."]</span>";
	$url['published'] = "http://".get_server_name()."/story.php?id=$res->link_id";
}

$res = $db->get_row("select link_id, link_title, link_votes, link_anonymous from links where link_status = 'queued' order by link_date desc limit 1");
if ($res) {
	$title['sent'] = text_to_summary($res->link_title, 70) . ' <span style="font-size: 90%;">['.($res->link_votes+$res->link_anonymous)."&nbsp;"._('votos')."]</span>";
	$url['sent'] = "http://".get_server_name()."/story.php?id=$res->link_id";
}
$last_comment = (int) $db->get_var("select comment_link_id from comments order by comment_id desc limit 1");
$res = $db->get_row("select link_id, link_title, link_votes, link_anonymous from links where link_id = $last_comment");

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
<div style="padding: 0 0 0 0; font-family: sans-serif; font-size: <?php echo $font_pt;?>pt; line-height: 1.1em; color : #<?php echo $color_text;?>; background: #<?php echo $color_bg ?>; border: 1px solid #<?php echo $color_border?>; width: <?php echo $width-2; ?>px; height: <?php echo $height-2; ?>px; ">

<?php echo $div1;?>
<a href="<?php echo $url['published']?>" style="color: #<?php echo $color_link;?>; text-decoration: none;" target="_parent"><strong><?php echo _('Última publicada'); ?></strong></a><br />
<?php echo $title['published'] ?>
</div>
<?php echo $div2;?>
<a href="<?php echo $url['sent']?>" style="color: #<?php echo $color_link;?>; text-decoration: none;" target="_parent"><strong><?php echo _('Última enviada'); ?></strong></a><br />
<?php echo $title['sent'] ?>
</div>
<?php echo $div3;?>
<a href="<?php echo $url['most']?>" style="color: #<?php echo $color_link;?>; text-decoration: none;" target="_parent"><strong><?php echo _('Caliente'); ?></strong></a><br />
<?php echo $title['most'] ?>
</div>
<?php echo $div4;?>
<a href="<?php echo $url['commented']?>" style="color: #<?php echo $color_link;?>; text-decoration: none;" target="_parent"><strong><?php echo _('Última comentada'); ?></strong></a><br />
<?php echo $title['commented'] ?>
</div>

<div style="position: absolute; left: 0; bottom: 0px; font-size: 8pt; background: #<?php echo $color_border;?>; color: #<?php echo $color_bg ?>; height: 10pt; width: 100%; text-align: right;">
<a href="http://<?php echo get_server_name();?>" style="color : #<?php echo $color_bg ?>; text-decoration: none" target="_parent" ><?php echo $signature;?></a>&nbsp;
</div>
</div>

<?php
echo '</body></html>';
?>
