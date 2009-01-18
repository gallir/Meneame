<?
// The source code packaged with this file is Free Software, Copyright (C) 2005-2009 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

mb_internal_encoding('UTF-8');

// Use proxy detecttion
if ($globals['check_behind_proxy']) {
	require_once(mnminclude.'check_behind_proxy.php');
	$globals['user_ip'] = check_ip_behind_proxy();
} else {
	$globals['user_ip'] = $_SERVER["REMOTE_ADDR"];
}

// Warn, we shoud printf "%u" because PHP on 32 bits systems fails with high unsigned numbers
$globals['user_ip_int'] = sprintf("%u", ip2long($globals['user_ip']));

$globals['now'] = time();

$globals['negative_votes_values'] = Array ( -1 => _('irrelevante'), -2 => _('antigua'), -3 => _('cansina'), -4 => _('amarillista'), -5 => _('spam'), -6 => _('duplicada'), -7 => _('provocación'), -8 => _('errónea'),  -9 => _('copia/plagio'));


$globals['extra_js'] = Array();
$globals['extra_css'] = Array();
$globals['post_js'] = Array();

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

// Check bots
if (preg_match('/(bot|slurp|wget|libwww|\Wjava|\Wphp)\W/i', $_SERVER['HTTP_USER_AGENT'])) {
	$globals['bot'] = true;
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

function get_hex_color($color, $prefix = '') {
	return $prefix . substr(preg_replace('/[^a-f\d]/i', '', $color), 0, 6);	
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

	$parts = explode('@', $email);
	$domain = $parts[1];
	$subparts = explode('+', $parts[0]); // Because we allow user+extension@gmail.com
	$user = $subparts[0];
	$user = $db->escape($user);
	$domain = $db->escape($domain);
	$res=$db->get_var("SELECT count(*) FROM users WHERE user_email = '$user@$domain' or user_email LIKE '$user+%@$domain'");
	if ($res>0) return $res;
	return false;
}

function check_email($email) {
	global $globals;
	require_once(mnminclude.'ban.php');
	if (! preg_match('/^[a-z0-9_\-\.]+(\+[a-z0-9_\-\.]+)*@[a-z0-9_\-\.]+\.[a-z]{2,4}$/i', $email)) return false;

	$username = preg_replace('/@.+$/', '', $email);
	if ( substr_count($username, '.') > 2 || preg_match('/\.{2,}/', $username) ) return false; // Doesn't allow "..+" or more than 2 dots

	if(check_ban(preg_replace('/^.*@/', '', $email), 'email')) return false;
	return true;
}

function url_clean($url) {
	$array = explode('#', $url, 1);
	return $array[0];
}

function check_username($name) {
	return (preg_match('/^[a-zçÇñÑ][a-z0-9_\-\.çÇñÑ·]+$/i', $name) && mb_strlen($name) <= 24 &&
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
	// Replace two "-" by a single longer one, to avoid problems with xhtml comments
	//$string = preg_replace('/--/', '–', $string);
	if ($wrap>0) $string = wordwrap($string, $wrap, " ", 1);
	if ($replace_nl) $string = preg_replace('/[\n\t\r]+/s', ' ', $string);
	if ($maxlength > 0) $string = mb_substr($string, 0, $maxlength);
	return @htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
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

function text_to_summary($string, $length=50) {
	return text_to_html(preg_replace('/&\w*$/', '', mb_substr(preg_replace("/^(.{1,$length}[^\&;])([\s].*$|$)/", '$1', preg_replace("/[\r\n\t]+/", ' ', $string)), 0, $length)), false).' ...';
}

function text_to_html($string, $do_links = true) {
	// Dirty trick to allow tagging consecutives words 
	//$string = preg_replace('/([_*[0-9]) ([#_*])/', "$1  $2", $string);

	if ($do_links) {
		$string = preg_replace('/([;\(\[:\.\s]|^)(https*:\/\/)([^ \t\n\r\]\&]{5,70})([^ \t\n\r\]]*)([^ :.\t,\n\r\(\)\"\'\]\?])/', '$1<a href="$2$3$4$5" title="$2$3$4$5" rel="nofollow">$3$5</a>', $string);
	}
	$string = preg_replace('/\b_([^\s<>_]+)_\b/', "<em>$1</em>", $string);
	$string = preg_replace('/(^|[\(¡;,:¿\s])\*([^\s<>]+)\*/', "$1<strong>$2</strong>", $string);
	return $string;
}

// Clean all special chars and html/utf entities
function text_sanitize($string) {
	$string = preg_replace('/&[^ ;]{1,8};/', ' ', $string);
	$string = preg_replace('/(^|[\(¡;,:\s])[_\*]([^\s<>]+)[_\*]/', ' $2 ', $string);
	return $string;
}

function check_integer($which) {
	if (is_numeric($_REQUEST[$which])) {
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
	if(($var=check_integer('page'))) {
		return $var;
	} else {
		return 1;
	}
    // return $_GET['page']>0 ? $_GET['page'] : 1;
}

function get_date($epoch) {
    return date("Y-m-d e", $epoch);
}

function get_date_time($epoch) {
	    //return date("Y-m-d H:i", $epoch);
	    return date(" d-m-Y H:i e", $epoch);
}

function get_server_name() {
	global $server_name;
	if($_SERVER['SERVER_NAME']) return $_SERVER['SERVER_NAME'];
	else {
		if ($server_name) return $server_name;
		else return 'meneame.net'; // Warn: did you put the right server name?
	}
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

function post_get_base_url($option='') {
	global $globals;
	if (empty($globals['base_sneakme_url'])) {
		if (empty($option)) {
			return $globals['base_url'].'sneakme/';
		} else {
			return $globals['base_url'].'sneakme/?id='.$option;
		}
	} else {
		return $globals['base_url'].$globals['base_sneakme_url'].$option;
	}
}

function get_avatar_url($user, $avatar, $size) {
	global $globals, $db; 

	// If it does not get avatar status, check the database
	if ($user > 0 && $avatar < 0) {
		$avatar = (int) $db->get_var("select user_avatar from users where user_id = $user");
	}

	if ($avatar > 0 && $globals['cache_dir']) {
		$file = $globals['cache_dir'] . '/avatars/'. intval($user/$globals['avatars_files_per_dir']) . '/' . $user . "-$size.jpg";
		// Don't check every time, but 1/10, decrease VM pressure 
		// Disabled for the moment, it fails just too much for size 40
		//if (rand(0, 10) < 10) return $globals['base_url'] . $file;
		$file_path = mnmpath.'/'.$file;
		if (@filemtime($file_path) >= $avatar) {
			return $globals['base_url'] . $file;
		} else {
			return $globals['base_url'] . "backend/get_avatar.php?id=$user&amp;size=$size&amp;time=$avatar";
		}
	} 
	return get_no_avatar_url($size);
}

function get_no_avatar_url($size) {
	global $globals;
	return $globals['base_url'].'img/mnm/no-gravatar-2-'.$size.'.jpg';
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

function not_found($mess = '') {
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
	if (empty($mess)) {
		echo '<h1>' . _('error') . ' 3.1415926536</h1><p>' . _('no encontrado') . '</p>';
	} else {
		echo $mess;
	}
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

function do_modified_headers($time, $tag) {
	header('Last-Modified: ' . date('r', $time));
	header('ETag: "'.$tag.'"');
	header('Cache-Control: max-age=5');
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
		return (int) $str;
	} else {
		$str = $db->escape($str);
		$id = (int) $db->get_var("select user_id from users where user_login = '$str'");
		return $id;
	}
}

function print_simpleformat_buttons($textarea_id) {
	global $globals, $current_user;

	// To avoid too many bolds and italics from new users and trolls
	if ($current_user->user_karma < 6.001) return;

	echo '<img onclick="applyTag(\''.$textarea_id.'\', \'*\');" src="'.$globals['base_url'].'img/common/richeditor-bold-01.png" alt="bold" class="rich-edit-key" />';
	echo '<img onclick="applyTag(\''.$textarea_id.'\', \'_\');" src="'.$globals['base_url'].'img/common/richeditor-italic-01.png" alt="italic" class="rich-edit-key" />';
}

function put_smileys($str) {
	global $globals;

	if ($globals['bot']) return $str;

	$str=preg_replace('/(\s|^):-{0,1}\)(\s|$)/i', ' <img src="'.$globals['base_url'].'img/smileys/smiley.gif" alt=":-)" title=":-)" width="15" height="15" />$1', $str);
	$str=preg_replace('/(\s|^);-{0,1}\)(\s|$)/i', ' <img src="'.$globals['base_url'].'img/smileys/wink.gif" alt=";)" title=";)"  width="15" height="15" />$1', $str);
	$str=preg_replace('/(\s|^):-{0,1}&gt;/i', ' <img src="'.$globals['base_url'].'img/smileys/cheesy.gif" alt=":-&gt;" title=":-&gt;"  width="15" height="15" />', $str);
	$str=preg_replace('/(\s|^):-{0,1}D|:grin:/i', ' <img src="'.$globals['base_url'].'img/smileys/grin.gif" alt=":-D" title=":-D" width="15" height="15" />', $str);
	$str=preg_replace('/(\s|^):oops:|&lt;:\(/i', ' <img src="'.$globals['base_url'].'img/smileys/embarassed.gif" alt="&lt;&#58;(" title="&#58;oops&#58; &lt;&#58;("  width="15" height="15" />', $str);
	$str=preg_replace('/&gt;:-{0,1}\((\s|$)/i', ' <img src="'.$globals['base_url'].'img/smileys/angry.gif" alt="&gt;&#58;-(" title="&gt;&#58;-("  width="15" height="15" />$1', $str);
	$str=preg_replace('/(\s|^)\?(:-){0,1}\((\s|$)/i', ' <img src="'.$globals['base_url'].'img/smileys/huh.gif" alt="?(" title="?("  width="15" height="22" />$1', $str);
	$str=preg_replace('/(\s|^):-{0,1}\((\s|$)/i', ' <img src="'.$globals['base_url'].'img/smileys/sad.gif" alt=":-(" title=":-("  width="15" height="15" />$1', $str);
	$str=preg_replace('/(\s|^):-{0,1}O/', ' <img src="'.$globals['base_url'].'img/smileys/shocked.gif" alt=":-O" title=":-O"  width="15" height="15" />', $str);
	$str=preg_replace('/(\s|^)8-{0,1}[D\)]|(\s|^):cool:/', ' <img src="'.$globals['base_url'].'img/smileys/cool.gif" alt="8-D" title=":cool: 8-D" width="15" height="15" />', $str);
	$str=preg_replace('/(\s|^):roll:/i', ' <img src="'.$globals['base_url'].'img/smileys/rolleyes.gif" alt=":roll:" title=":roll:"  width="15" height="15" />', $str);
	$str=preg_replace('/(\s|^):-{0,1}P/i', ' <img src="'.$globals['base_url'].'img/smileys/tongue.gif" alt=":-P" title=":-P"  width="15" height="15" />', $str);
	$str=preg_replace('/(\s|^):-{0,1}x/i', ' <img src="'.$globals['base_url'].'img/smileys/lipsrsealed.gif" alt=":-x" title=":-x"  width="15" height="15" />', $str);
	$str=preg_replace('/(\s|^):-{0,1}\//i', '$1 <img src="'.$globals['base_url'].'img/smileys/undecided.gif" alt=":-/" title=":-/ :/"  width="15" height="15" />', $str);
	$str=preg_replace('/(\s|^):\'\(|(\s|^):cry:/i', ' <img src="'.$globals['base_url'].'img/smileys/cry.gif" alt=":\'(" title=":cry: :\'("  width="15" height="15" />', $str);
	$str=preg_replace('/(\s|^)x-{0,1}D+|(\s|^):lol:/i', ' <img src="'.$globals['base_url'].'img/smileys/laugh.gif" alt="xD" title=":lol: xD"  width="15" height="15" />', $str);
	$str=preg_replace('/(\s|^):-{0,1}S/i', ' <img src="'.$globals['base_url'].'img/smileys/confused.gif" alt=":-S" title=":-S :S" width="15" height="15" />', $str);
	$str=preg_replace('/(\s|^):-{0,1}\|/i', ' <img src="'.$globals['base_url'].'img/smileys/blank.gif" alt=":-|" title=":-| :|" width="15" height="15" />', $str);
	$str=preg_replace('/(\s|^):-{0,1}\*/i', ' <img src="'.$globals['base_url'].'img/smileys/kiss.gif" alt=":-*" title=":-* :*" width="15" height="15" />', $str);
	return $str;
}


// Meta categories helpers
define('META_YES', '<img class="tabsub-shakeit-icon" src="'.$globals['base_url'].'img/common/fix-01.png" alt="del" width="12" height="12" title="'._('filtrar como tema por defecto').'"/>');
define('META_NO', '<img class="tabsub-shakeit-icon" src="'.$globals['base_url'].'img/common/fix-02.png" alt="del" width="12" height="12" title="'._('filtrar como tema por defecto').'"/>');

function meta_get_current() {
	global $globals, $db, $current_user;

	$globals['meta_current'] = 0;
	$globals['meta']  = clean_input_string($_REQUEST['meta']);

	//Check for personalisation
	// Authenticated users
	if ($current_user->user_id > 0) {
		$categories = $db->get_col("SELECT SQL_CACHE pref_value FROM prefs WHERE pref_user_id = $current_user->user_id and pref_key = 'category' order by pref_value");
		if ($categories) { 
			$current_user->has_personal = true;
			$globals['meta_skip'] = '?meta=_all';
			if (! $globals['meta']) {
				$globals['meta_categories'] = implode(',', $categories); 
				$globals['meta']= '_personal';
			}
		} else {
			$globals['meta_categories'] = false;
		}
	} elseif ($_COOKIE['mnm_user_meta']) {
		// anonymous users
		$meta = $db->escape(clean_input_string($_COOKIE['mnm_user_meta']));
		$globals['meta_skip'] = '?meta=_all';
		$globals['meta_user_default'] = $db->get_var("select category_id from categories where category_uri = '$meta' and category_parent = 0");
		// Anonymous can select metas by cookie
		// Select user default only if no category has been selected
		if(!$_REQUEST['category'] && !$globals['meta']) {
			$globals['meta_current'] = $globals['meta_user_default'];
		}
	}

	if ($_REQUEST['category']) {
		$_REQUEST['category'] = $cat = (int) $_REQUEST['category'];
		if ($globals['meta'][0] == '_') {
			$globals['meta_current'] = $globals['meta'];
		} else {
			$globals['meta_current'] = (int) $db->get_var("select SQL_CACHE category_parent from categories where category_id = $cat and category_parent > 0");
			$globals['meta'] = '';
		}
	} elseif ($globals['meta']) {
		// Special metas begin with _
		if ($globals['meta'][0] == '_') {
			return 0;
		}
		$meta = $db->escape($globals['meta']);
		$globals['meta_current'] = $db->get_var("select SQL_CACHE category_id from categories where category_uri = '$meta' and category_parent = 0");
		if ($globals['meta_current']) {
			$globals['meta'] = '';  // Security measure
		}
	} 
	
	if ($globals['meta_current'] > 0) {
		$globals['meta_categories'] = meta_get_categories_list($globals['meta_current']);
		if (!$globals['meta_categories']) {
			$globals['meta_current'] = 0;
		}
	}
	return $globals['meta_current'];
}

function meta_get_categories_list($id) {
	global $db;
	$categories = $db->get_col("SELECT SQL_CACHE category_id FROM categories WHERE category_parent = $id order by category_id");
	if (!$categories) return false;
	return implode(',', $categories);
}

function meta_teaser($current, $default) {
	global $globals;
	if ($current == $default)
		return META_YES;
	else 
		return META_NO;
}

function meta_teaser_item() {
	global $globals, $current_user;
	if ($globals['meta'][0] != '_' || $globals['meta'] == '_all') { // Ignore special metas
		echo '<li><a class="teaser" id="meta-'.$globals['meta_current'].'" href="javascript:get_votes(\'set_meta.php\',\''.$current_user->user_id.'\',\'meta-'.$globals['meta_current'].'\',0,\''.$globals['meta_current'].'\')">'.meta_teaser($globals['meta_current'], $globals['meta_user_default']).'</a></li>';
	}
}

function fork($uri) {
	global $globals;

	$sock = @fsockopen(get_server_name(), $_SERVER['SERVER_PORT'], $errno, $errstr, 0.01 );

	if ($sock) {
		@fputs($sock, "GET {$globals['base_url']}$uri HTTP/1.0\r\n" . "Host: {$_SERVER['HTTP_HOST']}\r\n\r\n");
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
		$item .= $key . ':"' . $val . '"';
		$passed = 1;
	}
	return $item . '}';
}

//
// Memcache functions
//

$memcache = false;

function memcache_minit () {
	global $memcache, $globals;

	if ($memcache) return true;
	if ($globals['memcache_host']) {
		$memcache = new Memcache;
		if (!isset($globals['memcache_port'])) $globals['memcache_port'] = 11211;
		if ( ! @$memcache->connect($globals['memcache_host'], $globals['memcache_port']) ) {
			$memcache = false;
			syslog(LOG_INFO, "Meneame: memcache init failed");
			return false;
		}
		return true;
	}
	return false;
}

function memcache_mget ($key) {
	global $memcache;

	if (memcache_minit()) return $memcache->get($key);
	return false;
}


function memcache_madd ($key, $str, $expire=0) {
	global $memcache;
	if (memcache_minit()) return $memcache->add($key, $str, false, $expire);
	return false;
}

function memcache_mprint ($key) {
	global $memcache;
	if (memcache_minit() && ($value = $memcache->get($key))) {
		echo $value;
		return true;
	}
	return false;
}

function memcache_mdelete ($key) {
	global $memcache;
	if (memcache_minit()) return $memcache->delete($key);
	return false;
}

// Generic function to get content from an url
function get_url($url, $referer = false, $max=200000) {
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
	curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($session, CURLOPT_HEADER , true );
	curl_setopt($session, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($session, CURLOPT_MAXREDIRS, 20);
	curl_setopt($session, CURLOPT_TIMEOUT, 20);
	curl_setopt($session, CURLOPT_FAILONERROR, true);
	curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 2); 
	//curl_setopt($session,CURLOPT_RANGE,"0-$max"); // It gives error with some servers
	$response = @curl_exec($session);
	if (!$response) {
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
?>
