<?php
$globals['alternate_db_server'] = 'backend';
// Don't check the user is logged
$globals['no_auth'] = true;

include_once(__DIR__.'/../config.php');

$id = intval($_GET['id']);
if (! $id > 0) die;

// Example to change the image for a give domain
//if (preg_match('/domain.com/', $_SERVER['HTTP_REFERER'])) {
//}

$votes_comments = $db->get_row("select link_votes, link_anonymous, link_comments from links where link_id=$id");
$im = imagecreate(200, 16);

$bg = imagecolorallocatealpha($im, 255, 255, 255, 0);
$textcolor = imagecolorallocate($im, 255, 100, 0);

imagestring($im, 3, 2, 1, ($votes_comments->link_votes+$votes_comments->link_anonymous). ' ' . _('meneos') . ", $votes_comments->link_comments ". _('comentarios'), $textcolor);

header("Content-type: image/png");
header('Cache-Control: max-age=120, must-revalidate');
header('Expires: ' . date('r', time()+120));
imagepng($im);
