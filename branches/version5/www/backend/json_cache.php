<?
include_once('../config.php');

// Designed originally to download and cache Twitter oembed

header('Content-Type: application/json; charset=utf-8');

$service = clean_input_string($_GET['s']);


switch ($service) {
	case 'tweet':
		$id = $_GET['id'];
		if (! ctype_digit($id)) {
			syslog(LOG_INFO, "Missing id $id");
			exit;
		}
		$url = "https://api.twitter.com/1/statuses/oembed.json?id=$id&align=center&maxwidth=550";
		break;
	default:
		die;
}

$key = "json_cache_$service-$id";
$cache = Annotation::from_db($key);


if ($cache) {
	echo $cache->text;
	exit(0);
}

// Get the url if not cached
$cache = new Annotation($key);

$res = get_url($url);
if (! $res || ! $res['content'] || $res['http_code'] != 200) {
	$cache->time = time() + 3600; // if it failed, cache for one hour
	$cache->text = '{}'; // Return empty object
	
} else {
	$cache->time = time() + 86400 * 7; // 7 days in cache
	$cache->text = $res['content'];
}

$cache->store();
echo $cache->text;


