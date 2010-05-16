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
	public $video = false;
	public $referer = false;
	public $type = 'external';
	public $url = false;
	public $checked = false;
	protected $parsed_url = false;
	protected $parsed_referer = false;


	function __construct($url='', $referer=false) {
		$url = $this->clean_url($url);
		if ($referer) $this->parsed_referer = parse_url($referer);
		$this->url = build_full_url($url, $referer);
		$this->parsed_url = parse_url($this->url);
		if ($referer) {
			$this->referer = $referer;
			$this->parsed_referer = parse_url($this->referer);
		}
	}

	function clean_url($str) {
		// Decode HTML entities
		//$str = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $str);
		//$str = preg_replace('~&#0*([0-9]+);~e', 'chr(\\1)', $str);
		return  urldecode(preg_replace('/[<>\r\n\t]/', '', $str));
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

	function round_corners() {
		$white = imagecolorallocatealpha($this->image, 255, 255, 255, 0);
		$semi_white = imagecolorallocatealpha($this->image, 255, 255, 255, 27);
		$semi_semi_white = imagecolorallocatealpha($this->image, 255, 255, 255, 87);
		if (!$white || !$semi_white) return;
		// Top left
		imagesetpixel($this->image, 0, 0, $white);
		imagesetpixel($this->image, 1, 0, $semi_white);
		imagesetpixel($this->image, 0, 1, $semi_white);
		imagesetpixel($this->image, 2, 0, $semi_semi_white);
		imagesetpixel($this->image, 0, 2, $semi_semi_white);
		// Top right
		imagesetpixel($this->image, $this->x-1, 0, $white);
		imagesetpixel($this->image, $this->x-2, 0, $semi_white);
		imagesetpixel($this->image, $this->x-1, 1, $semi_white);
		imagesetpixel($this->image, $this->x-3, 0, $semi_semi_white);
		imagesetpixel($this->image, $this->x-1, 2, $semi_semi_white);
		// Bottom left
		imagesetpixel($this->image, 0, $this->y-1, $white);
		imagesetpixel($this->image, 0, $this->y-2, $semi_white);
		imagesetpixel($this->image, 1, $this->y-1, $semi_white);
		imagesetpixel($this->image, 0, $this->y-3, $semi_semi_white);
		imagesetpixel($this->image, 2, $this->y-1, $semi_semi_white);
		// Bottom right
		imagesetpixel($this->image, $this->x-1, $this->y-1, $white);
		imagesetpixel($this->image, $this->x-1, $this->y-2, $semi_white);
		imagesetpixel($this->image, $this->x-2, $this->y-1, $semi_white);
		imagesetpixel($this->image, $this->x-1, $this->y-3, $semi_semi_white);
		imagesetpixel($this->image, $this->x-3, $this->y-1, $semi_semi_white);
		
	}

	function save($filename) {
		if (!$this->image) return false;
		$this->round_corners();
		return imagejpeg($this->image, $filename, 80);
	}

	function get() {
		$res = get_url($this->url, $this->referer, 1000000);
		$this->checked = true;
		if ($res && strlen($res['content']) < 1000000) { // Image is smaller than our limit
			$this->content_type = $res['content_type'];
			return $this->fromstring($res['content']);
		} 
		echo "<!-- Failed to get $this->url -->\n";
		return false;
	}

	function fromstring(&$imgstr) {
		$this->checked = true;
		$this->image = @imagecreatefromstring($imgstr);
		if ($this->image !== false) {
			$this->x = imagesx($this->image);
			$this->y = imagesy($this->image);
			return true;
		}
		$this->x = $this->y = 0;
		$this->type = 'error';
		echo "<!-- GET error: $this->url: $this->x, $this->y-->\n";
		return false;
	}
}

class WebThumb extends BasicThumb {
	protected static $visited = array();
	public $candidate = false;
	public $html_x = 0;
	public $html_y = 0;
	public $weight = 1;

	function __construct($imgtag = '', $referer = '') {
		if (!$imgtag) return;
		$this->tag = $imgtag;
		
		// poster captures also HTML5 video thumbnails
		if (!preg_match('/(?:src|poster) *=["\'](.+?)["\']/i', $this->tag, $matches) 
			&& !preg_match('/(?:src|poster) *=([^ ]+)/i', $this->tag, $matches)) { // Some sites don't use quotes
			if (!preg_match('/["\']((http:){0,1}[\.\d\w\-\/]+\.jpg)["\']/i', $this->tag, $matches)) {
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

		// Avoid images generated by scripts with different IDs per page
		if ((! $this->referer || $this->parsed_referer['host'] != $this->parsed_url['host']) 
				&& !preg_match('/\.jpg/', $this->url) && preg_match('#/.+?\?.+?&.+?&.+#', $this->url))  {
			return;
		}

		if(preg_match('/[ "]width *[=:][ \'"]*(\d+)/i', $this->tag, $match)) {
			$this->html_x = $this->x = intval($match[1]);
			$this->weight *= 1.5; // Give preference to images with img attributes
		}
		if(preg_match('/[ "]height *[=:][ \'"]*(\d+)/i', $this->tag, $match)) {
			$this->html_y = $this->y = intval($match[1]);
			$this->weight *= 1.5; // Give preference to images with img attributes
		}

		// First filter to avoid downloading very small images
		if (($this->x > 0 && $this->x < 100) || ($this->y > 0 && $this->y < 100)) {
			return;
		}

		if (!preg_match('/loading|button|banner|\Wads\W|\Wpub\W|\/logo|header|rss|advertising/i', $this->url)) {
			$this->candidate = true;
		}
	}

	function get() {
		if( !parent::get() ) return false;
		// Ensure we use the html "virtual" size
		// to avoid the selection of images scaled down in the page
		if ($this->html_x == 0 && $this->html_y == 0) {
			$this->html_x = $this->x;
			$this->html_y = $this->y;
		} elseif ($this->html_x == 0) {
			$this->html_x = intval($this->html_y * $this->x / $this->y);
		} else {
			$this->html_y = intval($this->html_x * $this->y / $this->x);
		}
		//echo "Got: $this->html_x, $this->html_y $url -> $this->url<br>\n";
		return true;
	}

	function surface() {
		return $this->html_x * $this->html_y;
	}

	function diagonal() {
		return (int) sqrt(pow($this->html_x, 2) + pow($this->html_y, 2));
	}

	function ratio() {
		return (max($this->html_x, $this->html_y) / min($this->html_x, $this->html_y));
	}

	function max() {
		return max($this->html_x, $this->html_y);
	}


	function good($strict = false) {
		if ($this->candidate && ! $this->checked) {
			if (!$this->get()) {
				return false;
			}
			$x = $this->html_x;
			$y = $this->html_y;
		}
		if ($strict) {
			$min_size = 220;
			$min_surface = 110000;
		} elseif (preg_match('/\/gif/i', $this->content_type) || preg_match('/\.gif/', $this->url)) {
			$min_size = 140;
			$min_surface = 35000;
			$this->weight = 0.75; // Prefer JPGs over GIFs
		} else {
			$min_size = 100;
			$min_surface = 16500;
		}
		echo "<!-- x:$x y:$y minsize: $min_size -->\n";
		return $x >= $min_size && $y >= $min_size && ( 
			(($x*$y) > $min_surface && $this->ratio() < 3.5) || 
			( $x > $min_size*4 && ($x*$y) > $min_surface*3 && $this->ratio() < 4.6)); // For panoramic photos
	}

}

class HtmlImages {
	public $html = '';
	public $alternate_html = '';
	public $selected = false;
	public $referer = false;
	public $debug = false;

	function __construct($url, $site = false) {
		$this->url = $url;
		$this->parsed_url = parse_url($url);
		$this->base = $url;
		$this->site = $site;
		$this->redirected = false;
	}

	function get() {
		$res = get_url($this->url, $this->referer);
		if (!$res) {
			echo "<!-- Error getting " . htmlentities($this->url) . "-->\n";
			return;
		}
		if ($this->debug) echo "<!-- Got $this->url (". strlen($res['content']) .") -->\n";
		if ($res['location'] != $this->url) {
			$this->redirected = clean_input_url($res['location']);
			$this->parsed_redirected = parse_url($this->redirected);
			if ($this->debug)
				echo "<!-- Redirected to URL: $this->redirected -->\n";
		}

		if (preg_match('/^image/i', $res['content_type'])) {
			$img = new BasicThumb($this->url);
			if ($img->fromstring($res['content'])) {
				$img->type = 'local';
				$img->candidate = true;
				$this->selected = $img;
			}
		} elseif (preg_match('/text\/html/i', $res['content_type'])) {
			$this->html = $res['content'];
			$this->title = get_html_title($this->html);
			if ($this->debug) echo "<!-- HTML $this->title -->\n";

			// First check for thumbnail head metas
			if ((preg_match('/<link\s+?rel=[\'"]image_src[\'"]\s+?href=[\'"](.+?)[\'"].*?>/is', $this->html, $match) ||
				preg_match('/<meta\s+?name=[\'"]thumbnail_url[\'"]\s+?content=[\'"](.+?)[\'"].*?>/is', $this->html, $match))
				&& ! preg_match('/meneame/i', $match[1])) { // a bad thumbnail meta in aldea-irreductible
				$url = $match[1];
				if ($this->debug)
					echo "<!-- Try to select from $url -->\n";
				$img = new BasicThumb($url);
				if ($img->get()) {
					$img->type = 'local';
					$img->candidate = true;
					$this->selected = $img;
					if ($this->debug)
						echo "<!-- Selected from $img->url -->\n";
					return $this->selected;
				}
			}


			// Analyze HTML <img's
			if (preg_match('/<base *href=["\'](.+?)["\']/i', $this->html, $match)) {
				$this->base = $match[1];
			}
			$html_short = $this->shorten_html($this->html);
			//  echo "<!-- $this->html -->\n";
			$this->parse_img($html_short);

			// If there is no image or image is slow
			// Check if there are players
			if ((!$this->selected || $this->selected->surface() < 120000)
					&& $this->other_html 
					&& preg_match('/((<|&lt;)embed|(<|&lt;)object|(<|&lt;)param|\.flv)/i', $this->html)) {
				if ($this->debug)
					echo "<!-- Searching for video -->\n";
				if ($this->check_youtube() || 
						$this->check_google_video() ||
						$this->check_metacafe() ||
						$this->check_vimeo() ||
						$this->check_zapp_internet() ||
						$this->check_daily_motion() ||
						$this->check_elmundo_video() ) {
					$this->selected->video = true;
					return $this->selected;
				}
			}

		}
		return $this->selected;
	}

	function shorten_html(&$html, $max = 200000) {
			$html = preg_replace('/^.*?<body[^>]*?>/is', '', $html); // Search for body
			$html = preg_replace('/< *!--.*?-->/s', '', $html); // Delete commented HTML
			$html = preg_replace('/<style[^>]*?>.+?<\/style>/is', '', $html); // Delete styles
			/* $html = preg_replace('/<script[^>].*?>.*?<\/script>/is', '', $html); // Delete javascript */
			$html = preg_replace('/<noscript>.*?<\/noscript>/is', '', $html); // Delete javascript 
			$html = preg_replace('/< *(div|span)[^>]{10,}>/is', '<$1>', $html); // Delete long divs and span with style
			$html = preg_replace('/[ ]{3,}/ism', '', $html); // Delete useless spaces
			/* $html = preg_replace('/^.*?<h1[^>]*?>/is', '', $html); // Search for a h1 */
			$html = substr($html, 0, $max); // Only analyze first X bytes
			return $html;
	}

	function parse_img(&$html) {
		$tags = array();
		preg_match_all('/(<img\s.+?>)/is', $html, $matches);
		$tags = array_merge($tags, $matches[1]);
		// Check also HTML5 video (for poster parameter)
		preg_match_all('/(<video\s.+?poster.+?>)/is', $html, $matches);
		$tags = array_merge($tags, $matches[1]);
		// Try with plain links in javascripts (RTVE uses it...)
		preg_match_all('/["\']image["\'][, ]+(["\'](http:){0,1}[\.\d\w\-\/]+\.jpg["\'])/is', $html, $matches);
		$tags = array_merge($tags, $matches[1]);
		// Now try with images in JS arrays (Clarin uses it...)
		preg_match_all('/\( *(["\'](http:){0,1}[\.\d\w\-\/]+\.jpg["\']) *[\),]/is', $html, $matches);
		$tags = array_merge($tags, $matches[1]);
		if (! count($tags)) return false;
		$this->images_count =  count($tags);

		/*
		if (!$this->get_other_html()) {
			if ($this->debug) {
				echo "<!-- No other html to compare -->\n";
			}
			return false;
		}
		*/

		$other_html = $this->get_other_html();
		if (!$other_html && $this->debug) {
			echo "<!-- No other html to compare -->\n";
		}

		$goods = $n = 0;
		foreach ($tags as $match) {
			//if ($this->debug)
			//	echo "<!-- PRE CANDIDATE: ". htmlentities($match) ." -->\n";
			if ($this->check_in_other($match)) continue;
			$img = new WebThumb($match, $this->base);
			if ($img->candidate && $img->good($other_html == false)) {
				$goods++;
				$img->coef = intval($img->surface()/(($img->html_x+$img->html_y)/2) * $img->weight);
				if ($this->debug)
					echo "<!-- CANDIDATE: ". htmlentities($img->url)." X: $img->html_x Y: $img->html_y Surface: ".$img->surface()." Coef1: $img->coef Coef2: ".intval($img->coef/1.5)." -->\n";
				if (!$this->selected || ($this->selected->coef < $img->coef/1.5)) {
					$this->selected = $img;
					$n++;
					if ($this->debug)
						echo "<!-- SELECTED: ". htmlentities($img->url)." X: $img->html_x Y: $img->html_y -->\n";
				}
			}
			if ($goods > 5 && $n > 0) break;
		}
		if ($this->selected && ! $this->selected->image) {
			$this->selected->get();
		}
		return $this->selected;
	}

	function get_other_html() {
		$selection = array();
		$levels = array();
		$level_max = array();

		// Tries to find an alternate page to check for "common images" and ignore them
		$this->other_html = false;
		$this->path_query = unify_path_query($this->parsed_url['path'], $this->parsed_url['query']);
		if ($this->html) {
			if ($this->debug)
					echo "<!-- Analyzing html: ". strlen($this->html). " bytes -->\n";
			$regexp = '[a-z]+?:\/\/'.preg_quote($this->parsed_url['host']).'\/[^\"\'>]+?';
			if ($this->site) {
				$parsed = parse_url($this->site);
				if ($parsed['host'] != $this->parsed_url['host']) {
					$regexp .= '|'.preg_quote($this->site, '/').'\/[^\"\'>]+?';
				}
			}
			if ($this->redirected && $this->parsed_redirected['host'] != $this->parsed_url['host']) {
				$regexp .= '|[a-z]+?:\/\/'.preg_quote($this->parsed_redirected['host']).'\/[^\"\'>]+?';
			}
			$regexp .= '|[\/\.][^\"\']+?|\w[^\"\':]+?';
		
			$seen = array();
			$visited = array();
			if (preg_match_all("/<a[^>]*\shref *= *[\"\']($regexp)[\"\']/is",$this->html,$matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					if ( preg_match('/\.(gif|jpg|zip|png|jpeg|rar|mp[1-4]|mov|mpeg|mpg|pdf|ps|gz|tar|tif)($|\s)/i', $match[1]) 
						|| preg_match('/^#/', $match[1])
						|| preg_match('/\?cat=\d+$/i', $match[1])
						|| preg_match('/(feed|rss|atom|trackback|search|download)\W/i', $match[1])) {
						continue;
					}
					$weight = 1;
					$url = preg_replace('/&amp;/i', '&', $match[1]);
					$url = preg_replace('/#.+/i', '', $url);
					$url = preg_replace('/[\?&]\s*$/i', '', $url); // Some urls with void &'s at the end
					$url = build_full_url(trim($url), $this->url);
					if (!$url) continue;

					if ($seen[$url]) continue;
					$seen[$url] = true;
					if ($this->debug)
						echo "<!-- Adding before analyzing: $url -->\n";

					$parsed_match = parse_url($url);
					$path_query_match = unify_path_query($parsed_match['path'], $parsed_match['query']);

					if ($visited[$path_query_match] || $this->path_query == $path_query_match) continue;
					$visited[$path_query_match] = true;

					$equals = min(path_equals($path_query_match, $this->path_query), path_count($path_query_match)-1);

					if ($equals > 0 && path_count($path_query_match) != path_count($this->path_query)) {
							// TODO: convert these checks in one iteration
							if (preg_replace('#.*?(/\d{4,}/*\d{2,}/*\d{2,}/*\d{2,}/).*#', '$1', $path_query_match) ==
								// Penalize with up to four levels if urls has same "dates"
								preg_replace('#.*?(/\d{4,}/*\d{2,}/*\d{2,}/*\d{2,}/).*#', '$1', $this->path_query)) {
								$c = 4;
							} elseif (preg_replace('#.*?(/\d{4,}/*\d{2,}/*\d{2,}/).*#', '$1', $path_query_match) ==
								// Penalize with up to three levels if urls has same "dates"
								preg_replace('#.*?(/\d{4,}/*\d{2,}/*\d{2,}/).*#', '$1', $this->path_query)) {
								$c = 3;
							} elseif (preg_replace('#.*?(/\d{4,}/*\d{2,}/).*#', '$1', $path_query_match) ==
								// Penalize with up to two levels if urls has same "dates"
								preg_replace('#.*?(/\d{4,}/*\d{2,}/).*#', '$1', $this->path_query)) {
								$c = 2;
							} 
						$equals = max(0, $equals-$c);
					}

					// Penalize with a level if one has query and the other does not
					if (empty($parsed_match['query']) != empty($this->parsed_url['query'])) {
						$equals = $equals-2;
					}

					$distance = levenshtein($path_query_match, $this->path_query) 
								* min(strlen($path_query_match), strlen($this->path_query))
								/ max(strlen($path_query_match), strlen($this->path_query));

					$item = array($url, $distance);
					$levels[$equals][] = $item;
					if ($this->debug)
						echo "<!-- Adding ($equals, $distance): ".$match[1]." ($path_query_match) -->\n";
				}

				// Insert in selection ordered by level and the distance
				krsort($levels);
				foreach ($levels as $level => $items) {
					usort($items, 'sort_url_distance_items');
					foreach ($items as $item) {
						$selection[] = $item[0];
					}
				}
			}

			if (count($selection) > 2) { // we avoid those simple pages with few links to other pages
				$max_to_check = max(2, min(4, count($selection) / 5));
				$n = $checked = $same_title = $other_title = $images_total = 0;
				$paths = array();
				$paths_visited = array();
				$paths[path_sub_path($this->path_query, 2)] =  path_count($this->path_query);
				foreach ($selection as $url) {
					if ($checked > 10) break;

					$parsed = parse_url($url);
					$unified = unify_path_query($parsed['path'], $parsed['query']);
					$first_paths = path_sub_path($unified, 2);

					$paths_visited[$first_paths] += 1;
					if ($paths_visited[$first_paths] > 2) {
						if ($this->debug)
							echo "<!-- Ignoring $url by equal path: ".$first_paths." -->\n";
						continue;
					}

					$paths_len = path_count($unified);
					if ($paths[$first_paths] && $paths_len < $paths[$first_paths]) {
						// Don't get twice a page with similar but shorter paths
						if ($this->debug)
							echo "<!-- Ignoring $url by previous path: $first_paths and lenght: $paths_len] -->\n";
						continue;
					}

					if ($this->debug)
						echo "<!-- Checking: $url -->\n";

					$checked ++;
					$res = get_url($url, $this->url);

					if (! $res || ! preg_match('/text\/html/i', $res['content_type'])) 
						continue;

					if ($res['location'] != $url) {
						$location_parsed = parse_url($res['location']);
						$location_unified = unify_path_query($location_parsed['path'], $location_parsed['query']);
						if ($location_parsed['host'] != $parsed['host'] 
								&& $location_parsed['host'] != $this->parsed_redirected['host']) {
							if ($this->debug)
								echo "<!-- Redirected to another host: ".$res['location'].", skipping -->\n";
							continue;
						} elseif ($location_unified  == $this->path_query) {
							if ($this->debug)
								echo "<!-- Redirected to same address: ".$res['location'].", skipping -->\n";
							continue;
						} elseif (path_count($location_unified) < path_count($this->path_query) 
									&& path_count($location_unified) < path_count($unified) ) {
							if ($this->debug)
								echo "<!-- Redirected to a shorter path: $url -> ".$res['location'].", skipping -->\n";
							continue;
						}
					}

					$images_count = preg_match_all('/<img .+?>/is', $res['content'], $dummy);
					if (! $images_count) 
						continue;
					$images_total += $images_count;

					// Check if it has the same title
					if (empty($this->title) || $this->title == get_html_title($res['content'])) {
						$same_title++;
						// Next iff we found less that 3 pages, otherwise we asume all pages have same title
						if ($same_title < 3 || $other_title) {
							echo "<!-- Skipping: same title $url -->\n";
							continue;
						}
					} else {
						$other_title++;
					}

					if ($this->debug)
						echo "<!-- Other: read $url -->\n";
					$paths[$first_paths] = max($paths_len, $paths[$first_paths]);
					$n++;
					$this->other_html .= $this->shorten_html($res['content'], 100000). "<!-- END part $n -->\n";
					if ($n > $max_to_check || $images_total > $this->images_count * 2) {
						break;
					}
				}
			}
		}
		return $this->other_html;
	}

	function check_in_other($str) {
		if (preg_match('/'.preg_quote($str,'/').'/', $this->other_html)) {
				if ($this->debug)
					echo "<!-- Skip: " . htmlentities($str). "-->\n";
				return true;
		}
		return false;
	}

	// VIDEOS

	// Google Video detection
	function check_google_video() {
		if (preg_match('/=["\']http:\/\/video\.google\.[a-z]{2,5}\/.+?\?docid=(.+?)&/i', $this->html, $match) &&
				(preg_match('/video\.google/', $this->parsed_url['host']) || ! $this->check_in_other($match[1]))) {
			$video_id = $match[1];
			if ($this->debug)
				echo "<!-- Detect Google Video, id: $video_id -->\n";
			if ($video_id) {
				$url = $this->get_google_thumb($video_id);
				if($url) {
					$img = new BasicThumb($url);
					if ($img->get()) {
						$img->type = 'local';
						$img->candidate = true;
						$this->selected = $img;
						if ($this->debug)
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
		if ((preg_match('/youtube\.com/', $this->parsed_url['host']) && preg_match('/v=([\w_\-]+)/i', $this->url, $match)) ||
			(preg_match('/http:\/\/www\.youtube\.com\/v\/(.+?)[\"\'&]/i', $this->html, $match) && ! $this->check_in_other($match[1]))) {
			$video_id = $match[1];
			if ($this->debug)
				echo "<!-- Detect Youtube, id: $video_id -->\n";
			if ($video_id) {
				$url = $this->get_youtube_thumb($video_id);
				if($url) {
					$img = new BasicThumb($url);
					if ($img->get()) {
						$img->type = 'local';
						$img->candidate = true;
						$this->selected = $img;
						if ($this->debug)
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
		if (preg_match('/=["\']http:\/\/www\.metacafe\.com\/fplayer\/(\d+)\//i', $this->html, $match) &&
				(preg_match('/metacafe\.com/', $this->parsed_url['host']) || ! $this->check_in_other($match[1]))) {
			$video_id = $match[1];
			if ($this->debug)
				echo "<!-- Detect Metacafe, id: $video_id -->\n";
			if ($video_id) {
				$url = $this->get_metacafe_thumb($video_id);
				if($url) {
					$img = new BasicThumb($url);
					if ($img->get()) {
						$img->type = 'local';
						$img->candidate = true;
						$this->selected = $img;
						if ($this->debug)
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

	// Elmundo.es detection
	function check_elmundo_video() {
		if (preg_match('#ArchivoFlash *= *"(http.+?reproductor_video.swf)".+?fotograma=(.+?\.jpg)#is', $this->html, $match) &&
			! $this->check_in_other($match[2])) {
			$server = $match[1];
			$url = $match[2];
			if ($this->debug)
				echo "<!-- Detected El Mundo, fotograma: $url -->\n";
			if ($url) {
				$img = new BasicThumb($url, $server);
				if ($img->get()) {
					$img->type = 'local';
					$img->candidate = true;
					$this->selected = $img;
					if ($this->debug)
						echo "<!-- Video selected from $img->url -->\n";
					return $this->selected;
				}
			}
		}
		return false;
	}

	// Vimeo detection
	function check_vimeo() {
		if (preg_match('/=["\']http:\/\/vimeo\.com\/moogaloop\.swf\?clip_id=(\d+)/i', $this->html, $match) &&
				(preg_match('/vimeo\.com/', $this->parsed_url['host']) || ! $this->check_in_other($match[1]))) {
			$video_id = $match[1];
			if ($this->debug)
				echo "<!-- Detect Vimeo, id: $video_id -->\n";
			if ($video_id) {
				$url = $this->get_vimeo_thumb($video_id);
				if($url) {
					$img = new BasicThumb($url);
					if ($img->get()) {
						$img->type = 'local';
						$img->candidate = true;
						$this->selected = $img;
						if ($this->debug)
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
		if (preg_match('#http://zappinternet\.com/v/([^&]+)#i', $this->html, $match) &&
				(preg_match('/zappinternet\.com/', $this->parsed_url['host']) || ! $this->check_in_other($match[1]))) {
			$video_id = $match[1];
			if ($this->debug)
				echo "<!-- Detect Zapp Internet Video, id: $video_id -->\n";
			if ($video_id) {
				$url = $this->get_zapp_internet_thumb($video_id);
				if($url) {
					$img = new BasicThumb($url);
					if ($img->get()) {
						$img->type = 'local';
						$img->candidate = true;
						$this->selected = $img;
						if ($this->debug)
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
		if (preg_match('#=["\']http://www.dailymotion.com/swf/([^&"\']+)#i', $this->html, $match) &&
				(preg_match('/dailymotion\.com/', $this->parsed_url['host']) || ! $this->check_in_other($match[1]))) {
			$video_id = $match[1];
			if ($this->debug)
				echo "<!-- Detect Daily Motion Video, id: $video_id -->\n";
			if ($video_id) {
				$url = $this->get_daily_motion_thumb($video_id);
				if($url) {
					$img = new BasicThumb($url);
					if ($img->get()) {
						$img->type = 'local';
						$img->candidate = true;
						$this->selected = $img;
						if ($this->debug)
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

function build_full_url($url, $referer) {
	$parsed_referer = @parse_url($referer);

	if (preg_match('/^\/\//', $url)) { // it's an absolute url wihout http:
            return $parsed_referer['scheme']."$url";
	}

	$parsed_url = @parse_url($url);
	if (! $parsed_url) return false;
	if (! $parsed_url['scheme']) {
		$fullurl = $parsed_referer['scheme'].'://'.$parsed_referer['host'];
		if ($parsed_referer['port']) $fullurl .= ':'.$parsed_referer['port'];
		if (!preg_match('/^\/+/', $parsed_url['path'])) {
			if (!preg_match('#/$#', $parsed_referer['path'])) {
				$parsed_referer['path'] = dirname($parsed_referer['path']).'/'; // dirname always take the last out!
			}
			$fullurl .= normalize_path($parsed_referer['path'].$parsed_url['path']);
		} else {
			$fullurl .= $parsed_url['path'];
		}
		if ($parsed_url['query']) $fullurl .= '?'.$parsed_url['query'];
		return $fullurl;
	}
	return $url;

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

function path_sub_path($path, $level = -1) {
	$parts = array();
	$dirs = explode('/',  preg_replace('#^/+#', '', $path));
	$count = count($dirs);
	if ($level < 0) $n = $count - $level;
	else  $n = $level;
	for ($i=0; $i<$n && $i<$count; $i++) {
			$parts[] = $dirs[$i];
	}
	return '/' . implode("/", $parts);
}

function path_equals($path1, $path2) {
	// Eliminate large "only numbers" path if they have also "semantic" parts
	$path1 = preg_replace('#([^\d]+/)[/\d]{6,}(/[^\d]{40,})#', '$1$2', $path1);
	$path2 = preg_replace('#([^\d]+/)[/\d]{6,}(/[^\d]{40,})#', '$1$2', $path2);


	$parts1 = explode('/', preg_replace('#^/+|/+$#', '', $path1));
	$parts2 = explode('/', preg_replace('#^/+|/+$#', '', $path2));
	$n = 0;
	$max = min(count($parts1), count($parts2));
	for ($i=0; $i < $max && $parts1[$i] == $parts2[$i]; $i++) $n++;
	return $n;
}

function path_count($path) {
	return count(explode('/', preg_replace('#^/+|/+$#', '', $path)));
}

function get_html_title(&$html) {
	if(preg_match('/<title[^<>]*>([^<>]*)<\/title>/si', $html, $matches))
		return $matches[1];
	return false;
}

function sort_url_distance_items($a, $b) {
	return $a[1] < $b[1];
}

function unify_path_query($path, $query) {
		$path_query = preg_replace('#/index\.\w{2,5}$#', '/', $path); // Don't count indexes
		if (!empty($query)) {
			$query = preg_replace('/(.+?)&.*/', '$1', $query); // Take just first part
			$query = preg_replace('#&|=#', '/', $query);
			$path_query .= "/$query";
		}
		$path_query = preg_replace('#/{2,}#', '/', $path_query);
		return $path_query;
}

?>
