<?
include_once('../config.php');
header('Content-Type: application/json; charset=utf-8');

if(empty($_GET['id']) || empty($_GET['fields'])) die;
$id = intval($_GET['id']);
$fields = clean_input_string($_GET['fields']);

if (empty($_GET['what'])) $what = 'link';
else $what = $_GET['what'];

$object = false;
switch ($what) {
	case 'link':
		$object = Link::from_db($id);
		break;
}

if(!$object) die;

$output = new stdClass();
foreach (preg_split('/,/', $fields, 10, PREG_SPLIT_NO_EMPTY) as $f) {
	$output->$f = $object->$f;
}

echo json_encode($output);


