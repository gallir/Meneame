<?
$sneak_version = 2;
$max_items = 25;
header('Connection: close');

function init_sneak() {
	global $globals, $db, $current_user;

	// Create temporary table for chat if it does not exist
	$db->query('CREATE TABLE IF NOT EXISTS `chats` ( `chat_time` INTEGER UNSIGNED NOT NULL DEFAULT 0 , `chat_uid` INTEGER UNSIGNED NOT NULL DEFAULT 0, `chat_room` enum("all","friends","admin") NOT NULL default "all", `chat_user` CHAR( 32 ) NOT NULL , `chat_text` CHAR( 255 ) NOT NULL , INDEX USING BTREE ( `chat_time` ) ) ENGINE = MEMORY MAX_ROWS = 2000');
	$db->query('CREATE TABLE IF NOT EXISTS `sneakers` ( `sneaker_id` CHAR(24) NOT NULL, `sneaker_time` INTEGER UNSIGNED NOT NULL DEFAULT 0, `sneaker_user` INTEGER UNSIGNED NOT NULL DEFAULT 0, UNIQUE ( `sneaker_id` ) ) ENGINE = MEMORY MAX_ROWS = 1000');

	// Check number of users if it's annonymous
	if ($current_user->user_id == 0) {
		$nusers= $db->get_var("select count(*) from sneakers");
		if ($nusers > 120) {
			header('Location: http://' . get_server_name().$globals['base_url'].'toomuch.html');
			die;
		}
	}

	// Check number of connections from the same IP addres
	// if it comes from Netvibes, allow more
	if (preg_match('/Netvibes Ajax/' , $_SERVER["HTTP_USER_AGENT"])) $max_conn = 50;
	else $max_conn = 5;
	$nusers= $db->get_var("select count(*) from sneakers where sneaker_id like '".$globals['user_ip']."-%'");
	if ($nusers > $max_conn) {
		header('Location: http://' . get_server_name().$globals['base_url'].'toomuch.html');
		die;
	}

	// Delete all connections from the same IP, just to avoid stupid cheating
	$db->query("delete from sneakers where sneaker_id like '".$globals['user_ip']."%'");
}

?>
