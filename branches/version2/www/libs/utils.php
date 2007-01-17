<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


// Use proxy detecttion
if ($globals['check_behind_proxy']) {
	require_once(mnminclude.'check_behind_proxy.php');
	$globals['user_ip'] = check_ip_behind_proxy();
} else {
	$globals['user_ip'] = $_SERVER["REMOTE_ADDR"];
}

// Warn, we shoud printf "%u" because PHP on 32 bits systems fails with high unsigned numbers
$globals['user_ip_int'] = sprintf("%u", ip2long($globals['user_ip']));

$globals['negative_votes_values'] = Array ( -1 => _('irrelevante'), -2 => _('antigua'), -3 => _('cansina'), -4 => _('amarillista'), -5 => _('spam'), -6 => _('duplicada'), -7 => _('provocación'), -8 => _('errónea') );

mb_internal_encoding('UTF-8');

// For PHP < 5
if ( !function_exists('htmlspecialchars_decode') ) {
	function htmlspecialchars_decode($text) {
		return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
	}
}


// Check the user's referer.
if( !empty($_SERVER['HTTP_REFERER'])) {
	if (preg_match('/http:\/\/'.preg_quote($_SERVER['SERVER_NAME']).'/', $_SERVER['HTTP_REFERER'])) {
		$globals['referer'] = 'local';
	} elseif (preg_match('/q=|search/', $_SERVER['HTTP_REFERER']) ) {
		$globals['referer'] = 'search';
	} else {
		$globals['referer'] = 'remote';
	}
} else {
	$globals['referer'] = 'unknown';
}

function htmlentities2unicodeentities ($input) {
	$htmlEntities = array_values (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
	$entitiesDecoded = array_keys  (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
	$num = count ($entitiesDecoded);
	for ($u = 0; $u < $num; $u++) {
		$utf8Entities[$u] = '&#'.ord($entitiesDecoded[$u]).';';
	}
	return str_replace ($htmlEntities, $utf8Entities, $input);
}

function clean_input_url($string) {
	$string = preg_replace('/ /', '+', trim(stripslashes($string)));
	return preg_replace('/[<>\r\n\t]/', '', $string);
}

function clean_input_string($string) {
	return preg_replace('/[ <>\'\"\r\n\t\(\)]/', '', stripslashes($string));
}

function get_negative_vote($value) {
	global $globals;
	return $globals['negative_votes_values'][$value];
}

function user_exists($username) {
	global $db;
	$username = $db->escape($username);
	$res=$db->get_var("SELECT count(*) FROM users WHERE user_login='$username'");
	if ($res>0) return true;
	return false;
}

function email_exists($email) {
	global $db;
	$email = $db->escape($email);
	$res=$db->get_var("SELECT count(*) FROM users WHERE user_email='$email'");
	if ($res>0) return $res;
	return false;
}

function check_ban_list($what, $list) {
	if (!empty($list)) { 
		$domains = preg_split("/[\s,]+/", $list);
		foreach ($domains as $domain) {
			if (preg_match("/$domain$/i", $what))
				return true;
		}
	}
	return false;
}

function check_email($email) {
	global $globals;
	if (! preg_match('/^[a-zA-Z0-9_\-\.]+(\+[a-zA-Z0-9_\-\.]+)*@[a-zA-Z0-9_\-\.]+\.[a-zA-Z]{2,4}$/', $email)) 
		return false;
	if(check_ban_list($email, $globals['forbidden_email_domains'])) return false;
	return true;
}

function url_clean($url) {
	$array = explode('#', $url, 1);
	return $array[0];
}

function check_username($name) {
	return (preg_match('/^[a-z0-9_\-\.çÇñÑ·]+$/i', $name) && strlen($name) <= 24);
}


function txt_time_diff($from, $now=0){
	$txt = '';
	if($now==0) $now = time();
	$diff=$now-$from;
	$days=intval($diff/86400);
	$diff=$diff%86400;
	$hours=intval($diff/3600);
	$diff=$diff%3600;
	$minutes=intval($diff/60);

	if($days>1) $txt  .= " $days "._('días');
	else if ($days==1) $txt  .= " $days "._('día');

	if($hours>1) $txt .= " $hours "._('horas');
	else if ($hours==1) $txt  .= " $hours "._('hora');

	if($minutes>1) $txt .= " $minutes "._('minutos');
	else if ($minutes==1) $txt  .= " $minutes "._('minuto');

	if($txt=='') $txt = ' '. _('pocos segundos');
	return $txt;
}

function txt_shorter($string, $len=70) {
	if (strlen($string) > $len)
		$string = substr($string, 0, $len-3) . "...";
	return $string;
}

// Used to get the text content for stories and comments
function clean_text($string, $wrap=0, $replace_nl=true, $maxlength=0) {
	$string = stripslashes(trim($string));
	$string = html_entity_decode($string, ENT_COMPAT, 'UTF-8');
	if ($wrap>0) $string = wordwrap($string, $wrap, " ", 1);
	if ($replace_nl) $string = preg_replace('/[\n\t\r]+/s', ' ', $string);
	if ($maxlength > 0) $string = mb_substr($string, 0, $maxlength);
	return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
}

function clean_lines($string) {
	return preg_replace('/[\n\r]{6,}/', "\n\n", $string);
}

function save_text_to_html($string) {
	//$string = strip_tags(trim($string));
	//$string= htmlspecialchars(trim($string));
	$string= text_to_html($string);
	$string = preg_replace("/\r\n|\r|\n/", "\n<br />\n", $string);
	return $string;
}

function text_to_html($string) {
	// Dirty trick to allow tagging consecutives words 
	//$string = preg_replace('/([_*[0-9]) ([#_*])/', "$1  $2", $string);

	$string = preg_replace('/([\W\s]|^)(https*:\/\/)([^ \t\n\r\]\(\)]{5,60})([^ \t\n\r\]\(\)]*)([^ .\t,\n\r\(\)\"\'\]\?])/', '$1<a href="$2$3$4$5" title="$2$3$4$5" rel="nofollow">$3$5</a>', $string);
	$string = preg_replace('/(^|[\W\s])_([^\s<>]+)_/', "$1<em>$2</em>", $string);
	$string = preg_replace('/(^|[\W\s])\*([^\s<>]+)\*/', "$1<strong>$2</strong>", $string);
	return $string;
}

function check_integer($which) {
	if (is_numeric($_REQUEST[$which])) {
		return intval($_REQUEST[$which]);
	} else {
		return false;
	}
}

function get_current_page() {
	if(($var=check_integer('page'))) {
		return $var;
	} else {
		return 1;
	}
    // return $_GET['page']>0 ? $_GET['page'] : 1;
}

function get_search_clause($option='') {
	global $db;
	if($option == 'boolean') {
		$mode = 'IN BOOLEAN MODE';
	}
	if(!empty($_REQUEST['search'])) {
		$_REQUEST['search'] = trim(substr(strip_tags($_REQUEST['search']), 0, 250));
		$words_count = count(explode(" ", $_REQUEST['search']));
		$words = $db->escape($_REQUEST['search']);
		if (preg_match('/^tag:/', $words)) {
			$_REQUEST['tag'] = 'true';
			$words=preg_replace('/^tag: */', '', $words);
		} elseif (preg_match('/^date:/', $words) || $words_count == 1) {
			$_REQUEST['date'] = 'true';
			$mode = 'IN BOOLEAN MODE';
			$words=preg_replace('/^date: */', '', $words);
		}
		if ($_REQUEST['tag'] == 'true') {
			$where .= "MATCH (link_tags) AGAINST ('$words' $mode) ";
		} elseif ($words_count == 1 && preg_match('/^http[s]*:\/\/|^www\./', $words)) {
			// With URLs, search with "like" because mysql (5.0) give erroneous results otherwise
			$where = "link_url like '%$words%' ";
		} else {
			$where = "MATCH (link_url, link_url_title, link_title, link_content, link_tags) AGAINST ('$words' $mode) ";
		}
		if (!empty($_REQUEST['from'])) {
			$where .=  " AND link_date > from_unixtime(".intval($_REQUEST['from']).") ";
		}
		// To avoid showing news still in "limbo"
		// it also avoid to show old discarded news
		$where .=  " AND (link_status != 'discard' OR (link_status = 'discard' AND link_date > date_sub(now(), interval 7 day) AND link_votes > 0)) ";
		return $where;
	} else {
		return false;
	}
}

function get_date($epoch) {
    return date("Y-m-d", $epoch);
}

function get_date_time($epoch) {
	    //return date("Y-m-d H:i", $epoch);
	    return date(" d-m-Y H:i", $epoch);
}

function get_server_name() {
	global $server_name;
	if(empty($server_name)) 
		return $_SERVER['SERVER_NAME'];
	else
		return $server_name;
}

function get_user_uri($user, $view='') {
	global $globals;

	if (!empty($globals['base_user_url'])) {
		$uri= $globals['base_url'] . $globals['base_user_url'] . htmlspecialchars($user);
		if (!empty($view)) $uri .= "/$view";
	} else {
		$uri = $globals['base_url'].'user.php?login='.htmlspecialchars($user);
		if (!empty($view)) $uri .= "&amp;view=$view";
	}
	return $uri;
}

function get_avatar_url($user, $avatar, $size) {
	global $globals; 
	if ($avatar > 0 && !empty($globals['avatars_dir'])) {
		$file = $globals['avatars_dir'] . '/'. intval($user/$globals['avatars_files_per_dir']) . '/' . $user . "-$size.jpg";
		$file_path = mnmpath.'/'.$file;
		if (is_readable($file_path)) {
			return $globals['base_url'] . $file;
		} else {
			return $globals['base_url'] . "backend/get_avatar.php?id=$user&amp;size=$size";
		}
	} 
	return get_no_avatar_url($size);
}

function get_no_avatar_url($size) {
	global $globals;
	return $globals['base_url'].'img/common/no-gravatar-2-'.$size.'.jpg';
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

function not_found() {
	header("HTTP/1.0 404 Not Found");
	header("Status: 404 Not Found");
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";
    echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$dblang.'" lang="'.$dblang.'">' . "\n";
    echo '<head>' . "\n";
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
    echo "<title>". _('error') . "</title>\n";
    echo '<meta name="generator" content="meneame" />' . "\n";
    echo '<link rel="icon" href="'.$globals['base_url'].'img/favicons/favicon4.ico" type="image/x-icon" />' . "\n";
    echo '</head>' . "\n";
    echo "<body>\n";
	echo '<h1>' . _('error') . ' 3.1415926536</h1><p>' . _('no encontrado') . '</p>';
	echo "</body></html>\n";
	exit;
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

function print_simpleformat_buttons($textarea_id) {
	global $globals;
	echo '<img onclick="applyTag(\''.$textarea_id.'\', \'*\');" src="'.$globals['base_url'].'img/common/richeditor-bold-01.png" alt="bold" class="rich-edit-key" />';
	echo '<img onclick="applyTag(\''.$textarea_id.'\', \'_\');" src="'.$globals['base_url'].'img/common/richeditor-italic-01.png" alt="italic" class="rich-edit-key" />';
}
?>
