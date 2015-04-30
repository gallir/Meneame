<?php
include_once('../config.php');

$id = intval($_GET['id']);
if (! $id > 0) die;

$comments = $db->get_var("select link_comments from links where link_id=$id");
$im = imagecreate(110, 18);
   
$bg = imagecolorallocatealpha($im, 255, 255, 255, 127);
$textcolor = imagecolorallocate($im, 255, 100, 0);

imagestring($im, 3, 2, 1, "$comments ". _('comentarios'), $textcolor);
		  
header("Content-type: image/png");
header('Cache-Control: max-age=30, must-revalidate');
header('Expires: ' . date('r', time()+30));
imagepng($im);
?>
