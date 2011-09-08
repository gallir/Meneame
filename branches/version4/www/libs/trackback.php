<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


class Trackback {
	var $id = 0;
	var $author = 0;
	var $link_id = 0;
	var $type = 'out';
	var $status = 'pendent';
	var $date = false;
	var $ip = 0;
	var $url  = '';
	var $title = '';
	var $link = '';
	var $content = '';
	var $read = false;

	function store() {
		global $db, $current_user, $globals;

		if(!$this->date) $this->date=time();
		if($this->ip == 0) $this->ip = $globals['user_ip_int'];
		$trackback_date=$this->date;
		$trackback_ip_int=$this->ip;
		$trackback_author = $this->author;
		$trackback_link_id = $this->link_id;
		$trackback_type = $this->type;
		$trackback_status = $this->status;
		$trackback_url = $db->escape(trim($this->url));
		$trackback_link = $db->escape(trim($this->link));
		$trackback_title = $db->escape(trim($this->title));
		$trackback_content = $db->escape(trim($this->content));
		$db->query("REPLACE INTO trackbacks (trackback_user_id, trackback_link_id, trackback_type, trackback_date, trackback_ip_int, trackback_status, trackback_link, trackback_url, trackback_title, trackback_content) VALUES ($trackback_author, $trackback_link_id, '$trackback_type', FROM_UNIXTIME($trackback_date), $trackback_ip_int, '$trackback_status', '$trackback_link', '$trackback_url', '$trackback_title', '$trackback_content')");
		if (!$this->id && $db->insert_id > 0) $this->id = $db->insert_id;
	}
	
	function read() {
		global $db, $current_user;

		if($this->id == 0 && !empty($this->link)  && $this->link_id > 0) 
			$cond = "trackback_type = '$this->type' AND trackback_link_id = $this->link_id AND trackback_link = '$this->link'";

		else $cond = "trackback_id = $this->id";
	
		if(($link = $db->get_row("SELECT * FROM trackbacks WHERE $cond"))) {
			$this->id=$link->trackback_id;
			$this->author=$link->trackback_user_id;
			$this->link_id=$link->trackback_link_id;
			$this->type=$link->trackback_type;
			$this->status=$link->trackback_status;
			$this->link=$link->trackback_link;
			$this->url=$link->trackback_url;
			$this->title=$link->trackback_title;
			$this->content=$link->trackback_content;
			$this->ip=$link->trackback_ip_int;
			$date=$link->trackback_date;
			$this->date=$db->get_var("SELECT UNIX_TIMESTAMP('$date')");
			$this->read = true;
			return true;
		}
		$this->read = false;
		return false;
	}

// Send a Trackback
	function send($link) {

		if (empty($this->url)) return;

		$this->title = clean_input_url($link->url);

		if (preg_match('/^ping:/', $this->url)) { // we got a pingback adress
			require_once(mnminclude.'IXR_Library.inc.php');
			$url = preg_replace('/^ping:/', '', $this->url);
			$client = new IXR_Client($url);
			$client->timeout = 3;
			$client->useragent .= ' -- Meneame/2';
			$client->debug = false;
			if ($client->query('pingback.ping', $link->get_permalink(), $this->link )) {
				$this->status='ok';
				$this->store();
				syslog(LOG_NOTICE, "Meneame, pingback sent: $this->link, $this->url");
				return true;
			} else {
				// Be quiet for pingbacks
				$this->status='error';
				$this->title = $client->getErrorMessage();
				$this->store();
				// syslog(LOG_NOTICE, "Meneame, out pingback error: $url ".$link->get_permalink().': '.$client->getErrorCode().' '.$client->getErrorMessage());
				return false;
			}
		}

		// Send standard old trackback
		$title = urlencode($link->title);
		// Convert everything to HTML and the strip all html tags.
		$excerpt = urlencode(text_to_summary($link->content, 250));

		$blog_name = urlencode(get_server_name());
		$tb_url = $this->url;
		$url = urlencode($link->get_permalink());
		$query_string = "charset=UTF-8&title=$title&url=$url&blog_name=$blog_name&excerpt=$excerpt";
		$trackback_url = parse_url($this->url);
		$http_request  = 'POST ' . $trackback_url['path'] . ($trackback_url['query'] ? '?'.$trackback_url['query'] : '') . " HTTP/1.0\r\n";
		$http_request .= 'Host: '.$trackback_url['host']."\r\n";
		$http_request .= 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8'."\r\n";
		$http_request .= 'Content-Length: '.strlen($query_string)."\r\n";
		$http_request .= "User-Agent: MNM (http://meneame.net) ";
		$http_request .= "\r\n\r\n";
		$http_request .= $query_string;
		if (empty($trackback_url['port'])) $trackback_url['port'] = 80;
		$fs = @fsockopen($trackback_url['host'], $trackback_url['port'], $errno, $errstr, 5);
		if($fs && ($res=@fputs($fs, $http_request)) ) {
		/*********** DEBUG *********
			$debug_file = '/tmp/trackback.log';
			$fp = fopen($debug_file, 'a');
			fwrite($fp, "\n*****\nRequest:\n\n$http_request\n\nResponse:\n\n");
			while(!@feof($fs)) {
				fwrite($fp, @fgets($fs, 4096));
			}
			fwrite($fp, "\n\n");
			fclose($fp);
		/*********** DEBUG ************/
			@fclose($fs);
			$this->status='ok';
			$this->store();
			syslog(LOG_NOTICE, "Meneame, trackback sent: $this->link, $this->url");
			return true;	
		}
		$this->status='error';	
		$this->store();
		return false;
	}

	function abuse() {
		global $globals, $db;

		$trackback_url = parse_url($this->url);
		$host = $trackback_url['host'];

		if ($host == get_server_name()) return false;

		if ($host && $this->link_id && $this->type == 'in') {
			$tbs = (int) $db->get_var("select count(*) from trackbacks where trackback_type='in' and trackback_link_id = $this->link_id and trackback_url like '%://$host/%'");
			if ($tbs > 0) {
				syslog(LOG_NOTICE, "Meneame: too many trackbacks/pingbacks from $host ($this->url)");
				$this->status = 'error';
				$this->store();
				return true;
			}
		}

		if ($globals['user_ip'] !=	$_SERVER["SERVER_ADDR"]) {
			$tbs = (int) $db->get_var("select count(*) from trackbacks where trackback_date > date_sub(now(), interval 120 minute) and trackback_type='in' and trackback_ip_int = $globals[user_ip_int]");
			if ($tbs > 2) {
				syslog(LOG_NOTICE, "Meneame: trackback/pingback abuse from $globals[user_ip], $this->link, $this->url");
				if (!empty($this->link) && $this->type == 'in') {
					$this->status = 'error';
					$this->store();
				}
				return true;
			}
		} 
	}
}
