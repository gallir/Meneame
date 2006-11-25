<?php
include_once('../config.php');

$id = intval($_GET['id']);
if (! $id > 0) die;

$votes_comments = $db->get_row("select link_votes, link_comments from links where link_id=$id");
$im = imagecreate(200, 16);
   
$bg = imagecolorallocatealpha($im, 255, 255, 255, 0);
$textcolor = imagecolorallocate($im, 255, 100, 0);

imagestring($im, 3, 2, 1, "$votes_comments->link_votes " . _('meneos') . ", $votes_comments->link_comments ". _('comentarios'), $textcolor);
		  
header("Content-type: image/png");
header('Cache-Control: max-age=30, must-revalidate');
header('Expires: ' . date('r', time()+30));
imagepng($im);
?>
