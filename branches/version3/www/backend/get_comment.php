<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and 
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

if (empty($_GET['id'])) die;
$id = intval($_GET['id']);
$comment = new Comment;
$comment->id=$id;
$comment->read();
if(!$comment->read) die;
$link = new Link;
$link->id = $comment->link;
if(!$link->read_basic()) die;
$comment->link_permalink =  $link->get_relative_permalink();
$comment->print_text(0);
?>
