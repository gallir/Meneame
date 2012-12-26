<?
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// David Martín :: Suki_ :: <david at sukiweb dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function check_ip_noaccess() {
	global $globals;
	$ban = check_ban($globals['user_ip'], 'noaccess');
	if ($ban) {
		header('HTTP/1.0 403 ' . $ban['comment']);
		die;
	}
	return false;
}

function check_ban_proxy() {
	global $globals;
	if (($ban = check_ban($globals['user_ip'], 'proxy'))) return $ban;
	else return check_ban($_SERVER['REMOTE_ADDR'], 'proxy');
}

function check_ban($ban_text, $ban_type, $check_valid = true, $first_level = false) {
	global $db, $globals;	
	
	$ban_text = $db->escape($ban_text);
	$ban_type = $db->escape($ban_type);
	
	// If check_valid == false does not check for validity of the address
	// in order to avoid problems with bad links in external pages
	switch ($ban_type) {
		case 'email':
		case 'hostname':
		case 'punished_hostname':
			// Clean protocol and path/arguments
			$ban_text = preg_replace('/^(https*|ftp):\/\//', '', $ban_text);
			// Delete double "/" that can be used to cheat the control
			$ban_text = preg_replace('/\/+/', '/', $ban_text);
			// It leaves up to second level path
			$ban_text = preg_replace('/(\/[^\/\?]+)(\/[^\/\?]+){0,1}[\/\?]+.*$/', '$1$2', $ban_text);
			$ban_text = preg_replace('/\.*$/', '', $ban_text);
			if ($check_valid  && ! preg_match('/^([\w_\-\.]+\.[\w]{2,4}(\/[a-z\.]+\/*){0,1}|[\w]{2,5})$/', $ban_text)) {
				$ban = array();
				$ban['match'] =  $ban_text;
				$ban['comment'] = _('No es un dominio correcto');
				return $ban;
			}
			$where= " ban_text IN (".subdomains_list($ban_text, $first_level).") AND ban_type='$ban_type' AND (ban_expire IS null OR ban_expire > now()) ";
			break;
		case 'ip':
		case 'proxy':
			if ($check_valid  && ! preg_match('/^([\da-f]+[\.\:])+/is', $ban_text)) { // TODO: check regexp
				$ban = array();
				$ban['match'] =  $ban_text;
				$ban['comment'] =_('No es una IP válida');
				syslog(LOG_INFO, "IP inválida: $ban_text");
				return $ban;
			}
			$list = subclasses_list($ban_text);
			$where="ban_text IN ($list) AND ban_type='$ban_type' AND (ban_expire IS null OR ban_expire > now())"; 
			break;
		case 'noaccess':
			$where="ban_text = '$ban_text' AND ban_type='$ban_type' AND (ban_expire IS null OR ban_expire > now())"; 
			break;
		default:
			return false;
	}

	$match=$db->get_row("SELECT ban_text, ban_comment, UNIX_TIMESTAMP(ban_date) as date, UNIX_TIMESTAMP(ban_expire) as expire FROM bans WHERE $where LIMIT 1");
	if ($match) {
		$ban = array();
		$ban['date'] = $match->date;
		$ban['expire'] = $match->expire;
		$ban['text'] = htmlentities($ban_text);
		// For security
		$ban['match']  = htmlentities(trim($match->ban_text));
		$ban['comment'] = $match->ban_comment;
		return $ban;
	}
	return false;
}

function subclasses_list($ip) {
	$list = "'$ip'";
	$array = explode('.', $ip);
	$size = count($array) - 1;
	while ($size > 1)  {
		$new_class = $array[0];
		for ($i=1; $i < $size; $i++) {
			$new_class .= '.' . $array[$i];
		}
		$list .= ",'$new_class'";
		$size--;
	}
	return $list;
}

function subdomains_list($domain_path, $first_level = false) {
	$paths = array();
	// search for the first part of the path
	if(preg_match('/^[^\/]+\/+([^\/\?]+)[\/\?]*/', $domain_path, $match) > 0) {
		$paths[0] = $match[1];
		// search for the second part of the path
		if($paths[0] && preg_match('/^[^\/]+\/+[^\/\?]+\/+([^\/\?]+)[\/\?]*/', $domain_path, $match) > 0) {
			$paths[1] = $paths[0].'/'.$match[1];
		}
	}

	$domain = preg_replace('/\/.*$/', '', $domain_path);
	$list = "'$domain'";
	foreach ($paths as $path) {
		$list .= ", '$domain/$path', '$domain/$path/'";
	}
	$array = explode('.', $domain);
	$size = count($array);
	if ($first_level) $domain_limit = $size;
	else $domain_limit = $size - 1;

	for($i=1; $i < $domain_limit; $i++) {
		$sub = array_slice($array, $i);
		$sub = implode('.', $sub);
		$list .= ", '$sub'";
		if ($i < $size-1 ) { // Add path only if there is at least a second level, avoid tk/path
			foreach ($paths as $path) {
				$list .= ", '$sub/$path', '$sub/$path/'";
			}
		}
	}
	return $list;
}

function insert_ban($ban_type, $ban_text, $ban_comment="", $ban_expire='UNDEFINED', $ban_id=0) {
	global $globals;

	if (strlen($ban_text) < 2) {
		echo '<div class="form-error">';
		echo '<p>'._('Texto del ban muy corto').'</p>';
		echo "</div>\n";
		return;
	}
	/*
	if (strlen($ban_text) > 8 && preg_match('/^www\..+\.[a-z]+(\/[a-z]+\/*){0,1}$/i', $ban_text) ) {
		$ban_text = preg_replace('/^www\./', '', $ban_text);
	}
	*/

	$ban=new Ban();
	if ($ban_id > 0) {
		$ban->ban_id = (int) $ban_id;
		$ban->read();
	}
	$ban->ban_type=$ban_type;
	$ban->ban_text=$ban_text;
	if (!empty($ban_comment)) {
		$ban->ban_comment=$ban_comment;
	}
	if (!empty($ban_expire)) {
		$ban->ban_expire=$ban_expire;
	}
	$ban->store();
	return $ban;
}

function del_ban($ban_id) {
	$ban=new Ban();
	$ban->ban_id=$ban_id;
	$ban->remove();
}

class Ban {
	var $ban_id = 0;
	
	function Ban($ban_id=0) {
		if ($ban_id>0) {
			$this->ban_id = intval($ban_id);
			$this->read();
		}
	}
	
	function read() {
		global $db;
		$ban_id = intval($this->ban_id);
		$ban_type= $db->escape($this->ban_type);
		$ban_text= $db->escape($this->ban_text);
		if($ban_id>0) $where = "ban_id = $ban_id";
		elseif(!empty($ban_text) && !empty($ban_type)) $where = "ban_type='$ban_type' AND ban_text='$ban_text'";

		if(!empty($where) && ($ban = $db->get_row("SELECT * FROM bans WHERE $where"))) {
			$this->ban_id =$ban->ban_id;
			$this->ban_text=$ban->ban_text;
			$this->ban_type=$ban->ban_type;
			if (empty($ban->ban_expire)) {
				$this->ban_expire = 'NULL';
			} else {
				$this->ban_expire=$ban->ban_expire;
			}
			$this->ban_date=$ban->ban_date;
			$this->ban_comment=$ban->ban_comment;
			$this->read = true;
			return true;
		}
		$this->id = 0;
		$this->read = false;
		return false;
	}
	
	function store() {
		global $db;
		$this->ban_id=intval($this->ban_id);
		$this->ban_type=$db->escape($this->ban_type);
		$this->ban_text=$db->escape(clean_text($this->ban_text));
		$this->ban_comment=$db->escape(clean_text($this->ban_comment));

		if (empty($this->ban_expire) || $this->ban_expire=='UNDEFINED' || $this->ban_expire == 'NULL' || preg_match('/[^0-9 :-]/', $this->ban_expire)) {
			$expire_value='NULL';
		} elseif ($this->ban_expire != 'NOCHANGE' && preg_match('/^[0-9]+$/', $this->ban_expire)) {
			$expire_value='FROM_UNIXTIME('.intval($this->ban_expire).')';
		} else {
			$expire_value = "'".$db->escape($this->ban_expire)."'";
		}

		if ($this->ban_id > 0) {
			$sql="UPDATE bans SET ban_type='$this->ban_type', ban_text='$this->ban_text', ban_comment = '$this->ban_comment', ban_expire = $expire_value WHERE ban_id =$this->ban_id";
			//$sql = 'UPDATE bans SET (ban_id, ban_type, ban_text, ban_comment, ban_expire) ';
			//$sql .= "VALUES ($this->ban_id, '$this->ban_type', '$this->ban_text',  '$this->ban_comment', $expire_value) ";
		} else {
			$sql = 'REPLACE INTO bans (ban_type, ban_text, ban_comment, ban_expire) ';
			$sql .= "VALUES ('$this->ban_type', '$this->ban_text',  '$this->ban_comment', $expire_value) ";
		}
		//echo "<br>Executing: $sql<br>\n";
		$db->query($sql);
	}

	function remove() {
		global $db;
		$ban_id=intval($this->ban_id);
		if($ban_id !=0) {
			$db->query("DELETE FROM bans WHERE ban_id=$ban_id");
		}
	}
}
