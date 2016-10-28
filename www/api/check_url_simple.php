<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if(empty($_GET['url'])) die;

include(__DIR__.'/../config.php');

$mnm_image = $globals['base_static']."img/mnm/api/mnm-over-01.png";
header('Content-Type: text/html; charset=UTF-8');

echo '<html>'."\n";
echo '<body>'."\n";
echo '<a href="/submit.php?url='.urlencode($_GET['url']).'" title="'._('menéame').'" target="_parent"><img style="border: 0" src="'.$mnm_image.'" name="menéame"/></a>';
echo '</body></html>';
?>
