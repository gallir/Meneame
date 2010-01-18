<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../../config.php');

if (!defined($_REQUEST['id']) && !empty($_SERVER['PATH_INFO'])) {
	$url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
	$option = $url_args[1]; // The first element is always a "/"
	$post_id = $url_args[2];
} else {
	$url_args = preg_split('/\/+/', $_REQUEST['id']);
	$option = $url_args[0];
	$post_id = $url_args[1];
}

$page_size = 30;
$post = new Post;


switch ($option) {
	case '':
    case '_all':
		$sql = "SELECT post_id from posts order by post_date desc LIMIT $page_size";
		break;
	default:
		$user = new User;
		$user->username = $db->escape($option);
		if(!$user->read()) {
			not_found();
		}
		$sql = "SELECT post_id FROM posts WHERE post_user_id=$user->id ORDER BY post_id desc limit $page_size";
}

header("Content-type: text/html; charset=utf-8");
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">' . "\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$dblang.'">' . "\n";
echo '<head>' . "\n";
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
echo '<link rel="stylesheet" href="../mueveme.css" type="text/css" />';
echo "<title>N&oacute;tame</title>\n";
echo '<meta name="generator" content="meneame" />' . "\n";
echo '</head>' . "\n";
echo '<body>';

echo '<div class="header">Nótame</div>'."\n";
echo '<div class="links">';
if (!empty($option)) {
	echo '<a href="./">'._('Todas').'</a>&nbsp;';
}
echo '<a href="../">'._('Muéveme').'</a>';
echo '</div>';

echo "<ul>\n";

$posts = $db->get_col($sql);
if ($posts) {
	foreach($posts as $post_id) {
		$post->id=$post_id;
		$post->read();
		echo '<li><a href="./?id='.htmlspecialchars($post->username).'">'.$post->username.'</a>: ';
		echo '<span class="text">'.save_text_to_html($post->content).'</span>';
		echo "</li>\n";
	}
}
echo "</ul>\n";
echo "</body></html>";

?>
