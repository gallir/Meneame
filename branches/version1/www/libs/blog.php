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
		$this->key = md5($this->url.$this->rss.$this->rss2.$this->atom);
	}

	function has_key() {
		return (strlen($this->key) == 32);
	}

	function analyze_html($url, $html) {
		$rss=false;
		$rss_candidates = array();

		if(preg_match_all('/<link[^>]+text\/xml[^>]+href=[^>]+>/i', $html, $matches)) {
			for ($i=0; $i<count($matches[0]); $i++) {
				if(preg_match('/href="([^"]+)"/i', $matches[0][$i], $matches2)) {
					array_push($rss_candidates,$matches2[1]);
					$this->type='blog';
				}
			}
			$rss=$this->rss=$this->shortest_text($rss_candidates);
		}

		$rss_candidates = array();
		if(preg_match_all('/<link[^>]+application\/atom\+xml[^>]+>/i', $html, $matches)) {
			for ($i=0; $i<count($matches[0]); $i++) {
				if(preg_match('/href="([^"]+)"/i', $matches[0][$i], $matches2)) {
					array_push($rss_candidates,$matches2[1]);
					$this->type='blog';
				}
			}
			$rss=$this->atom=$this->shortest_text($rss_candidates);
		}

		$rss_candidates = array();
		//if(preg_match('/<link[^>]+application\/rss\+xml[^>]+href=[^>]+>/i', $html, $matches)) {
		if(preg_match_all('/<link[^>]+application\/rss\+xml[^>]+>/i', $html, $matches)) {
			for ($i=0; $i<count($matches[0]); $i++) {
				if(preg_match('/href="([^"]+)"/i', $matches[0][$i], $matches2)) {
					array_push($rss_candidates,$matches2[1]);
					$this->type='blog';
				}
			}
			$rss=$this->rss2=$this->shortest_text($rss_candidates);
		}
		// Last try to find a rss
		if($this->type!='blog' && preg_match('/<a[^>]+href="(http[^>]+\.rdf)"/i', $html, $matches2)) {
				$rss=$this->rss=$matches2[1];
				$this->type='blog';
		}


		// Try to find the base url
		$path='';
		$url_url = parse_url($url);
		$url_url['path'] = preg_replace('/\/$/', '', $url_url['path']);
		if($this->type=='blog') {
			$rss_url = parse_url($rss);
			$rss_url['path'] = preg_replace('/\/$/', '', $rss_url['path']);
			if($url_url['host'] == $rss_url['host']) {
				$len = min(strlen($url_url['path']), strlen($rss_url['path']));
				for($i=0;$i<$len;$i++) {
					if(substr($url_url['path'], 0, $i) != substr($rss_url['path'], 0, $i) ) {
						break;
					}
					$path = substr($url_url['path'], 0, $i);
				}
			}
		}
		$path = preg_replace('/\/$/', '', $path);
		if(empty($url_url['scheme'])) $scheme="http";
		else $scheme=$url_url['scheme'];
		$this->url=$scheme.'://'.$url_url['host'].$path;
		$this->calculate_key();
		return $this->type;
	}

	function shortest_text($array) {
		$txt = false;
		// Find the shorter rss
		// There are sites with unordered rss's
		if (count($array) > 0) {
			for ($i=0; $i<count($array); $i++) {
				if (!$txt || strlen($array[$i]) < strlen($txt)) {
					$txt=$array[$i];
				}
			}
			return $txt;
		} else return '';
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
