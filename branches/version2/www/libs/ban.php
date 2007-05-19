<?
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// David Martín :: Suki_ :: <david at sukiweb dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function check_ban($ban_text, $ban_type) {
	global $db;	
	
	$ban_text = $db->escape($ban_text);
	$ban_type = $db->escape($ban_type);
	
	switch ($ban_type) {
		case 'hostname':
			if (! preg_match('/^[a-zA-Z0-9_\-\.]+\.[a-zA-Z]{2,4}$/', $ban_text)) {
				$error="No es un dominio correcto";
				return $error;
			}
			$where= " ban_text IN (".subdomains_list($ban_text).") AND ban_type='".$ban_type."' AND (ban_expire IS null OR ban_expire > now()); ";
			break;
		case 'ip':
			//Quizá convendría revisar este preg_mach para revisar las IPs válidas mejor.
			if (! preg_match('/^[1-9]\d{0,2}\.(\d{1,3}\.){2}[1-9]\d{0,2}$/s', $ban_text)) {
				$error="No es una IP válida";
				return $error;
			}
			break;
	}

	if (!$where) { $where="ban_text='".$ban_text."' AND ban_type='".$ban_type."' AND (ban_expire IS null OR ban_expire > now()); "; }
	$res=$db->get_var("SELECT count(*) FROM bans WHERE ".$where);
	if ($res>0) return "El ".$ban_type." ya existe";
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

function insert_ban($ban_type, $ban_text, $ban_comment="", $ban_expire="UNDEFINED", $ban_id=0) {
	$ban=new Ban();
	$ban->ban_type=$ban_type;
	$ban->ban_text=$ban_text;
	$ban->ban_comment=$ban_comment;
	$ban->ban_expire=$ban_expire;
	if ($ban_id!=0) { $ban->ban_id=$ban_id; }		
	if($ban_id==0 AND $ban->read()) {
		recover_error(_('El ban ya existe'));
	} else {
		if($error=check_ban($ban_text, $ban_type)) {
			recover_error(_($error));
			return;
		}
		$ban->store();
	}
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
		$ban_text= $db->escape($this->ban_text);
		if($ban_id>0) $where = "ban_id = $ban_id";
		else if(!empty($ban_text)) $where = "ban_text='".$ban_text."'";

		if(!empty($where) && ($ban = $db->get_row("SELECT * FROM bans WHERE $where"))) {
			$this->ban_id =$ban->ban_id;
			$this->ban_text=$ban->ban_text;
			$this->ban_type=$ban->ban_type;
			$this->ban_expire=$ban->ban_expire;
			$this->ban_date=$ban->ban_date;
			$this->ban_comment=$ban->ban_comment;
			$this->read = true;
			return true;
		}
		$this->read = false;
		return false;
	}
	
	function store() {
		global $db;
		$ban_id=intval($this->ban_id);
		$ban_type=$db->escape($this->ban_type);
		$ban_text=$db->escape($this->ban_text);
		$ban_comment=$db->escape(clean_text($this->ban_comment));

		if($this->ban_id==0) {
			if ($this->ban_expire!="UNDEFINED") { 
				//$expire=", NOW() + INTERVAL ".$this->ban_expire." DAY ";
				 $expire=", FROM_UNIXTIME(".$this->ban_expire.") ";
			}
			$SELECT="INSERT INTO `bans` ( `ban_type` , `ban_text` ,  `ban_comment` ";
			if ($expire) { $SELECT .=", `ban_expire` ";}
			$SELECT .=") VALUES ('".$ban_type."', '".$ban_text."',  '".$ban_comment."' ";
			if ($expire) { $SELECT .=$expire; }
			$SELECT .=")";
			$db->query($SELECT);
		
		} else {
		// update
			if ($this->ban_expire=="UNDEFINED") { 
				$expire=", ban_expire=NULL "; 
			} else if ($this->ban_expire != "NOCHANGE") { 
				$expire=", FROM_UNIXTIME(".$this->ban_expire.") "; 
			}
			$SELECT="UPDATE `bans` SET `ban_text`='".$ban_text."', `ban_comment` = '".$ban_comment."'";
			if ($expire) { $SELECT .=$expire; }
			$SELECT .=" WHERE `bans`.`ban_id` ='".$ban_id."' LIMIT 1 ;";
			$db->query($SELECT);
		}
		
	}

	function remove() {
		global $db;
		$ban_id=intval($this->ban_id);
		if($ban_id !=0) {
			$db->query("DELETE FROM `bans` WHERE `ban_id`='".$ban_id."'");
		}
	}
}
