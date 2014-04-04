<?
// The source code packaged with this file is Free Software, Copyright (C) 2005-2010 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function unaccent($string) {
	$chars = array(
	// Decompositions for Latin-1 Supplement
	chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
	chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
	chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
	chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
	chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
	chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
	chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
	chr(195).chr(143) => 'I',
	chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
	chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
	chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
	chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
	chr(195).chr(156) => 'U',
	chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
	chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
	chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
	chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
	chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
	chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
	chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
	chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
	chr(195).chr(178) => 'o',
	chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
	chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
	chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
	chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
	chr(195).chr(188) => 'u',
	// Decompositions for Latin Extended-A
	chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
	chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
	chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
	chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
	chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
	chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
	chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
	chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
	chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
	chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
	chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
	chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
	chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
	chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
	chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
	chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
	chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
	chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
	chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
	chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
	chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
	chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
	chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
	chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
	chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
	chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
	chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
	chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
	);
	$string = strtr($string, $chars);
	return $string;
}

function htmlentities2unicodeentities ($input) {
	$input = utf8_for_xml($input);
	$htmlEntities = array_values (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
	$entitiesDecoded = array_keys  (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
	$num = count ($entitiesDecoded);
	for ($u = 0; $u < $num; $u++) {
		$utf8Entities[$u] = '&#'.ord($entitiesDecoded[$u]).';';
	}
	return str_replace ($htmlEntities, $utf8Entities, $input);
}

function utf8_for_xml($string) {
	return preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
}

function clean_input_url($string) {
	$string = preg_replace('/ /', '+', trim(stripslashes(mb_substr($string, 0, 512))));
	$string = preg_replace('/[<>\r\n\t]/', '', $string);
	$string = preg_replace('/(utm_\w+?|feature)=[^&]*/', '', $string); // Delete common variables for Analitycs and Youtube
	$string = preg_replace('/&{2,}/', '&', $string); // Delete duplicates &
	$string = preg_replace('/&+$/', '', $string); // Delete useless & at the end
	$string = preg_replace('/\?&+/', '?', $string); // Delete useless & after ?
	$string = preg_replace('/\?&*$/', '', $string); // Delete empty queries
	return $string;
}

function clean_input_string($string) {
	return preg_replace('/[ <>\'\"\r\n\t\(\)]/', '', stripslashes($string));
}

function get_hex_color($color, $prefix = '') {
	return $prefix . substr(preg_replace('/[^a-f\d]/i', '', $color), 0, 6);
}

function get_negative_vote($value) {
	global $globals;
	return $globals['negative_votes_values'][$value];
}

function user_exists($username, $ignore = 0) {
	global $db;
	$username = $db->escape($username);
	$res=$db->get_var("SELECT user_id FROM users WHERE user_login='$username' AND user_id != $ignore");
	if ($res>0) return true;
	return false;
}

function email_exists($email, $check_previous_registered = true) {
	global $db;

	$parts = explode('@', $email);
	$domain = $parts[1];
	$subparts = explode('+', $parts[0]); // Because we allow user+extension@gmail.com
	$user = $subparts[0];
	$user = $db->escape($user);
	$domain = $db->escape($domain);
	$res=$db->get_var("SELECT count(*) FROM users WHERE user_email = '$user@$domain' or user_email LIKE '$user+%@$domain'");
	if ($res>0) return $res;
	if ($check_previous_registered) {
		// Check the same email wasn't used recently for another account
		$res=$db->get_var("SELECT count(*) FROM users WHERE (user_email_register = '$user@$domain' or user_email_register LIKE '$user+%@$domain') and user_date > date_sub(now(), interval 1 year)");
		if ($res>0) return $res;
	}
	return false;
}

function check_email($email) {
	global $globals;
	require_once(mnminclude.'ban.php');
	if (! preg_match('/^[a-z0-9_\-\.]+(\+[a-z0-9_\-\.]+)*@[a-z0-9_\-\.]+\.[a-z]{2,4}$/i', $email)) return false;

	$username = preg_replace('/@.+$/', '', $email);
	if ( substr_count($username, '.') > 3 || preg_match('/\.{2,}/', $username) ) return false; // Doesn't allow "..+" or more than 2 dots

	if(check_ban(preg_replace('/^.*@/', '', $email), 'email')) return false;
	return true;
}

function check_username($name) {
	return (preg_match('/^\p{L}[\._\p{L}\d]+$/ui', $name) && mb_strlen($name) > 2 && mb_strlen($name) <= 24 &&
				! preg_match('/^admin/i', $name) ); // Does not allow nicks begining with "admin"
}

function check_password($password) {
	 return preg_match("/^(?=.{6,})(?=(.*[a-z].*))(?=(.*[A-Z0-9].*)).*$/", $password);
}


function txt_time_diff($from, $now=0){
	global $globals;
	$txt = '';
	if($now==0) $now = $globals['now'];
	$diff=$now-$from;
	$days=intval($diff/86400);
	$diff=$diff%86400;
	$hours=intval($diff/3600);
	$diff=$diff%3600;
	$minutes=intval($diff/60);
	$secs=$diff%60;

	if($days>1) $txt  .= " $days "._('días');
	else if ($days==1) $txt  .= " $days "._('día');

	if($hours>1) $txt .= " $hours "._('horas');
	else if ($hours==1) $txt  .= " $hours "._('hora');

	if($minutes>1) $txt .= " $minutes "._('minutos');
	else if ($minutes==1) $txt	.= " $minutes "._('minuto');

	if($txt=='') {
		if ($secs < 5) $txt = ' ' . _('nada');
		else $txt = " $secs ". _('segundos');
	}
	return $txt;
}

function txt_shorter($string, $len=70) {
	if (mb_strlen($string) > $len)
		$string = mb_substr($string, 0, $len-3) . "...";
	return $string;
}

// Used to get the text content for stories and comments
function clean_text($string, $wrap=0, $replace_nl=true, $maxlength=0) {
	$string = stripslashes(trim($string));
	$string = preg_replace('/\r\n/u', "\n", $string); // Change \r\n to \n to show right chars' counter
	$string = clear_whitespace($string);
	$string = html_entity_decode($string, ENT_COMPAT, 'UTF-8');
	// Replace two "-" by a single longer one, to avoid problems with xhtml comments
	//$string = preg_replace('/--/', '–', $string);
	if ($wrap>0) $string = wordwrap($string, $wrap, " ", 1);
	if ($replace_nl) $string = preg_replace('/[\n\t\r]+/su', ' ', $string);
	if ($maxlength > 0) $string = mb_substr($string, 0, $maxlength);
	$string = @htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
	return preg_replace('/(\d+) +(\d{3,})/u', "$1&nbsp;$2", $string); // Avoid to wrap in the middle of numbers with thousands' space separator
}

function clean_text_with_tags($string, $wrap=0, $replace_nl=true, $maxlength=0) {
	$string = add_tags(clean_text($string, $wrap, $replace_nl, $maxlength));
	$string = preg_replace_callback('/(?:&lt;|<)(\/{0,1})(\w{1,6})(?:&gt;|>)/', 'enable_tags_callback', $string);
	$string = close_tags($string);
	$string = preg_replace('/<\/(\w{1,6})>( *)<(\1)>/', "$2", $string); // Deletes useless close+open tags
	//$string = preg_replace('/<(\/{0,1}\w{1,6})>( *)<(\1)>/', "<$1>$2", $string); // Deletes repeated tags
	return $string;
}

function enable_tags_callback($matches) {
	global $globals;
	static $open_tags = array();

	if (preg_match('/^('.$globals['enabled_tags'].')$/', $matches[2])) {
		if ($matches[1] == '/') {
			if (count($open_tags) > 0 && $open_tags[count($open_tags)-1] != $matches[2]) {
				return $matches[0];
			}
			array_pop($open_tags);
			return "</$matches[2]>";
		}
		$open_tags[] =	$matches[2];
		return "<$matches[2]>";
	}
	return $matches[0];
}

function close_tags(&$string) {
	return preg_replace_callback('/(?:<\s*(\/{0,1})\s*([^>]+)>|$)/', 'close_tags_callback', $string);
}

function close_tags_callback($matches) {
	static $open_tags = array();

	if (empty($matches[0])) {
		// End of text, close open tags
		$end = '';
		while (($t = array_pop($open_tags))) {
			$end .= "</$t>";
		}
		if ($end) $end = "\n$end\n";
		return $end;
	}
	if ($matches[1] && $matches[1][0] == '/') {
		if (count($open_tags) > 0 && $open_tags[count($open_tags)-1] == $matches[2]) {
			array_pop($open_tags);
		} else {
			return ' '; // Don't allow misplaced or wrong tags
		}
	} else {
		$open_tags[] = $matches[2];
	}
	return $matches[0];
}

function clean_lines($string) {
	return preg_replace('/[\n\r]{6,}/', "\n\n", $string);
}

function text_to_summary($string, $length=50) {
	$string = strip_tags($string);
	$string = preg_replace("/[\r\n\t]+/", ' ', $string);
	$len = mb_strlen($string);
	$string = mb_substr($string,  0, $length);
	if (mb_strlen($string) < $len) {
		$string = preg_replace('/ *[\w&;]*$/', '', $string);
		$string = preg_replace('/\s\S{1,20}$/', '', $string);
		$string .= '...';
	}
	return $string;
}

function add_tags($string) {
	// Convert to em, strong and strike tags
	$regexp = '_[^\s<>_]+_\b|\*[^\s<>]+\*|\-([^\s\-<>]+)\-';
	return preg_replace_callback('/([ \t\r\n\(\[{¿]|^)('.$regexp.')/u', 'add_tags_callback', $string);
}

function add_tags_callback($matches) {
	global $globals;

	switch ($matches[2][0]) {
		case '_':
			return $matches[1].'<em>'.substr($matches[2], 1, -1).'</em>';
		case '*':
			return $matches[1].'<strong>'.substr($matches[2], 1, -1).'</strong>';
		case '-':
			return $matches[1].'<del>'.substr($matches[2], 1, -1).'</del>';
	}
	return $matches[1].$matches[2];
}

function text_to_html(&$string) {
	global $globals;

	$regexp = '/([\s\(\[{¡;,:¿]|^)((https{0,1}:\/\/)([^\s<>]{5,500}))/Smu';
	return preg_replace_callback($regexp, 'text_to_html_callback', $string);
}

function text_to_html_callback(&$matches) {
	global $globals;

	switch ($matches[2][0]) {
		case 'h':
			$suffix = '';
			if (substr($matches[4], -1) == ')' && strrchr($matches[4], '(') === false) {
				$matches[4] = substr($matches[4], 0, -1);
				$suffix = ')';
			}
			return $matches[1].'<a href="'.$matches[3].$matches[4].'" title="'.$matches[4].'" rel="nofollow">'.substr($matches[4], 0, 70).'</a>'.$suffix;
	}
	return $matches[1].$matches[2];
}

function check_integer($which) {
	if (isset($_REQUEST[$which]) && is_numeric($_REQUEST[$which])) {
		return intval($_REQUEST[$which]);
	} else {
		return false;
	}
}

function get_comment_page_suffix($page_size, $order, $total=0) {
	if ($page_size > 0) {
		if ($total && $total < $page_size) return '';
		return '/'.ceil($order/$page_size);
	}
	return '';
}

function get_current_page() {
	if(($var=check_integer('page')) && $var > 0) {
		return $var;
	} else {
		return 1;
	}
	// return $_GET['page']>0 ? $_GET['page'] : 1;
}

function get_date($epoch) {
	return date("d-m-Y", $epoch);
}

function get_date_time($epoch) {
		global $globals;
		//return date("Y-m-d H:i", $epoch);
		if (abs($globals['now'] - $epoch) < 72000) // Difference is less than 20 hours
			return date(" H:i T", $epoch);
		else
			return date(" d-m-Y H:i T", $epoch);
}

function get_server_name() {
	global $globals;
	return isset($globals['server_name']) ? $globals['server_name'] : $_SERVER['SERVER_NAME'];
}

function get_static_server_name() {
	global $globals;
	return $globals['static_server_name'];
}

function get_auth_link() {
	global $globals;
	if ($globals['ssl_server']) {
		// If this is the mobile version and it's an external server, e.g www.meneame.net,
		// we'd call /mobile/login.php instead of /login.php
		if ($globals['mobile_version'] && $globals['ssl_server'] != get_server_name() ) {
			$path = 'mobile/';
		} else {
			$path = '';
		}
		return 'https://'.$globals['ssl_server'].$globals['base_url_general'].$path;
	} else {
		return $globals['base_url_general'];
	}
}

function check_auth_page() {
	global $globals;

	if ($globals['https']) {
		// If it's not a page that need SSL, redirect to the standard server
		if (!$globals['secure_page']) {
			// Send the user back to the origial page if it exists the cookie
			if (!empty($_COOKIE['return_site'])) {
				$host = $_COOKIE['return_site'];
			} else {
				$host = $_SERVER["SERVER_NAME"];
			}
			setcookie('return_site', '', $globals['now'] - 3600, $globals['base_url_general'], UserAuth::domain());
			header('HTTP/1.1 302 Moved');
			header('Location: http://'.$host.$_SERVER["REQUEST_URI"]);
			die;
		}
	} elseif ($globals['ssl_server'] && $globals['secure_page']) {
		setcookie('return_site', get_server_name(), 0, $globals['base_url_general'], UserAuth::domain());
		header('HTTP/1.1 302 Moved');
		header('Location: https://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);
		die;
	}
}

function get_form_auth_ip() {
	global $globals, $site_key;
	if (check_form_auth_ip()) { // We reuse the values
		$ip = $_REQUEST['userip'];
		$control = $_REQUEST['useripcontrol'];
	} else {
		$ip = $globals['user_ip'];
		$control = sha1($ip.$site_key.base64_encode($ip.$site_key));
	}
	echo '<input type="hidden" name="userip" value="'.$ip.'"/>';
	echo '<input type="hidden" name="useripcontrol" value="'.$control.'"/>';
}

function check_form_auth_ip() {
	global $globals, $site_key;
	if ($_REQUEST['userip'] && $_REQUEST['useripcontrol'] && sha1($_REQUEST['userip'].$site_key.base64_encode($_REQUEST['userip'].$site_key)) == $_REQUEST['useripcontrol']) {
		$globals['form_user_ip'] = $_REQUEST['userip'];
		$globals['form_user_ip_int'] = sprintf("%u", ip2long($globals['form_user_ip']));
		return true;
	} else {
		$globals['form_user_ip'] = $globals['user_ip'];
		$globals['form_user_ip_int'] = $globals['user_ip_int'];
		return false;
	}
}

function get_user_uri($user, $view='') {
	global $globals;

	$uri= $globals['base_url_general'] . 'user/' . htmlspecialchars($user);
	if (!empty($view)) $uri .= "/$view";
	return $uri;
}

function get_user_uri_by_uid($user, $view='') {
	global $globals;

	$uid = guess_user_id($user);
	if ($uid == 0) $uid = -1; // User does not exist, ensure it will give error later
	$uri = get_user_uri($user, $view);
	$uri .= "/$uid";
	return $uri;
}

function post_get_base_url($option='', $give_base=true) {
	global $globals;
	if ($give_base) $base = $globals['base_url_general'];
	else $base = '';
	return $base.'notame/'.$option;
}

function get_avatar_url($user, $avatar, $size, $fullurl = true) {
	global $globals, $db;

	// If it does not get avatar status, check the database
	if ($user > 0 && $avatar < 0) {
		$avatar = (int) $db->get_var("select user_avatar from users where user_id = $user");
	}

	if ($avatar > 0) {
		if ($globals['Amazon_S3_media_url'] && !$globals['Amazon_S3_local_cache']) {
			return $globals['Amazon_S3_media_url']."/avatars/$user-$avatar-$size.jpg";
		} elseif ($globals['cache_dir']) {

			if ($fullurl) $base = $globals['base_static_noversion'];
			else $base = $globals['base_url_general'];

			$file = Upload::get_cache_relative_dir($user) ."/$user-$avatar-$size.jpg";
			$file_path = mnmpath.'/'.$file;
			if (!$globals['cache_redirector']) {
				if (is_readable($file_path)) {
					return $base . $file;
				} else {
					return $globals['base_url_general'] . "backend/get_avatar.php?id=$user&amp;size=$size&amp;time=$avatar";
				}
			} else {
				return $base . $file;
			}
		}
	}
	return get_no_avatar_url($size, $fullurl);
}

function get_no_avatar_url($size, $fullurl = true) {
	global $globals;
	
	return $globals['base_static'].'img/mnm/no-gravatar-2-'.$size.'.jpg';
}

function utf8_substr($str,$start)
{
	preg_match_all("/./su", $str, $ar);

	if(func_num_args() >= 3) {
		$end = func_get_arg(2);
		return join("",array_slice($ar[0],$start,$end));
	} else {
		return join("",array_slice($ar[0],$start));
	}
}

// Simple unified key generator for use in GET requests
function get_security_key($time = false) {
	global $globals, $current_user, $site_key;
	if (!$time) $time = $globals['now'];

	if ($current_user->user_id > 0) {
		// For users of balanced connections and 3G we avoid using the IP
		return $time.'-'.sha1($time.$current_user->user_id.$current_user->user_date.$site_key);
	} else {
		// We shift 8 bits to avoid key errors with mobiles/3G that change IP frequently
		$ip_key = $globals['user_ip_int']>>8;
		// return $time.'-'.sha1($time.$ip_key.$site_key);
		return $time.'-'.base64_encode($time.$ip_key); // Faster, not needed more complex for anoymous users
	}

}

function check_security_key($key) {
	global $globals, $current_user, $site_key;

	$time_key = preg_split('/-/', $key);
	if (count($time_key) != 2) return false;
	if ($globals['now'] - intval($time_key[0]) > 7200) return false;
	return $key == get_security_key($time_key[0]);
}

function do_error($mess = false, $error = false, $send_status = "Error") {
	global $globals;
	$globals['ads'] = false;

	if (headers_sent($file, $line)) {
		syslog(LOG_INFO, "Headers already sent, file $file line $line, uri: ".$_SERVER["DOCUMENT_URI"]." mess: $mess");
	}

	if (! $mess ) $mess = _('algún error nos ha petado');

	if ($error) {
		@header("HTTP/1.0 $error $mess");
		@header("Status: $error $mess");
	}

	Haanga::Load('error.html', compact('mess', 'error'));
	die;
}

function not_found($mess = '') {
	do_error($mess, 404, 'Not found');
}

function get_uppercase_ratio($str) {
	$str = trim(htmlspecialchars_decode($str));
	$len = mb_strlen($str);
	$uppers = preg_match_all('/[A-Z]/', $str, $matches);
	if ($uppers > 0 && $len > 0) {
		return $uppers/$len;
	}
	return 0;
}

function do_modified_headers($time, $tag) {
	header('Last-Modified: ' . date('r', $time));
	header('ETag: "'.$tag.'"');
}

// Use this function to normalize headers to capital first letter
// Apache converts X-Something to x-something
function request_headers() {
	$headers = array();
	foreach ($_SERVER as $key => $value) {
		if (substr($key, 0, 5) != 'HTTP_') {
			continue;
		}
		$headername = strtr(ucwords(strtolower(strtr(substr($key, 5), '_', ' '))), ' ', '-');
		$headers[$headername] = $value;
	}
	return $headers;
}

if (!function_exists("apache_request_headers")){
	function apache_request_headers() {
		return request_headers();
	}
}

function get_if_modified() {
	// Get client headers - Apache only
	$request = apache_request_headers();
	if (isset($request['If-Modified-Since'])) {
	// Split the If-Modified-Since (Netscape < v6 gets this wrong)
		$modifiedSince = explode(';', $request['If-Modified-Since']);
		return strtotime($modifiedSince[0]);
	} else {
		return 0;
	}
}

function guess_user_id ($str) {
	global $db;

	if (preg_match('/^[0-9]+$/', $str)) {
		// It's a number, return it as id
		return intval($str);
	} else {
		$str = $db->escape(mb_substr($str,0,64));
		$id = intval($db->get_var("select user_id from users where user_login = '$str'"));
		return $id;
	}
}

function print_simpleformat_buttons($id) {
	global $globals, $current_user;

	// To avoid too many bolds and italics from new users and trolls
	if ($current_user->user_karma < 6.001) return;

	Haanga::Load('simpleformat_buttons.html', compact('id'));
}

function put_smileys($str) {
	global $globals;

	if ($globals['bot']) return $str;
	$str = preg_replace_callback('/\{([a-z]{3,10})\}/', 'put_smileys_callback', $str);
	return $str;
}

function put_smileys_callback(&$matches) {
	global $globals;
	static $translations = false;
	if (!$translations) {
		$translations = array(
			'wall' => ' <img src="'.$globals['base_static'].'img/smileys/wall2.gif" alt=":wall:" title=":wall:" width="24" height="18" /> ',
			'troll' => ' <img src="'.$globals['base_static'].'img/smileys/trollface2.gif" alt=":troll:" title=":troll:" width="15" height="15" /> ',
			'ffu' => ' <img src="'.$globals['base_static'].'img/smileys/fu.gif" alt=":ffu:" title=":ffu:" width="19" height="15" /> ',
			'palm' => ' <img src="'.$globals['base_static'].'img/smileys/palm.gif" alt=":palm:" title=":palm:" width="15" height="15" /> ',
			'goatse' => ' <img src="'.$globals['base_static'].'img/smileys/goat.gif" alt="goat" title="goat-ish" width="15" height="15" /> ',

			'wow' => ' <img src="'.$globals['base_static'].'img/smileys/wow.gif" alt="o_o" title="o_o :wow:" width="15" height="15" /> ',
			'shame' =>' <img src="'.$globals['base_static'].'img/smileys/shame.gif" alt="¬¬" title="¬¬ :shame:" width="15" height="15" /> ',
			'grin' =>' <img src="'.$globals['base_static'].'img/smileys/grin.gif" alt=":-D" title=":-D" width="15" height="15" /> ',
			'oops' => ' <img src="'.$globals['base_static'].'img/smileys/embarassed.gif" alt="&lt;&#58;(" title="&#58;oops&#58; &lt;&#58;("  width="15" height="15"/> ',
			'cool' => ' <img src="'.$globals['base_static'].'img/smileys/cool.gif" alt="8-D" title=":cool: 8-D" width="15" height="15"/> ',
			'roll' => ' <img src="'.$globals['base_static'].'img/smileys/rolleyes.gif" alt=":roll:" title=":roll:"	width="15" height="15"/> ',
			'cry' => ' <img src="'.$globals['base_static'].'img/smileys/cry.gif" alt=":\'(" title=":cry: :\'("	width="15" height="15"/> ',
			'lol' => ' <img src="'.$globals['base_static'].'img/smileys/laugh.gif" alt="xD" title=":lol: xD"  width="15" height="15"/> ',


			'smiley' => ' <img src="'.$globals['base_static'].'img/smileys/smiley.gif" alt=":-)" title=":-)" width="15" height="15" /> ',
			'wink' => ' <img src="'.$globals['base_static'].'img/smileys/wink.gif" alt=";)" title=";)"	width="15" height="15" /> ',
			'cheesy' => ' <img src="'.$globals['base_static'].'img/smileys/cheesy.gif" alt=":-&gt;" title=":-&gt;"	width="15" height="15" /> ',
			'angry' => ' <img src="'.$globals['base_static'].'img/smileys/angry.gif" alt="&gt;&#58;-(" title="&gt;&#58;-("	width="15" height="15" /> ',
			'huh' => ' <img src="'.$globals['base_static'].'img/smileys/huh.gif" alt="?(" title="?("  width="15" height="22" /> ',
			'sad' => ' <img src="'.$globals['base_static'].'img/smileys/sad.gif" alt=":-(" title=":-("	width="15" height="15" /> ',
			'shocked' => ' <img src="'.$globals['base_static'].'img/smileys/shocked.gif" alt=":-O" title=":-O"	width="15" height="15" />',
			'tongue' => ' <img src="'.$globals['base_static'].'img/smileys/tongue.gif" alt=":-P" title=":-P"  width="15" height="15" /> ',
			'lipssealed' => ' <img src="'.$globals['base_static'].'img/smileys/lipsrsealed.gif" alt=":-x" title=":-x"  width="15" height="15"/> ',
			'undecided' => ' <img src="'.$globals['base_static'].'img/smileys/undecided.gif" alt=":-/" title=":-/ :/"  width="15" height="15"/> ',
			'confused' => ' <img src="'.$globals['base_static'].'img/smileys/confused.gif" alt=":-S" title=":-S :S" width="15" height="15"/> ',
			'blank' => ' <img src="'.$globals['base_static'].'img/smileys/blank.gif" alt=":-|" title=":-| :|" width="15" height="15"/> ',
			'kiss' => ' <img src="'.$globals['base_static'].'img/smileys/kiss.gif" alt=":-*" title=":-* :*" width="15" height="15" /> ',
			'hug' => ' <img src="'.$globals['base_static'].'img/smileys/hug.gif" alt="hug" title="hug" width="39" height="15" /> ',
			'shit' => ' <img src="'.$globals['base_static'].'img/smileys/shit.gif" alt="shit" title="shit" width="15" height="15" /> ',
		);
	}
	return isset($translations[$matches[1]]) ? $translations[$matches[1]] : $matches[0];
}

function normalize_smileys($str) {
	global $globals;

	$str=preg_replace('/(\s|^):wall:/i', '$1{wall}', $str);
	$str=preg_replace('/(\s|^):troll:/i', '$1{troll}', $str);
	$str=preg_replace('/(\s|^):ffu:/i', '$1{ffu}', $str);
	$str=preg_replace('/(\s|^):palm:/i', '$1{palm}', $str);
	$str=preg_replace('/(\s|^):goatse:/i', '$1{goatse}', $str);
	$str=preg_replace('/(\s|^)o_o|:wow:/i', '$1{wow}', $str);
	$str=preg_replace('/(\s|^)¬¬|:shame:/i', '$1{shame}', $str);
	$str=preg_replace('/(\s|^):-{0,1}\)(\s|$)/i', '$1{smiley}$2', $str);
	$str=preg_replace('/(\s|^);-{0,1}\)(\s|$)/i', '$1{wink}$2', $str);
	$str=preg_replace('/(\s|^):-{0,1}&gt;/i', '$1{cheesy}', $str);
	$str=preg_replace('/(\s|^)(:-{0,1}D|:grin:)/i', '$1{grin}', $str);
	$str=preg_replace('/(\s|^)(:oops:|&lt;:\()/i', '$1{oops}', $str);
	$str=preg_replace('/(\s|^)&gt;:-{0,1}\((\s|$)/i', '$1{angry}$2', $str);
	$str=preg_replace('/(\s|^)\?(:-){0,1}\((\s|$)/i', '$1{huh}$2', $str);
	$str=preg_replace('/(\s|^):-{0,1}\((\s|$)/i', '$1{sad}$2', $str);
	$str=preg_replace('/(\s|^):-{0,1}O/', '$1{shocked}', $str);
	$str=preg_replace('/(\s|^)(8-{0,1}[D\)]|:cool:)/', '$1{cool}', $str);
	$str=preg_replace('/(\s|^):roll:/i', '$1{roll}', $str);
	$str=preg_replace('/(\s|^):-{0,1}P(\s|$)/i', '$1{tongue}$2', $str);
	$str=preg_replace('/(\s|^):-{0,1}x/i', '$1{lipssealed}', $str);
	$str=preg_replace('/(\s|^):-{0,1}\//i', '$1{undecided}', $str);
	$str=preg_replace('/(\s|^)(:\'\(|:cry:)/i', '$1{cry}', $str);
	$str=preg_replace('/(\s|^)(x-{0,1}D+|:lol:)/i', '$1{lol}', $str);
	$str=preg_replace('/(\s|^):-{0,1}S(\s|$)/i', '$1{confused}$2', $str);
	$str=preg_replace('/(\s|^):-{0,1}\|/i', '$1{blank}', $str);
	$str=preg_replace('/(\s|^):-{0,1}\*/i', '$1{kiss}', $str);
	$str=preg_replace('/(\s|^):hug:/i', '$1{hug}', $str);
	$str=preg_replace('/(\s|^):shit:/i', '$1{shit}', $str);
	return $str;
}


function meta_get_current() {
	global $globals, $db, $current_user;

	$globals['meta_current'] = '';
	if (!empty($_REQUEST['meta']) && $_REQUEST['meta'][0] == '_') {
		$globals['meta']  = clean_input_string($_REQUEST['meta']);
	} else {
		$globals['meta'] = '';
	}


	//Check for personalisation
	// Authenticated users
	if (empty($globals['submnm']) && $current_user->user_id > 0) {
		$subs = $db->get_col("SELECT pref_value FROM prefs WHERE pref_user_id = $current_user->user_id and pref_key = 'sub_follow' order by pref_value");
		if ($subs) {
			$current_user->has_subs = true;
			$globals['meta_skip'] = '?meta=_all';
			if (! $globals['meta']) {
				$globals['meta_subs'] = implode(',', $subs);
				$globals['meta'] = '_subs';
			}
		} else {
			$globals['meta_subs'] = false;
		}
	}
}

function fork($uri) {
	global $globals;

	$sock = @fsockopen(get_server_name(), $_SERVER['SERVER_PORT'], $errno, $errstr, 0.01 );

	if ($sock) {
		@fputs($sock, "GET {$globals['base_url_general']}$uri HTTP/1.0\r\n" . "Host: {$_SERVER['SERVER_NAME']}\r\n\r\n");
		return true;
	}
	return false;
}

function stats_increment($type, $all=false) {
	global $globals, $db;

	if ($globals['save_pageloads']) {
		if(!$globals['bot'] || $all) {
			$db->query("insert into pageloads (date, type, counter) values (now(), '$type', 1) on duplicate key update counter=counter+1");
		} else {
			$db->query("insert into pageloads (date, type, counter) values (now(), 'bot', 1) on duplicate key update counter=counter+1");
		}
	}
}

// Json basic functions

function json_encode_single($dict) {
	$item = '{';
	$passed = 0;
	foreach ($dict as $key => $val) {
		if ($passed) $item .= ',';
		$item .= "\"$key\": \"$val\"";
		$passed = 1;
	}
	return $item . '}';
}

//
// Memcache functions
//
// Uses also xcache if enabled and available

$memcache = false;

function memcache_menabled () {
	global $globals;

	return !empty($globals['memcache_host']) || ($globals['xcache_enabled'] && defined('XC_TYPE_VAR'));
}

function memcache_minit () {
	global $memcache, $globals;

	if ($memcache) return true;
	if ($globals['memcache_host']) {
		$memcache = new Memcache;
		if (!isset($globals['memcache_port'])) $globals['memcache_port'] = 11211;
		if ( ! @$memcache->pconnect($globals['memcache_host'], $globals['memcache_port']) ) {
			$memcache = false;
			syslog(LOG_INFO, "Meneame: memcache init failed");
			return false;
		}
		return true;
	}
	return false;
}

function memcache_mget ($key) {
	global $memcache, $globals;

	// Use xcache vars if enabled and available
	if ($globals['xcache_enabled'] && defined('XC_TYPE_VAR')) {
		if (xcache_isset($key)) {
			return xcache_get($key);
		} else {
			return false;
		}
	}

	// Check for memcache
	if (memcache_minit()) return $memcache->get($key);
	return false;
}


function memcache_madd ($key, $value, $expire=3600) {
	global $memcache, $globals;

	// Use xcache vars if enabled and available
	if ($globals['xcache_enabled'] && defined('XC_TYPE_VAR')) {
		return xcache_set($key, $value, $expire);
	}

	// Check for memcache
	if (memcache_minit()) return $memcache->set($key, $value, 0, $expire);
	return false;
}

function memcache_mprint ($key) {
	global $memcache, $globals;

	// Use xcache vars if enabled and available
	if ($globals['xcache_enabled'] && defined('XC_TYPE_VAR')) {
		if (xcache_isset($key)) {
			echo xcache_get($key);
			return true;
		}
		return false;
	}

	// Check for memcache
	if (memcache_minit() && ($value = $memcache->get($key))) {
		echo $value;
		return true;
	}
	return false;
}

function memcache_mdelete ($key) {
	global $memcache, $globals;

	// Use xcache vars if enabled and available
	if ($globals['xcache_enabled'] && defined('XC_TYPE_VAR')) {
		return xcache_unset($key);
	} elseif (memcache_minit()) {
		return $memcache->delete($key);
	}
	return false;
}

// Generic function to get content from an url
function get_url($url, $referer = false, $max=500000, $log =true) {
	if ($max == null) $max = 500000; // Ensure default value

	global $globals;
	static $session = false;
	static $previous_host = false;


	$url = html_entity_decode($url);
	$parsed = parse_url($url);
	if (!$parsed) return false;

	if ($session && $previous_host != $parsed['host']) {
		curl_close($session);
		$session = false;
	}
	if (!$session) {
		$session = curl_init();
		$previous_host =  $parsed['host'];
	}
	$url = preg_replace('/ /', '%20', $url);
	curl_setopt($session, CURLOPT_URL, $url);
	curl_setopt($session, CURLOPT_USERAGENT, $globals['user_agent']);
	if ($referer) curl_setopt($session, CURLOPT_REFERER, $referer);
	curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 20);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($session, CURLOPT_HEADER , true );
	curl_setopt($session, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($session, CURLOPT_MAXREDIRS, 20);
	curl_setopt($session, CURLOPT_TIMEOUT, 25);
	curl_setopt($session, CURLOPT_FAILONERROR, true);
	curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($session, CURLOPT_COOKIESESSION, true);
	curl_setopt($session, CURLOPT_COOKIEFILE, "/dev/null");
	curl_setopt($session, CURLOPT_COOKIEJAR, "/dev/null");
	//curl_setopt($session,CURLOPT_RANGE,"0-$max"); // It gives error with some servers
	$response = @curl_exec($session);
	if (!$response && $log) {
			syslog(LOG_INFO, "Meneame: CURL error " . curl_getinfo($session,CURLINFO_EFFECTIVE_URL) . ": " .curl_error($session));
			echo "<! -- CURL error " . curl_getinfo($session,CURLINFO_EFFECTIVE_URL) . ": " .curl_error($session) . " -->\n";
			return false;
	}
	$header_size = curl_getinfo($session,CURLINFO_HEADER_SIZE);
	$result['header'] = substr($response, 0, $header_size);
	$result['content'] = substr($response, $header_size, $max);
	if (preg_match('/Content-Encoding: *gzip/i', $result['header'])) {
			$result['content'] = gzBody($result['content']);
			echo "<!-- get_url gzinflating -->\n";
	}
	$result['http_code'] = curl_getinfo($session,CURLINFO_HTTP_CODE);
	$result['content_type'] = curl_getinfo($session, CURLINFO_CONTENT_TYPE);
	$result['redirect_count'] = curl_getinfo($session, CURLINFO_REDIRECT_COUNT);
	$result['location'] = curl_getinfo($session, CURLINFO_EFFECTIVE_URL);
	return $result;
}

// From http://es2.php.net/manual/en/function.gzinflate.php#77336
function gzBody($gzData){
	if(substr($gzData,0,3)=="\x1f\x8b\x08"){
		$i=10;
		$flg=ord(substr($gzData,3,1));
		if($flg>0){
			if($flg&4){
				list($xlen)=unpack('v',substr($gzData,$i,2));
				$i=$i+2+$xlen;
			}
			if($flg&8) $i=strpos($gzData,"\0",$i)+1;
			if($flg&16) $i=strpos($gzData,"\0",$i)+1;
			if($flg&2) $i=$i+2;
		}
		return gzinflate(substr($gzData,$i,-8));
	}
	else return false;
}

function clear_invisible_unicode($input){
	$input = preg_replace('/[\x00-\x09]/', '', $input);

	$invisible = array(
	"\x1F",
	"\x7f", // (U+007f)
	"\xc2\xad", // 'SOFT HYPHEN' (U+00AD)
	"\xc2\x81", // (U+0081)
	"\xc2\x91", // (U+0091)
	"\xc2\x92", // (U+0092)
	"\xc2\x93", // (U+0093)
	"\xc2\x94", // (U+0094)
	"\xc2\x9d", // (U+009D)
	"\xcc\xb7", // (U+0337)
	"\xcc\xb8", // 'COMBINING LONG SOLIDUS OVERLAY' (U+0338)
	"\xcd\x8f", // 'COMBINING GRAPHEME JOINER' (U+034F)
	"\xe1\x85\x9f", // 'HANGUL CHOSEONG FILLER' (U+115F)
	"\xe1\x85\xa0", // 'HANGUL JUNGSEONG FILLER' (U+1160)
	"\xe2\x80\x8b", // 'ZERO WIDTH SPACE' (U+200B)
	"\xe2\x80\x8c", // 'ZERO WIDTH NON-JOINER' (U+200C)
	"\xe2\x80\x8d", // 'ZERO WIDTH JOINER' (U+200D)
	"\xe2\x80\x8e", // 'LEFT-TO-RIGHT MARK' (U+200E)
	"\xe2\x80\x8f", // 'RIGHT-TO-LEFT MARK' (U+200F)
	"\xe2\x80\xaa", // 'LEFT-TO-RIGHT EMBEDDING' (U+202A)
	"\xe2\x80\xab", // 'RIGHT-TO-LEFT EMBEDDING' (U+202B)
	"\xe2\x80\xac", // 'POP DIRECTIONAL FORMATTING' (U+202C)
	"\xe2\x80\xad", // 'LEFT-TO-RIGHT OVERRIDE' (U+202D)
	"\xe2\x80\xae", // 'RIGHT-TO-LEFT OVERRIDE' (U+202E)
	"\xe3\x85\xa4", // 'HANGUL FILLER' (U+3164)
	"\xef\xbb\xbf", // 'ZERO WIDTH NO-BREAK SPACE' (U+FEFF)
	"\xef\xbe\xa0", // 'HALFWIDTH HANGUL FILLER' (U+FFA0)
	"\xef\xbf\xb9", // 'INTERLINEAR ANNOTATION ANCHOR' (U+FFF9)
	"\xef\xbf\xba", // 'INTERLINEAR ANNOTATION SEPARATOR' (U+FFFA)
	"\xef\xbf\xbb", // 'INTERLINEAR ANNOTATION TERMINATOR' (U+FFFB)
	);

	return str_replace($invisible, '', $input);

}

function clear_unicode_spaces($input){
	$spaces = array(
	"\x9", // 'CHARACTER TABULATION' (U+0009)
	//	"\xa", // 'LINE FEED (LF)' (U+000A)
	"\xb", // 'LINE TABULATION' (U+000B)
	"\xc", // 'FORM FEED (FF)' (U+000C)
	"\x10", 
	//	"\xd", // 'CARRIAGE RETURN (CR)' (U+000D)
	"\x20", // 'SPACE' (U+0020)
	"\xc2\xa0", // 'NO-BREAK SPACE' (U+00A0)
	"\xe1\x9a\x80", // 'OGHAM SPACE MARK' (U+1680)
	"\xe1\xa0\x8e", // 'MONGOLIAN VOWEL SEPARATOR' (U+180E)
	"\xe2\x80\x80", // 'EN QUAD' (U+2000)
	"\xe2\x80\x81", // 'EM QUAD' (U+2001)
	"\xe2\x80\x82", // 'EN SPACE' (U+2002)
	"\xe2\x80\x83", // 'EM SPACE' (U+2003)
	"\xe2\x80\x84", // 'THREE-PER-EM SPACE' (U+2004)
	"\xe2\x80\x85", // 'FOUR-PER-EM SPACE' (U+2005)
	"\xe2\x80\x86", // 'SIX-PER-EM SPACE' (U+2006)
	"\xe2\x80\x87", // 'FIGURE SPACE' (U+2007)
	"\xe2\x80\x88", // 'PUNCTUATION SPACE' (U+2008)
	"\xe2\x80\x89", // 'THIN SPACE' (U+2009)
	"\xe2\x80\x8a", // 'HAIR SPACE' (U+200A)
	"\xe2\x80\xa8", // 'LINE SEPARATOR' (U+2028)
	"\xe2\x80\xa9", // 'PARAGRAPH SEPARATOR' (U+2029)
	"\xe2\x80\xaf", // 'NARROW NO-BREAK SPACE' (U+202F)
	"\xe2\x81\x9f", // 'MEDIUM MATHEMATICAL SPACE' (U+205F)
	"\xe3\x80\x80", // 'IDEOGRAPHIC SPACE' (U+3000)
	);

	return str_replace($spaces, ' ', $input);
}

function clear_whitespace($input){
	$input = clear_unicode_spaces(clear_invisible_unicode($input));
	return preg_replace('/ {5,}/', ' ', $input);
}


// IP and chec_proxy functions

function isIPIn($ip,$net,$mask) {
		$lnet=ip2long($net);
		$lip=ip2long($ip);
		$binnet=str_pad( decbin($lnet),32,"0", STR_PAD_LEFT);
		$firstpart=substr($binnet,0,$mask);
		$binip=str_pad( decbin($lip),32,"0", STR_PAD_LEFT);
		$firstip=substr($binip,0,$mask);
		return(strcmp($firstpart,$firstip)==0);
}


function isPrivateIP($ip) {
		$privates = array ("127.0.0.0/24", "10.0.0.0/8", "172.16.0.0/12", "192.168.0.0/16");
		foreach ( $privates as $k ) {
				list($net,$mask)=preg_split("#/#",$k);
				if (isIPIn($ip,$net,$mask)) {
						return true;
				}
		}
		return false;
}

function check_ip_behind_load_balancer() {
	// It's similar to behind_proxy but faster and only takes in account
	// the last IP in the list.
	// Used to get the real IP behind a load balancer like Amazon ELB
	// WARN: does not check for valid IP, it must be a trusted proxy/load balancer
	if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
		$ips = preg_split('/[, ]/', $_SERVER["HTTP_X_FORWARDED_FOR"], -1, PREG_SPLIT_NO_EMPTY);
		$ip = array_pop($ips);
		if ($ip) return $ip;
	}
	return $_SERVER["REMOTE_ADDR"];
}

function check_ip_behind_proxy() {
	static $last_seen = '';

	if(!empty($last_seen) ) return $last_seen;

	if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
		$user_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} else if ($_SERVER["HTTP_CLIENT_IP"]) {
		$user_ip = $_SERVER["HTTP_CLIENT_IP"];
	} else {
		$last_seen = $_SERVER["REMOTE_ADDR"];
		return $last_seen;
	}

	$ips = preg_split('/[, ]/', $user_ip, -1, PREG_SPLIT_NO_EMPTY);
	foreach ($ips as $last_seen) {
		if (preg_match('/^[1-9]\d{0,2}\.(\d{1,3}\.){2}[1-9]\d{0,2}$/s', $last_seen)
			&& !isPrivateIP($last_seen) ) {
			return $last_seen;
		}
	}

	$last_seen = $_SERVER["REMOTE_ADDR"];
	return $last_seen;
}

/**
 * Convert an IP address from string/presentation format to decimal(39.0) format
 * See: http://stackoverflow.com/questions/1120371/how-to-convert-ipv6-from-binary-for-storage-in-mysql
 */

function inet_ptod($ip_address) {

	if (empty($ip_address)) {
		return 0;
	}

	// IPv4 address
	if (strpos($ip_address, ':') === false && strpos($ip_address, '.') !== false) {
		return sprintf("%u", ip2long($ip_address));
	}

	// IPv6 address
	$packed_ip = inet_pton($ip_address);
	if ($packed_ip === FALSE) {
		syslog(LOG_INFO, "Bad ip address in inet_pton: $ip_address X-Forwarded: " . $_SERVER["HTTP_X_FORWARDED_FOR"]);
		return 0;
	}

	$parts = unpack('N*', $packed_ip);

	foreach ($parts as &$part) {
			if ($part < 0) {
					$part = bcadd((string) $part, '4294967296');
			}
			if (!is_string($part)) {
					$part = (string) $part;
			}
	}
	bcscale(0);
	$decimal = $parts[4];
	$decimal = bcadd($decimal, bcmul($parts[3], '4294967296'));
	$decimal = bcadd($decimal, bcmul($parts[2], '18446744073709551616'));
	$decimal = bcadd($decimal, bcmul($parts[1], '79228162514264337593543950336'));

	return $decimal;
}

/**
 * Convert an IP address from decimal to presentation format
 */
function inet_dtop($decimal) {
	// Decimal format
	bcscale(0);
	$parts = array();
	$parts[1] = bcdiv($decimal, '79228162514264337593543950336', 0);
	$decimal = bcsub($decimal, bcmul($parts[1], '79228162514264337593543950336'));
	$parts[2] = bcdiv($decimal, '18446744073709551616', 0);
	$decimal = bcsub($decimal, bcmul($parts[2], '18446744073709551616'));
	$parts[3] = bcdiv($decimal, '4294967296', 0);
	$decimal = bcsub($decimal, bcmul($parts[3], '4294967296'));
	$parts[4] = $decimal;

	foreach ($parts as &$part) {
		if (bccomp($part, '2147483647') == 1) {
				$part = bcsub($part, '4294967296');
		}

		$part = (int) $part;
	}

	$network = pack('N4', $parts[1], $parts[2], $parts[3], $parts[4]);
	$ip_address = inet_ntop($network);

	// Turn IPv6 to IPv4 if it's IPv4
	if (preg_match('/^::\d+.\d+.\d+.\d+$/', $ip_address)) {
		return substr($ip_address, 2);
	}

	return $ip_address;
}

function http_cache($maxage = 30) {
	// Send cache control
	global $globals, $current_user;

	if ($current_user->user_id) $globals['cache-control'][] = 's-maxage=0, private, community="'.$current_user->user_login.'"';

	if ($globals['cache-control']) header('Cache-Control: ' . implode(', ', $globals['cache-control']));
	else header('Cache-Control: s-maxage='.$maxage);
}

// Used to store countes, in order to avoid expensives select count(*)
function get_count($key, $seconds = 7200) { // Every two hours by default
	global $db;
	$res = $db->get_row("select `count` from counts where `key` = '$key' and date > date_sub(now(), interval $seconds second)");
	if ($res) return $res->count;
	else return false;
}

function set_count($key, $count) {
	global $db;

	return $db->query("REPLACE INTO counts (`key`, `count`) VALUES ('$key', $count)");
}

function print_oauth_icons($return = false) {
	global $globals, $current_user;

	if (! $return) {
		$return = $globals['uri'];
	}
	$return = htmlentities($return);

	echo '<div class="auth-buttons">';
	if ($globals['oauth']['twitter']['consumer_key']) {
		$title = false;
		if ($current_user->user_id) {
			// Check the user is not already associated to Twitter
			if (! $current_user->GetOAuthIds('twitter')) {
				$title = _('asociar la cuenta a Twitter, podrás autentificarte también con tu cuenta en Twitter');
				$text = _('asociar a Twitter');
			}
		} else {
			$title = _('crea una cuenta o autentifícate desde Twitter');
			$text = _('login con Twitter');
		}
		if ($title) {
			echo '<a href="'.$globals['base_url_general'].'oauth/signin.php?service=twitter&amp;op=init&amp;return='.$return.'" title="'.$title.'">';
			echo '<img src="'.$globals['base_static'].'img/external/signin-twitter2.png" width="89" height="21" alt=""/></a>';
		}
	}

	if ($globals['facebook_key']) {
		$title = false;
		if ($current_user->user_id) {
			// Check the user is not already associated to Twitter
			if (! $current_user->GetOAuthIds('facebook')) {
				$title = _('asociar la cuenta a Facebook, podrás autentificarte también con tu cuenta en Facebook');
				$text = _('asociar a Facebook');
			}
		} else {
			$title = _('crea una cuenta o autentifícate desde Facebook');
			$text = _('login con Facebook');
		}
		if ($title) {
			echo '<a href="'.$globals['base_url_general'].'oauth/fbconnect.php?return='.$return.'" title="'.$title.'">';
			echo '<img src="'.$globals['base_static'].'img/external/signin-fb.gif" width="89" height="21" alt=""/></a>';
		}
	}

	if ($globals['oauth']['gplus']['consumer_key']) {
		$title = false;
		if ($current_user->user_id) {
			// Check the user is not already associated to Twitter
			if (! $current_user->GetOAuthIds('gplus')) {
				$title = _('asociar la cuenta a Google+, podrás autentificarte también con tu cuenta en Google+');
				$text = _('asociar a Google+');
			}
		} else {
			$title = _('crea una cuenta o autentifícate desde Google+');
			$text = _('login con Google+');
		}
		if ($title) {
			echo '<a href="'.$globals['base_url_general'].'oauth/signin.php?service=gplus&amp;op=init&amp;return='.$return.'" title="'.$title.'">';
			echo '<img src="'.$globals['base_static'].'img/external/signin-gplus.png" width="89" height="21" alt=""/></a>';
		}
	}
	echo '</div>';
}

function backend_call_string($program,$type,$page,$id) {
	// It replaces the get_votes function
	// it generates the string to link to a backend program given its arguments
	global $globals;

	return $globals['base_url_general']."backend/$program?id=$id&amp;p=$page&amp;type=$type&amp;key=".$globals['security_key'];
}

function check_load($max=4) {
	$load = sys_getloadavg();
	if ($load[0] > $max) {
		header('HTTP/1.1 503 Too busy, try again later');
		die();
	}
}

function check_ip_noaccess($steps = 0) {
	global $globals, $db;

	if ( empty($globals['check_ip_noaccess'])
		|| ($steps == 1 && empty($globals['check_ip_noaccess_cache']))
		|| ($globals['proxy_ip'] == $globals['user_ip'])
		|| !empty($globals['skip_check_ip_noaccess'])
		) {
			return true; // Don't callme again
		}

	if (! empty($globals['check_ip_noaccess_cache']) && $globals['check_ip_noaccess_cache'] > 0) {
		$cache_key = 'noaccess_'.$globals['user_ip'];
	} else {
		$cache_key = false;
	}

	if ($steps < 2 && $cache_key) { // Don't check cache if >= 2
		$match = memcache_mget($cache_key);
		if ($match !== false) {
			if ($steps == 1 && empty($match)) { // Only in cache and found it's 0
				return true; // OK
			} elseif (!empty($match)) {
				reject_connection($match);
			} 
		}
		return false; // Not found in cache
	} 

	$res = $db->get_var('SELECT ban_comment FROM bans WHERE ban_text = "'.$globals['user_ip'].'" AND ban_type = "noaccess" AND (ban_expire IS null OR ban_expire > now()) LIMIT 1');
	if ($res) {
		// If the first word is an URL, force a redirection to it
		// aka: eat you own dog food, bitch
		$url = explode(' ', ltrim($res), 2);
		if ($url && preg_match('/^https{0,1}:\/\/.{5,}/', $url[0])) {
			$match = $url[0];
		} else {
			$match = 1;
		}
	} else {
		$match = 0;
	}

	if ($cache_key) {
		if ($match) $ttl = 60; // Blocked IPs cached for 60 seconds
		else $ttl = $globals['check_ip_noaccess_cache'];
		memcache_madd ($cache_key, $match, $ttl);
	}
	if (! empty($match)) {
		reject_connection($match);
	}

	return true;
}

function reject_connection($redirect = false) {
	global $globals, $db;

	if (is_object($db)) $db->close();

	// $globals['access_log'] = false; // Don't log it to avoid repeated bans
	$globals['ip_blocked'] = true;

	usleep(500000);
	if (is_string($redirect) && strlen($redirect) > 10) {
		redirect($redirect);
	} else {
		header('HTTP/1.0 403 ' . 'Too many connections');
	}
	die;
}

function redirect($url, $code = 301) {
	global $globals;

	if ($globals['partial']) {
		if (preg_match('/\?/', $url)) {
			$url .= '&partial';
		} else {
			$url .= '?partial';
		}
	}
    header("HTTP/1.1 $code Moved");
    header('Location: ' . $url);
    header("Content-Length: 0");
}


function close_connection(){
	if (function_exists('fastcgi_finish_request')) {
		fastcgi_finish_request();
	}
}

function add_javascript($code) {
	echo '<script type="text/javascript">';
    echo 'addPostCode(\''.$code.'\');';
    echo '</script>';
}

?>
