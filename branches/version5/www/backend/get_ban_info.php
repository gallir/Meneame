<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// David Martín :: Suki_ :: <david at sukiweb dot net>.
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>
if (! defined('mnmpath')) {
	include_once('../config.php');
	header('Content-Type: text/html; charset=utf-8');
}

if (empty($_GET['id']) || !$current_user->admin) die;
$id = intval($_GET['id']);
require_once(mnminclude.'ban.php');
$ban=new Ban();
$ban->ban_id=$id;
if (! $ban->read())  die; 
echo '<strong>' . _($ban->ban_type) . ':</strong>&nbsp;' . $ban->ban_text . '<br/>';
if ($ban->ban_comment) echo '<strong>' . _('comentario') . ':</strong>&nbsp;' . $ban->ban_comment . '<br/>';
if ($ban->ban_expire)  echo '<strong>' . _('caduca') . ':</strong>&nbsp;' . $ban->ban_expire . '<br/>';
?>
