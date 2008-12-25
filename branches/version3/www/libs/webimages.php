<?
// The source code packaged with this file is Free Software, Copyright (C) 2008 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class BasicThumb {
	public $x = 0;
	public $y = 0;
	public $image = false;
	public $referer = false;
	public $type = 'external';
	public $url = false;
	public $checked = false;
	protected $parsed_url = false;
	protected $parsed_referer = false;


	function __construct($url='', $referer=false) {
		$url = $this->clean_url($url);
		if ($referer) $this->parsed_referer = parse_url($referer);
		if (preg_match('/^\/\//', $url)) { // it's an absolute url wihout http:
			$this->url = "http:$url";
		} elseif ($this->parsed_referer && !preg_match('/https*:\/\//', $url)) {
			$this->url = $this->parsed_referer['scheme'].'://'.$this->parsed_referer['host'];
			if ($this->parsed_referer['port']) $this->url .= ':'.$this->parsed_referer['port'];
			if (preg_match('/^\/+/', $url)) {
				$this->url .= $url;
			} else {
				$this->url .= normalize_path(dirname($this->parsed_referer['path']).'/'.$url);
			}
		} else {
			$this->url = $url;
		}
		$this->parsed_url = parse_url($this->url);
		$this->referer = $referer;
	}

	function clean_url($str) {
		return clean_input_url(preg_replace('/ /', '%20', $str));
	}

	function surface() {
		return $this->x * $this->y;
	}

	function diagonal() {
		return (int) sqrt(pow($this-x, 2) + pow($this->y, 2));
	}

	function ratio() {
		return (max($this->x, $this->y) / min($this->x, $this->y));
	}

	function scale($size=100) {
		if (!$this->image && ! $this->checked) {
			$this->get();
		}
		if (!$this->image) return false;
		if ($this->x > $this->y) {
			$percent = $size/$this->x;
		} else {
			$percent = $size/$this->y;
		}
		$min = min($this->x*$percent, $this->y*$percent);
		if ($min < $size/2) $percent = $percent * $size/2/$min; // Ensure that minimum axis size is size/2
		$new_x = round($this->x*$percent);
		$new_y = round($this->y*$percent);
		$dst = ImageCreateTrueColor($new_x,$new_y);
		imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
		if(imagecopyresampled($dst,$this->image,0,0,0,0,$new_x,$new_y,$this->x,$this->y)) {
			$this->image = $dst;
			$this->x=imagesx($this->image);
			$this->y=imagesy($this->image);
			return true;
		} 
		return false;
	}

	function save($filename) {
		if (!$this->image) return false;
		return imagejpeg($this->image, $filename);
	}

	function get() {
		$res = get_url($this->url);
		$this->checked = true;
		if ($res) {
			$this->content_type = $res['content_type'];
			return $this->fromstring($res['content']);
		} 
		echo "Failed to get $this->url<br>";
		return false;
	}

	function fromstring($imgstr) {
		$this->checked = true;
		$this->image = @imagecreatefromstring($imgstr);
		if ($this->image !== false) {
			$this->x = imagesx($this->image);
			$this->y = imagesy($this->image);
			return true;
		}
		$this->x = $this->y = 0;
		$this->type = 'error';
		return false;
	}



}

class WebThumb extends BasicThumb {
	protected static $visited = array();
	public $candidate = false;

	function __construct($imgtag = '', $referer = '') {
		if (!$imgtag) return;
		$this->tag = $imgtag;
		
		if (!preg_match('/src=["\'](.+?)["\']/i', $this->tag, $matches)) {
			if (!preg_match('/["\']*([\da-z\/]+\.jpg)["\']*/i', $this->tag, $matches)) {
				return;
			}
		} else {
			// Avoid maps, headers and such
			if (preg_match('/usemap=|header/i',  $this->tag)) return;
		}

		parent::__construct($matches[1], $referer);
		$this->type = 'local';

		if (strlen($this->url) < 5 || WebThumb::$visited[$this->url] ) return;
		WebThumb::$visited[$this->url] = true;

		if(preg_match('/[ "]width *[=:][ "]*(\d+)/i', $this->tag, $match)) {
			$this->x = $match[1];
		}
		if(preg_match('/[ "]height *[=:][ "]*(\d+)/i', $this->tag, $match)) {
			$this->y = $match[1];
		}

		// First filter to avoid downloading very small images
		if (($this->x > 0 && $this->x < 80) || ($this->y > 0 && $this->y < 80)) {
			$this->candidate = false;
			return;
		}

		// Check if domain.com are the same for the referer and the url
		if (preg_replace('/.*?([^\.]+\.[^\.]+)$/', '$1', $this->parsed_url['host']) == preg_replace('/.*?([^\.]+\.[^\.]+)$/', '$1', $this->parsed_referer['host']) || preg_match('/gfx\.|cdn\.|imgs*\.|\.img|media\.|cache\.|\.cache|static\.|\.ggpht.com|upload|files|blogspot|blogger|wordpress\.com|wp-content/', $this->parsed_url['host'])) {
			$this->candidate = true;
			//echo "Candidate: $url -> $this->url <br>\n";
		}
	}

	function good() {
		if ($this->candidate && ! $this->checked) {
			$x = $this->x;
			$y = $this->y;
			$this->get();
			// To avoid the selection of images scaled down in the page
			if ($x == 0 || $this->x < $x) $x = $this->x;
			if ($y == 0 || $this->y < $y) $y = $this->y;
		}
		if (preg_match('/\/gif/i', $this->content_type) || preg_match('/\.gif/', $this->url)) {
			$min_size = 140;
			$min_surface = 35000;
		} else {
			$min_size = 90;
			$min_surface = 20000;
		}
		return $x >= $min_size && $y >= $min_size && ($x*$y) > $min_surface && $this->ratio() < 3.5 && !preg_match('/button|banner|\Wban[_\W]|\Wads\W|\Wpub\W|logo|header/i', $this->url);
	}

}

class HtmlImages {
	public $html = '';
	public $selected = false;

	function __construct($url) {
		$this->url = $url;
		$this->base = $url;
	}

	function get() {
		$res = get_url($this->url);
		if (!$res) return;
		if (preg_match('/^image/i', $res['content_type'])) {
			$img = new BasicThumb($this->url);
			if ($img->fromstring($res['content'])) {
				$img->type = 'local';
				$img->candidate = true;
				$this->selected = $img;
			}
		} elseif (preg_match('/text\/html/i', $res['content_type'])) {
			$html = $res['content'];

			// First check for thumbnail head metas
			if (preg_match('/<link +rel=[\'"]image_src[\'"] +href=[\'"](.+?)[\'"].*?>/is', $html, $match) ||
				preg_match('/<meta +name=[\'"]thumbnail_url[\'"] +content=[\'"](.+?)[\'"].*?>/is', $html, $match)) {
				$url = $match[1];
				echo "<!-- Try to select from $url -->\n";
				$img = new BasicThumb($url);
				if ($img->get()) {
					$img->type = 'local';
					$img->candidate = true;
					$this->selected = $img;
					echo "<!-- Selected from $img->url -->\n";
					return $this->selected;
				}
			}


			// Check if there are players
			if (preg_match('/(<|&lt;)(embed|object|param)/i', $html)) {
				$this->html = &$html;
				echo "<!-- Searching for video -->\n";
				if ($this->check_youtube()) return $this->selected;
				if ($this->check_google_video()) return $this->selected;
				if ($this->check_metacafe()) return $this->selected;
				if ($this->check_vimeo()) return $this->selected;
				if ($this->check_zapp_internet()) return $this->selected;
				if ($this->check_daily_motion()) return $this->selected;
			}

			// Analyze HTML <img's
			if (preg_match('/<base *href=["\'](.+?)["\']/i', $html, $match)) {
				$this->base = $match[1];
			}
			$html = preg_replace('/^.*?<body[^>]*?>/is', '', $html); // Search for body
			$html = preg_replace('/<*!--.+?-->/s', '', $html); // Delete commented HTML
			$html = preg_replace('/<style[^>]*?>.+?<\/style>/is', '', $html); // Delete javascript
			$html = preg_replace('/<script[^>]*?>.*?<\/script>/is', '', $html); // Delete javascript
			$html = preg_replace('/<noscript[^>]*?>.*?<\/noscript>/is', '', $html); // Delete javascript
			$html = preg_replace('/[ ]{3,}/ism', '', $html); // Delete useless spaces
			/* $html = preg_replace('/^.*?<h1[^>]*?>/is', '', $html); // Search for a h1 */
			$html = substr($html, 0, 30000); // Only analyze first X bytes
			$this->html = $html;
			$this->parse_img();
		}
		return $this->selected;
	}

	function parse_img() {
		preg_match_all('/(<img\s.+?>|["\'][\da-z\/]+\.jpg["\'])/is', $this->html, $matches);
		$goods = $n = 0;
		foreach ($matches[0] as $match) {
			$img = new WebThumb($match, $this->base);
			if ($img->candidate && $img->good()) {
				$goods++;
				$img->coef = intval($img->surface()/$img->diagonal()/pow($img->ratio(), 1));
				echo "\n<!-- CANDIDATE: ". htmlentities($img->url)." X: $img->x Y: $img->y Diagonal: ".$img->diagonal()." Aspect: ".$img->ratio()." Coef1: $img->coef Coef2: ".intval($img->coef/1.5)." -->\n";
				if (!$this->selected || ($this->selected->coef < $img->coef/1.5)) {
					$this->selected = $img;
					$n++;
					echo "<!-- SELECTED: ". htmlentities($img->url)." X: $img->x Y: $img->y -->\n";
				}
			}
			if ($goods > 5 && $n > 0) break;
		}
		if ($this->selected && ! $this->selected->image) {
			$this->selected->get();
		}
		return $this->selected;
	}

	// Google Video detection
	function check_google_video() {
		if (preg_match('/=["\']http:\/\/video\.google\.[a-z]{2,5}\/.+?\?docid=(.+?)&/i', $this->html, $match)) {
			$video_id = $match[1];
			echo "<!-- Detect Google Video, id: $video_id -->\n";
			if ($video_id) {
				$url = $this->get_google_thumb($video_id);
				if($url) {
					$img = new BasicThumb($url);
					if ($img->get()) {
						$img->type = 'local';
						$img->candidate = true;
						$this->selected = $img;
						echo "<!-- Video selected from $img->url -->\n";
						return $this->selected;
					}
				}
			}
		}
		return false;
	}

	function get_google_thumb($videoid) {
		if(($res = get_url("http://video.google.com/videofeed?docid=$videoid"))) {
			$vrss = $res['content'];
			if($vrss) {
				preg_match('/<media:thumbnail url=["\'](.+?)["\']/',$vrss,$thumbnail_array);
				return $thumbnail_array[1];
			}
		}
		return false;
	}

	// Youtube detection
	function check_youtube() {
		if (preg_match('/http:\/\/www\.youtube\.com\/v\/(.+?)&/i', $this->html, $match)) {
			$video_id = $match[1];
			echo "<!-- Detect Youtube, id: $video_id -->\n";
			if ($video_id) {
				$url = $this->get_youtube_thumb($video_id);
				if($url) {
					$img = new BasicThumb($url);
					if ($img->get()) {
						$img->type = 'local';
						$img->candidate = true;
						$this->selected = $img;
						echo "<!-- Video selected from $img->url -->\n";
						return $this->selected;
					}
				}
			}
		}
		return false;
	}

	function get_youtube_thumb($videoid) {
		$thumbnail = false;
		if(($res = get_url("http://gdata.youtube.com/feeds/api/videos/$videoid"))) {
			$vrss = $res['content'];
			$previous = 0;
			if($vrss && 
				preg_match_all('/<media:thumbnail url=["\'](.+?)["\'].*?width=["\'](\d+)["\']/',$vrss,$matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					if ($match[2] > $previous) {
						$thumbnail = $match[1];
						$previous = $match[2];
					}
				}
			}
		}
		return $thumbnail;
	}

	// Metaface detection
	function check_metacafe() {
		if (preg_match('/=["\']http:\/\/www\.metacafe\.com\/fplayer\/(\d+)\//i', $this->html, $match)) {
			$video_id = $match[1];
			echo "<!-- Detect Metacafe, id: $video_id -->\n";
			if ($video_id) {
				$url = $this->get_metacafe_thumb($video_id);
				if($url) {
					$img = new BasicThumb($url);
					if ($img->get()) {
						$img->type = 'local';
						$img->candidate = true;
						$this->selected = $img;
						echo "<!-- Video selected from $img->url -->\n";
						return $this->selected;
					}
				}
			}
		}
		return false;
	}

	function get_metacafe_thumb($videoid) {
		if(($res = get_url("http://www.metacafe.com/api/item/$videoid"))) {
			$vrss = $res['content'];
			if($vrss) {
				preg_match('/<media:thumbnail url=["\'](.+?)["\']/',$vrss,$thumbnail_array);
				return $thumbnail_array[1];
			}
		}
		return false;
	}

	// Vimeo detection
	function check_vimeo() {
		if (preg_match('/=["\']http:\/\/vimeo\.com\/moogaloop\.swf\?clip_id=(\d+)/i', $this->html, $match)) {
			$video_id = $match[1];
			echo "<!-- Detect Vimeo, id: $video_id -->\n";
			if ($video_id) {
				$url = $this->get_vimeo_thumb($video_id);
				if($url) {
					$img = new BasicThumb($url);
					if ($img->get()) {
						$img->type = 'local';
						$img->candidate = true;
						$this->selected = $img;
						echo "<!-- Video selected from $img->url -->\n";
						return $this->selected;
					}
				}
			}
		}
		return false;
	}

	function get_vimeo_thumb($videoid) {
		if(($res = get_url("http://vimeo.com/api/clip/$videoid.xml"))) {
			$vrss = $res['content'];
			if($vrss) {
				preg_match('/<thumbnail_large>(.+)<\/thumbnail_large>/i',$vrss,$thumbnail_array);
				return $thumbnail_array[1];
			}
		}
		return false;
	}

	// ZappInternet Video detection
	function check_zapp_internet() {
		if (preg_match('#http://zappinternet\.com/v/([^&]+)#i', $this->html, $match)) {
			$video_id = $match[1];
			echo "<!-- Detect Zapp Internet Video, id: $video_id -->\n";
			if ($video_id) {
				$url = $this->get_zapp_internet_thumb($video_id);
				if($url) {
					$img = new BasicThumb($url);
					if ($img->get()) {
						$img->type = 'local';
						$img->candidate = true;
						$this->selected = $img;
						echo "<!-- Video selected from $img->url -->\n";
						return $this->selected;
					}
				}
			}
		}

		return false;
	}

	function get_zapp_internet_thumb($videoid) {
		return 'http://zappinternet.com/videos/'.substr($videoid, 0, 1).'/frames/'.$videoid.'.jpg';
	}

	// Daily Motion Video detection
	function check_daily_motion() {
		if (preg_match('#=["\']http://www.dailymotion.com/swf/([^&"\']+)#i', $this->html, $match)) {
			$video_id = $match[1];
			echo "<!-- Detect Daily Motion Video, id: $video_id -->\n";
			if ($video_id) {
				$url = $this->get_daily_motion_thumb($video_id);
				if($url) {
					$img = new BasicThumb($url);
					if ($img->get()) {
						$img->type = 'local';
						$img->candidate = true;
						$this->selected = $img;
						echo "<!-- Video selected from $img->url -->\n";
						return $this->selected;
					}
				}
			}
		}
		return false;
	}

	function get_daily_motion_thumb($videoid) {
		return 'http://www.dailymotion.com/thumbnail/160x120/video/'.$videoid;
	}

}

function normalize_path($path) {
	$path = preg_replace('~/\./~', '/', $path);
    // resolve /../
    // loop through all the parts, popping whenever there's a .., pushing otherwise.
	$parts = array();
	foreach (explode('/', preg_replace('~/+~', '/', $path)) as $part) {
		if ($part === "..") {
			array_pop($parts);
		} elseif ($part) {
			$parts[] = $part;
		}
	}
	return '/' . implode("/", $parts);
}

function get_url($url) {
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
	curl_setopt($session, CURLOPT_URL, $url);
	curl_setopt($session, CURLOPT_USERAGENT, $globals['user_agent']);
	curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($session, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($session, CURLOPT_MAXREDIRS, 20);
	curl_setopt($session, CURLOPT_TIMEOUT, 20);
	$result['content'] = curl_exec($session);
	if (!$result['content']) return false;
	$result['content_type'] = curl_getinfo($session, CURLINFO_CONTENT_TYPE);
	return $result;
}

?>
