<?
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// David Martín :: Suki_ :: <david at sukiweb dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function check_ban_proxy() {
	return check_ban($_SERVER['REMOTE_ADDR'], 'proxy');
}

function check_ban($ban_text, $ban_type, $check_valid = true) {
	global $db, $globals;	
	
	$ban_text = $db->escape($ban_text);
	$ban_type = $db->escape($ban_type);
	
	// If check_valid == false does not check for validity of the address
	// in order to avoid problems with bad links in external pages
	switch ($ban_type) {
		case 'email':
		case 'hostname':
			if ($check_valid  && ! preg_match('/^[\w_\-\.]+\.[\w]{2,4}(\/[a-z]+\/*){0,1}$/', $ban_text)) {
				$globals['ban_message'] =_('No es un dominio correcto');
				return true;
			}
			$where= " ban_text IN (".subdomains_list($ban_text).") AND ban_type='$ban_type' AND (ban_expire IS null OR ban_expire > now()); ";
			break;
		case 'ip':
		case 'proxy':
			//Quizá convendría revisar este preg_mach para revisar las IPs válidas mejor.
			if ($check_valid  && ! preg_match('/^[1-9]\d{0,2}\.(\d{1,3}\.){2}\d{1,3}$/s', $ban_text)) {
				$globals['ban_message'] =_('No es una IP válida');
				return true;
			}
			$where="ban_text='$ban_text' AND ban_type='$ban_type' AND (ban_expire IS null OR ban_expire > now()); "; 
			break;
		default:
			return false;
	}

	$res=$db->get_col("SELECT ban_comment FROM bans WHERE $where");
	if ($res) {
		$globals['ban_message'] = '';
		foreach ($res as $comment) {
			$globals['ban_message'] .= "$comment ";
		}
		return true;
	}
	return false;
}

function subdomains_list($domain) {
	$list = "'$domain'";
	$array = explode('.', $domain);
	$size = count($array);

	for($i=1; $i < $size-1; $i++) {
		$sub = array_slice($array, $i);
		$list .= ", '". implode('.', $sub). "'";
	}
	return $list;
}

function insert_ban($ban_type, $ban_text, $ban_comment="", $ban_expire='UNDEFINED', $ban_id=0) {
	global $globals;

	if (strlen($ban_text) < 4) {
		recover_error(_('Texto del ban muy corto'));
		return;
	}
	if (strlen($ban_text) > 8 && preg_match('/^www\..+\.[a-z]+(\/[a-z]+\/*){0,1}$/i', $ban_text) ) {
		$ban_text = preg_replace('/^www\./', '', $ban_text);
	}

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
	if ($ban_expire != 'UNDEFINED' && !empty($ban_expire)) {
		$ban->ban_expire=$ban_expire;
	}
	$ban->store();
	return $ban;
}

function del_ban($ban_id) {
	$ban=new Ban();
	$ban->ban_id=$_REQUEST["del_ban"];
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
			$sql="UPDATE bans SET ban_type='$this->ban_type', ban_text='$this->ban_text', ban_comment = '$this->ban_comment', ban_expire = $expire_value WHERE ban_id =$this->ban_id LIMIT 1";
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
