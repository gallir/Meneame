<?
// The source code packaged with this file is Free Software, Copyright (C) 2009 by
// Ricardo Galli <gallir at gallir dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
include('../config.php');

$colors = array('negatives' => '#CB4B4B', 'positives' => '#4DA74D');

header('Content-Type: application/json; charset=utf-8');

if (empty($_GET['id'])) die;
$id = intval($_GET['id']);
$user = new User;
$user->id=$id;
if($current_user->user_id != $user->id && ! $current_user->admin) die;
$user->read();
if(!$user->read) die;

if (($res = $db->get_results("select floor(unix_timestamp(vote_date)/3600)*3600 as t, vote_type, count(*) as n from votes where vote_user_id = $user->id and vote_date > date_sub(now(), interval 1 month) and vote_type in ('links', 'comments', 'posts') group by vote_type, t"))) {
	$data['links'] = array();
	$data['comments'] = array();
	$data['posts'] = array();
	foreach ($res as $vote) {
		foreach (array_keys($data) as $key) {
			$item = array($vote->t*1000, (int) $vote->n);
			array_push($data[$vote->vote_type], $item);
		}
	}
	$objects = array();
	foreach (array_keys($data) as $key) {
		$obj = array();
		$obj['label'] = $key;
		$obj['data'] = $data[$key];
		array_push($objects, $obj);
	}
}
echo json_encode($objects);
?>

