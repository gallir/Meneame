<?php
include_once('../config.php');
$forbidden = array('ip', 'email', 'ip_int', 'user_level');


header('Content-Type: application/json; charset=utf-8');

if(empty($_GET['id']) || empty($_GET['fields'])) die;
$id = intval($_GET['id']);
$fields = clean_input_string($_GET['fields']); // It has to remove parenthesis

if (empty($_GET['what'])) $what = 'link';
else $what = $_GET['what'];

$object = false;
switch ($what) {
	case 'link':
	case 'links':
		$object = Link::from_db($id, null, false);
		break;
	case 'comment':
	case 'comments':
		$object = Comment::from_db($id);
		break;
	case 'post':
	case 'posts':
		$object = Post::from_db($id);
		break;
}

if(!$object) die;

$output = new stdClass();
foreach (preg_split('/,/', $fields, 10, PREG_SPLIT_NO_EMPTY) as $f) {
	if (! in_array($f, $forbidden) && property_exists($object, $f)) {
		$output->$f = $object->$f;
	}
}

echo json_encode($output);


