<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Blog {
	var $id = 0;
	var $key = false;
	var $type = 'normal';
	var $url = false;
	var $rss = false;
	var $rss2 = false;
	var $atom = false;
	var $read = false;

	function print_html() {
		echo "rss: " . $this->rss . "<br>\n";
		echo "rss2: " . $this->rss2 . "<br>\n";
		echo "atom: " . $this->atom . "<br>\n";
	}

	function calculate_key() {
		$normalised_url = preg_replace('/\/\/www\./', '//', $this->url);
		$this->key = md5($normalised_url);
	}

	function has_key() {
		return (strlen($this->key) == 32);
	}

	function analyze_html($url, $html) {
		$this->rss=$this->getShortestValFromExp('/<link[^>]+text\/xml[^>]+href=[^>]+>/i','/href="([^"]+)"/i',$html);
		$this->atom=$this->getShortestValFromExp('/<link[^>]+application\/atom\+xml[^>]+>/i','/href="([^"]+)"/i',$html);
		$this->rss2=$this->getShortestValFromExp('/<link[^>]+application\/rss\+xml[^>]+>/i','/href="([^"]+)"/i',$html);
       
		if($this->rss || $this->atom || $this->rss2) $this->type='blog';

		// Last try to find a rss
		if($this->type!='blog' && preg_match('/<a[^>]+href="(http[^>]+\.rdf)"/i', $html, $matches2)) {
			$rss=$this->rss=$matches2[1];
			$this->type='blog';
		}

		$this->find_base_url($url);
		$this->calculate_key();
		return $this->type;
		
	}

	function getShortestValFromExp($match,$secondMatch,$text){
		$value=false;

		if(preg_match_all($match, $text, $matches)) {
			$candidates=array();
			foreach ($matches[0] as $m){
				if(preg_match($secondMatch, $m, $matches2)) {
					array_push($candidates,$matches2[1]);
				}
			}
			$value=$this->shortest_text($candidates);
		}

		return ($value) ? $value : '';
	}

	function find_base_url($url) {
		// Try to find the base url
		$feeds = array($this->rss, $this->rss2, $this->atom);
		$path='';
		$url_url = parse_url($url);
		$url_url['path'] = preg_replace('/\/$/', '', $url_url['path']);
		$host = $url_url['host'];
		if($this->type=='blog') {
			$host_quoted = preg_quote($host);
			foreach ($feeds as $feed) {
				$rss_url = parse_url($feed);
				$rss_quoted = preg_quote($rss_url['host']);
				if ($host == $rss_url['host']) {
					// Same hostname, keep it
					$rss_found = true;
					break;
				} elseif (preg_match("/^www\.$rss_quoted$/", $host)) {
					// hostname from url is the shortest
					$rss_found = true;
					break;
				} elseif (preg_match("/^www\.$host_quoted$/", $rss_url['host']))  {
					// RSS hostname is the shortest
					$host = $rss_url['host'];
					$rss_found = true;
					break;
				}
			}
			if ($rss_found) {
				$rss_url['path'] = preg_replace('/(index\.(.){3,4})*\/+$/', '', $rss_url['path']);
				if (preg_match('/\//', $rss_url['path'])) {  // Still has at least a /, that is a "sub blog"
					$dir_path = dirname($rss_url['path']);
					$len = min(strlen($url_url['path']), strlen($dir_path));
					if ($len > 0) {
						for($i=1;$i<=$len;$i++) {
							if(substr($url_url['path'], 0, $i) != substr($dir_path, 0, $i) ) {
								break;
							}
							$path = substr($dir_path, 0, $i);
						}
					}
				}
			}
		}
		$path = preg_replace('/(index\.(.){3,4})*\/+$/', '', $path);
		if(empty($url_url['scheme'])) $scheme="http";
		else $scheme=$url_url['scheme'];
		$this->url=$scheme.'://'.$host.$path;
	}

	function shortest_text($strArray) {
		$shortest=false;
		foreach ($strArray as $s){
			if (!$shortest || strlen($s) < strlen($shortest)) {
				$shortest=$s;
			}
		}
		return $shortest;
	}

	function store() {
		global $db, $current_user;

		if(! $this->has_key()) $this->calculate_key();

		$blog_type = $this->type;
		$blog_key = $this->key;
		$blog_url = $db->escape($this->url);
		$blog_rss = $db->escape($this->rss);
		$blog_rss2 = $db->escape($this->rss2);
		$blog_atom = $db->escape($this->atom);
		if($this->id===0) {
			$db->query("INSERT INTO blogs (blog_type, blog_key, blog_url, blog_rss, blog_rss2, blog_atom ) VALUES ('$blog_type', '$blog_key', '$blog_url', '$blog_rss', '$blog_rss2', '$blog_atom')");
			$this->id = $db->insert_id;
		} else {
		// update
			$db->query("UPDATE blogs set blog_type='$blog_type', blog_key='$blog_key', blog_url='$blog_url', blog_rss='$blog_rss', blog_rss2='$blog_rss2', blog_atom='$blog_atom' WHERE blog_id=$this->id");
		}
	}

	function read($what='id') {
		global $db, $current_user;

		if($what==='id') {
			$where = "blog_id = $this->id";
		} elseif ($what==='key') {
			$where = "blog_key = '$this->key'";
		} else {
			$where = "blog_url = '$this->url'";
		}
		if(($blog = $db->get_row("SELECT * FROM blogs WHERE $where"))) {
			$this->id=$blog->blog_id;
			$this->type=$blog->blog_type;
			$this->key=$blog->blog_key;
			$this->url=$blog->blog_url;
			$this->rss=$blog->blog_rss;
			$this->rss2=$blog->blog_rss2;
			$this->atom=$blog->blog_atom;
			$this->read = true;
			return true;
		}
		$this->read = false;
		return false;
	}
}
