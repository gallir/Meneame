<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once(mnminclude.'log.php');
require_once(mnminclude.'favorites.php');

class Link {
	var $id = 0;
	var $author = -1;
	var $blog = 0;
	var $username = false;
	var $randkey = 0;
	var $karma = 0;
	var $valid = false;
	var $date = false;
	var $published_date = 0;
	var $modified = 0;
	var $url = false;
	var $url_title = '';
	var $encoding = false;
	var $status = 'discard';
	var $type = '';
	var $category = 0;
	var $votes = 0;
	var $anonymous = 0;
	var $votes_avg = 0;
	var $negatives = 0;
	var $title = '';
	var $tags = '';
	var $uri = '';
	var $content = '';
	var $html = false;
	var $read = false;
	var $voted = false;
	var $banned = false;

	function print_html() {
		echo "Valid: " . $this->valid . "<br>\n";
		echo "Url: " . $this->url . "<br>\n";
		echo "Title: " . $this->url_title . "<br>\n";
		echo "encoding: " . $this->encoding . "<br>\n";
	}

	function check_url($url, $check_local = true) {
		global $globals, $current_user;
		if(!preg_match('/^http[s]*:/', $url)) return false;
		$url_components = @parse_url($url);
		if (!$url_components) return false;
		if (!preg_match('/[a-z]+/', $url_components['host'])) return false;
		$quoted_domain = preg_quote(get_server_name());
		if($check_local && preg_match("/^$quoted_domain$/", $url_components['host'])) {
			$globals['ban_message'] = _('el servidor es local');
			syslog(LOG_NOTICE, "Meneame, server name is local name ($current_user->user_login): $url");
			return false;
		}
		require_once(mnminclude.'ban.php');
		if(check_ban($url_components[host].$url_components[path], 'hostname', false) || check_ban_list($url_components[host], $globals['forbiden_domains'])) {
			syslog(LOG_NOTICE, "Meneame, server name is banned ($current_user->user_login): $url");
			$this->banned = true;
			return false;
		}
		return true;
	}

	function get($url, $maxlen = 100000, $check_local = true) {
		global $globals, $current_user;
		$url=trim($url);
		$url_components = @parse_url($url);
		if(version_compare(phpversion(), '5.0.0') >= 0) {
			$opts = array(
				'http' => array('user_agent' => 'Mozilla/5.0 (compatible; Meneame; +http://meneame.net/) Gecko/Meneame Firefox', 'max_redirects' => 7, 'timeout' => 10, 'header' => 'Referer: http://'.get_server_name().$globals['base_url']."\r\n" ),
				'https' => array('user_agent' => 'Mozilla/5.0 (compatible; Meneame; +http://meneame.net/) Gecko/Meneame Firefox', 'max_redirects' => 7, 'timeout' => 10, 'header' => 'Referer: http://'.get_server_name().$globals['base_url']."\r\n")
			);
			$context = stream_context_create($opts);
			if(($stream = @fopen($url, 'r', false, $context))) {
				$meta_data = stream_get_meta_data($stream);
				foreach($meta_data['wrapper_data'] as $response) {
					// Check if it has pingbacks
					if (preg_match('/^X-Pingback: /i', $response)) {
						$answer = split(' ', $response);
						if (!empty($answer[1])) {
							$this->pingback = 'ping:'.trim($answer[1]);
						}
					}
					/* Were we redirected? */
					if (preg_match('/^location: /i', $response)) {
						/* update $url with where we were redirected to */
						$answer = split(' ', $response);
						$new_url = clean_input_url($answer[1]);
					}
				}
				if (!empty($new_url) && $new_url != $url) {
					syslog(LOG_NOTICE, "Meneame, redirected ($current_user->user_login): $url -> $new_url");
					/* Check again the url */
					// Warn: relative path can come in "Location:" headers, manage them
					if(!preg_match('/^http[s]*:/', $new_url)) {
						// It's relative
						$new_url = $url . $new_url;
					}
					if (!$this->check_url($new_url, $check_local)) return false;
					// Change the url if we were directed to another host
					if (strlen($new_url) < 250  && ($new_url_components = @parse_url($new_url))) {
						if ($url_components['host'] != $new_url_components['host']) {
							syslog(LOG_NOTICE, "Meneame, changed source URL ($current_user->user_login): $url -> $new_url");
							$url = $new_url;
							$url_components = $new_url_components;
						}
					}
				}
				$url_ok = $this->html = @stream_get_contents($stream, $maxlen);
				fclose($stream);
			} else {
				syslog(LOG_NOTICE, "Meneame, error getting ($current_user->user_login): $url");
				$url_ok = false;
			}
			//$url_ok = $this->html = @file_get_contents($url, false, $context, 0, 200000);
		} else {
			$url_ok = $this->html = @file_get_contents($url);
		}
		$this->url=$url;
		// NO more to do
		if (!$url_ok) return true;

		if(preg_match('/charset=([a-zA-Z0-9-_]+)/i', $this->html, $matches)) {
			$this->encoding=trim($matches[1]);
			if(strcasecmp($this->encoding, 'utf-8') != 0) {
				$this->html=iconv($this->encoding, 'UTF-8//IGNORE', $this->html);
			}
		}

		// Now we analyse the html to find links to banned domains
		// It avoids the trick of using google or technorati
		// Ignore it if the link has a rel="nofollow" to ignore comments in blogs
		if (!empty($this->pingback) || $this->has_rss() || !empty($this->trackback) || $this->trackback()) {
			preg_match_all('/<(meta +http-equiv|script|iframe|frame>)[^>]+(href|url|action|src)=[\'"]{0,1}https*:\/\/[^\s "\'>]+[\'"]{0,1}[^>]*>/i', $this->html, $matches);
		} else {
			preg_match_all('/<(a|meta +http-equiv|script|iframe|frame>)[^>]+(href|url|action|src)=[\'"]{0,1}https*:\/\/[^\s "\'>]+[\'"]{0,1}[^>]*>/i', $this->html, $matches);
		}
		$check_counter = 0;
		foreach ($matches[0] as $match) {
			if (!preg_match('/<a.+rel=.*nofollow.*>/', $match)) {
				preg_match('/(href|url|action|src)=[\'"]{0,1}(https*:\/\/[^\s "\'>]+)[\'"]{0,1}/i', $match, $url_a);
				$embeded_link  = $url_a[2];
				$new_url_components = @parse_url($embeded_link);
				if (! empty($embeded_link) && $new_url_components['host'] != $url_components['host'] && $check_counter < 6) {
					$check_counter++;
					if ($checked_links[$new_url_components['host']] != true) {
						$checked_links[$new_url_components['host']] = true;
						if (!$this->check_url($embeded_link, false) && $this->banned) return false;
					}
				}
			}
		}

		// The URL has been checked
		$this->valid = true;

		if(preg_match('/<title[^<>]*>([^<>]*)<\/title>/si', $this->html, $matches)) {
			$url_title=clean_text($matches[1]);
			if (mb_strlen($url_title) > 3) {
				$this->url_title=$url_title;
			}
		}
		return true;
	}


	function trackback() {
		// Now detect trackbacks
		if (preg_match('/trackback:ping="([^"]+)"/i', $this->html, $matches) ||
			preg_match('/trackback:ping +rdf:resource="([^>]+)"/i', $this->html, $matches) || 
			preg_match('/<trackback:ping>([^<>]+)/i', $this->html, $matches)) {
			$trackback=trim($matches[1]);
		} elseif (preg_match('/<a[^>]+rel="trackback"[^>]*>/i', $this->html, $matches)) {
			if (preg_match('/href="([^"]+)"/i', $matches[0], $matches2)) {
				$trackback=trim($matches2[1]);
			}
		} elseif (preg_match('/<a[^>]+href=[^>#]+>[^>]*trackback[^>]*<\/a>/i', $this->html, $matches)) {
			if (preg_match('/href="([^"]+)"/i', $matches[0], $matches2)) {
				$trackback=trim($matches2[1]);
			}
		}  elseif (preg_match('/(http:\/\/[^\s#]+\/trackback\/*)/i', $this->html, $matches)) {
			$trackback=trim($matches[0]);
		}

		if (!empty($trackback)) {
			$this->trackback = clean_input_url($trackback);
			return true;
		}
		return false;
	}

	function pingback() {
		$url_components = @parse_url($this->url);
		// Now we use previous pingback or detect it
		if ((!empty($url_components['query']) || preg_match('|^/.*[\.-/]+|', $url_components['path']))) {
			if (!empty($this->pingback)) {
				$trackback = $this->pingback;
			} elseif (preg_match('/<link[^>]+rel="pingback"[^>]*>/i', $this->html, $matches)) {
				if (preg_match('/href="([^"]+)"/i', $matches[0], $matches2)) {
					$trackback='ping:'.trim($matches2[1]);
				}
			}
		}
		if (!empty($trackback)) {
			$this->trackback = clean_input_url($trackback);
			return true;
		}
		return false;
	}

	function has_rss() {
		return preg_match('/<link[^>]+(text\/xml|application\/rss\+xml|application\/atom\+xml)[^>]+>/i', $this->html);
	}

	function create_blog_entry() {
		require_once(mnminclude.'blog.php');
		$blog = new Blog();
		$blog->analyze_html($this->url, $this->html);
		if(!$blog->read('key')) {
			$blog->store();
		}
		$this->blog=$blog->id;
		$this->type=$blog->type;
	}

	function type() {
		if (empty($this->type)) {
			if ($this->blog > 0) {
				require_once(mnminclude.'blog.php');
				$blog = new Blog();
				$blog->id = $this->blog;
				if($blog->read()) {
					$this->type=$blog->type;
					return $this->type;
				}
			}
			return 'normal';
		}
		return $this->type;
	}

	function store() {
		global $db, $current_user;

		$this->store_basic();
		$link_url = $db->escape($this->url);
		$link_uri = $db->escape($this->uri);
		$link_url_title = $db->escape($this->url_title);
		$link_title = $db->escape($this->title);
		$link_tags = $db->escape($this->tags);
		$link_content = $db->escape($this->content);
		$db->query("UPDATE links set link_url='$link_url', link_uri='$link_uri', link_url_title='$link_url_title', link_title='$link_title', link_content='$link_content', link_tags='$link_tags' WHERE link_id=$this->id");
	}

	function store_basic() {
		global $db, $current_user, $globals;

		if(!$this->date) $this->date=$globals['now'];
		$link_author = $this->author;
		$link_blog = $this->blog;
		$link_status = $db->escape($this->status);
		$link_votes = $this->votes;
		$link_negatives = $this->negatives;
		$link_anonymous = $this->anonymous;
		$link_comments = $this->comments;
		$link_karma = $this->karma;
		$link_votes_avg = $this->votes_avg;
		$link_randkey = $this->randkey;
		$link_category = $this->category;
		$link_date = $this->date;
		$link_published_date = $this->published_date;
		if($this->id===0) {
			$db->query("INSERT INTO links (link_author, link_blog, link_status, link_randkey, link_category, link_date, link_published_date, link_votes, link_negatives, link_karma, link_anonymous, link_votes_avg) VALUES ($link_author, $link_blog, '$link_status', $link_randkey, $link_category, FROM_UNIXTIME($link_date), FROM_UNIXTIME($link_published_date), $link_votes, $link_negatives, $link_karma, $link_anonymous, $link_votes_avg)");
			$this->id = $db->insert_id;
		} else {
		// update
			$db->query("UPDATE links set link_author=$link_author, link_blog=$link_blog, link_status='$link_status', link_randkey=$link_randkey, link_category=$link_category, link_date=FROM_UNIXTIME($link_date), link_published_date=FROM_UNIXTIME($link_published_date), link_votes=$link_votes, link_negatives=$link_negatives, link_comments=$link_comments, link_karma=$link_karma, link_anonymous=$link_anonymous, link_votes_avg=$link_votes_avg WHERE link_id=$this->id");
		}
		if ($this->votes == 1 && $this->negatives == 0 && $this->status == 'queued') {
			// This is a new link, add it to the events, it an additional control
			// just in case the user dind't do the last submit phase and voted later
			log_conditional_insert('link_new', $this->id, $this->author);
		} 
	}
	
	function read_basic($key='id') {
		global $db, $current_user;
		switch ($key)  {
			case 'id':
				$cond = "link_id = $this->id";
				break;
			case 'uri':
				$cond = "link_uri = '$this->uri'";
				break;
			case 'url':
				$cond = "link_url = '$this->url'";
				break;
			default:
				$cond = "link_id = $this->id";
		}
		if(($link = $db->get_row("SELECT link_id, link_author, link_blog, link_status, link_votes, link_negatives, link_anonymous, link_votes_avg, link_comments, link_karma, link_randkey, link_category, link_uri, link_title, UNIX_TIMESTAMP(link_date) as link_ts, UNIX_TIMESTAMP(link_published_date) as published_ts, UNIX_TIMESTAMP(link_modified) as modified_ts  FROM links WHERE $cond"))) {
			$this->id=$link->link_id;
			$this->author=$link->link_author;
			$this->blog=$link->link_blog;
			$this->status=$link->link_status;
			$this->votes=$link->link_votes;
			$this->negatives=$link->link_negatives;
			$this->anonymous=$link->link_anonymous;
			$this->votes_avg=$link->link_votes_avg;
			$this->comments=$link->link_comments;
			$this->karma=$link->link_karma;
			$this->randkey=$link->link_randkey;
			$this->category=$link->link_category;
			$this->uri= $link->link_uri;
			$this->title=$link->link_title;
			$this->date=$link->link_ts;
			$this->published_date=$link->published_ts;
			$this->modified=$link->modified_ts;
			return true;
		}
		return false;
	}

	function read($key='id') {
		global $db, $current_user;
		switch ($key)  {
			case 'id':
				$cond = "link_id = $this->id";
				break;
			case 'uri':
				$cond = "link_uri = '$this->uri'";
				break;
			case 'url':
				$cond = "link_url = '$this->url'";
				break;
			default:
				$cond = "link_id = $this->id";
		}
		if(($link = $db->get_row("SELECT links.*, UNIX_TIMESTAMP(link_date) as link_ts, UNIX_TIMESTAMP(link_published_date) as published_ts, UNIX_TIMESTAMP(link_modified) as modified_ts, users.user_login, users.user_email, users.user_avatar, users.user_karma, users.user_level, users.user_adcode FROM links, users WHERE $cond AND user_id=link_author"))) {
			$this->id=$link->link_id;
			$this->author=$link->link_author;
			$this->username=$link->user_login;
			$this->user_level=$link->user_level;
			$this->user_karma=$link->user_karma;
			$this->anonymous=$link->link_anonymous;
			$this->votes_avg=$link->link_votes_avg;
			$this->user_adcode=$link->user_adcode;
			$this->avatar=$link->user_avatar;
			$this->email=$link->user_email;
			$this->blog=$link->link_blog;
			$this->status=$link->link_status;
			$this->votes=$link->link_votes;
			$this->negatives=$link->link_negatives;
			$this->comments=$link->link_comments;
			$this->karma=$link->link_karma;
			$this->randkey=$link->link_randkey;
			$this->category=$link->link_category;
			$this->url= $link->link_url;
			$this->uri= $link->link_uri;
			$this->url_title=$link->link_url_title;
			$this->title=$link->link_title;
			$this->tags=$link->link_tags;
			$this->content=$link->link_content;
			$this->date=$link->link_ts;
			$this->published_date=$link->published_ts;
			$this->modified=$link->modified_ts;
			if ($this->category > 0) {
				$meta_info = $db->get_row("SELECT categories.category_name, categories.category_uri, meta.category_name as meta_name, meta.category_uri as meta_uri FROM categories, categories as meta  WHERE categories.category_id = $this->category AND meta.category_id = categories.category_parent");
				$this->category_name=$meta_info->category_name;
				$this->meta_name=$meta_info->meta_name;
				$this->meta_uri=$meta_info->meta_uri;
			}
			$this->read = true;
			return true;
		}
		$this->read = false;
		return false;
	}

	function duplicates($url) {
		global $db;
		if (preg_match('/\/$/', $url)) {
			$link_alternative = preg_replace('/\/$/', '', $url);
		} else {
			$link_alternative = $url . '/';
		}
		$link_url=$db->escape($url);
		$link_alternative=$db->escape($link_alternative);
		$found = $db->get_var("SELECT link_id FROM links WHERE (link_url = '$link_url' OR link_url = '$link_alternative') AND (link_status not in ('discard', 'abuse') OR link_votes>0) limit 1");
		return $found;
	}

	function print_summary($type='full') {
		global $current_user, $current_user, $globals, $db;

		if(!$this->read) return;
		if($this->is_votable()) {
			$this->voted = $this->vote_exists($current_user->user_id);
			if (!$this->voted) $this->md5 = md5($current_user->user_id.$this->id.$this->randkey.$globals['user_ip']);
		}

		$url = htmlspecialchars($this->url);

		echo '<div class="news-summary">';
		echo '<div class="news-body">';
		if ($type != 'preview' && !empty($this->title) && !empty($this->content)) {
			$this->print_shake_box($votes_enabled);
		}

		$this->print_warn();

		if($globals['external_ads']) echo "<!-- google_ad_section_start -->\n";
		
		if ($this->status != 'published') $nofollow = ' rel="nofollow"';
		else $nofollow = '';
		echo '<h1>';
		echo '<a href="'.$url.'"'.$nofollow.'>'. $this->title. '</a>';
		echo '</h1>';

		// GEO
		if ($this->latlng) {
			echo '<div class="thumbnail" id="map" style="width:130px;height:130px">&nbsp;</div>'."\n";
		} elseif ($type=='full' && $globals['do_websnapr'] && $this->votes_enabled && $globals['link_id'] > 0 && !empty($this->url_title)) {
		// Websnapr
		// In order not to overload websnapr, display the image only if votes are enabled
			echo '<img class="news-websnapr" alt="websnapr.com" src="http://images.websnapr.com/?size=T&amp;url='.$url.'" width="92" height="70"  onmouseover="return tooltip.ajax_delayed(event, \'get_link_snap.php\', '.$this->id.');" onmouseout="tooltip.clear(event);" onclick="tooltip.clear(this);"/>';
		}

		echo '<div class="news-submitted">';
		if ($type != 'short') {
			echo '<a href="'.get_user_uri($this->username).'"><img src="'.get_avatar_url($this->author, $this->avatar, 25).'" width="25" height="25" alt="avatar" onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$this->author.');" onmouseout="tooltip.clear(event);" /></a>';
		}
		echo '<strong>'.htmlentities(preg_replace('/^https*:\/\//', '', txt_shorter($this->url))).'</strong>'."<br />\n";
		echo _('por').' <a href="'.get_user_uri($this->username, 'history').'" title="karma:&nbsp;'.$this->user_karma.'">'.$this->username.'</a> ';
		// Print dates
		if ($globals['now'] - $this->date > 604800) { // 7 days
			echo _('el').get_date_time($this->date);
			if($this->status == 'published')
				echo ', '  ._('publicado el').get_date_time($this->published_date);
		} else {
			echo _('hace').txt_time_diff($this->date);
			if($this->status == 'published')
				echo ', '  ._('publicado hace').txt_time_diff($this->published_date);
		}
		echo "</div>\n";

		if($type=='full' || $type=='preview') {
			echo '<p>'.text_to_html($this->content);
			if ($type != 'preview' ) {
				if ($this->is_editable()) {
					echo '&nbsp;&nbsp;<a href="'.$globals['base_url'].'editlink.php?id='.$this->id.'&amp;user='.$current_user->user_id.'" title="'._('editar noticia').' #'.$this->id.'"><img src="'.$globals['base_url'].'img/common/edit-misc01.png" alt="edit"/></a>';
				}
				if ($this->geo && $this->is_map_editable()) {
					echo '&nbsp;&nbsp;<a href="#" onclick="$(\'#geoedit\').load(\''.$globals['base_url']."geo/get_form.php?id=$this->id&amp;type=link&amp;icon=$this->status".'\'); return false;"><img src="'.$globals['base_url'].'img/common/edit-geo01.png" alt="edit" title="'._('editar geolocalización').'"/></a>';
				}
			}
			echo '</p>';
		}

		echo '<div class="news-details">';
		if($this->comments > 0) {
			$comments_mess = $this->comments . ' ' . _('comentarios');
		} else  {
			$comments_mess = _('sin comentarios');
		}
		echo '<span class="comments">&nbsp;<a href="'.$this->get_relative_permalink().'">'.$comments_mess. '</a></span>';

		// If the user is authenticated, show favorite box
		if ($current_user->user_id > 0)  {
			echo '<span class="tool"><a id="fav-'.$this->id.'" href="javascript:get_votes(\'get_favorite.php\',\''.$current_user->user_id.'\',\'fav-'.$this->id.'\',0,\''.$this->id.'\')">'.favorite_teaser($current_user->user_id, $this->id).'</a></span>';
		}

		// Print meta and category
		echo ' <span class="tool">'._('en').': ';
		echo '<a href="'.$globals['base_url'].'?meta='.$this->meta_uri.'" title="'._('meta').'">'.$this->meta_name.'</a>, ';
		echo '<a href="'.$globals['base_url'].'?meta='.$this->meta_uri.'&amp;category='.$this->category.'" title="'._('categoría').'">'.$this->category_name.'</a>';
		echo '</span>';
		echo ' <span class="tool">karma: <span id="a-karma-'.$this->id.'">'.intval($this->karma).'</span></span>';

		if(!$this->voted &&  
				$this->negatives_allowed() && 
				$type != 'preview' &&
				$this->votes_enabled /*&& $this->author != $current_user->user_id*/) {
				$this->print_problem_form();
		}

		echo '</div>'."\n";
		// End news details

		// Displayed only in a story page
		if ($globals['link']) {
			if (!empty($this->tags)) {
				echo '<div class="news-details">';
				echo '<strong>'._('etiquetas').'</strong>:';
				$tags_array = explode(",", $this->tags);
				$tags_counter = 0;
				foreach ($tags_array as $tag_item) {
					$tag_item=trim($tag_item);
					$tag_url = urlencode($tag_item);
					if ($tags_counter > 0) echo ',';
					echo ' <a href="'.$globals['base_url'].'search.php?search=tag:'.$tag_url.'">'.$tag_item.'</a>';
					$tags_counter++;
				}
				echo '</div>'."\n";
			}
			echo '<div class="news-details">';
			echo '<strong>'._('votos negativos').'</strong>: <span id="a-neg-'.$this->id.'">'.$this->negatives.'</span>&nbsp;&nbsp;';
			echo '<strong>'._('usuarios').'</strong>: '.$this->votes.'&nbsp;&nbsp;';
			echo '<strong>'._('anónimos').'</strong>: '.$this->anonymous.'&nbsp;&nbsp;';
			echo '</div>';
		}
		if($globals['external_ads']) echo "<!-- google_ad_section_end -->\n";

		echo '</div>'."\n";
		echo '</div>'."\n";

		// Geo edit form div
		if ($this->geo && $this->is_map_editable())  {
			echo '<div id="geoedit" class="geoform" style="margin-left:20px">';
			if ($current_user->user_id == $this->author && !$this->latlng)  {
				geo_coder_print_form('link', $this->id, $globals['latlng'], _('ubica al origen de la noticia o evento (ciudad, país)'));
			}
			echo '</div>'."\n";
		}

	}
	
	function print_shake_box() {
		global $current_user, $anonnymous_vote, $site_key, $globals;
		
		switch ($this->status) {
			case 'queued': // another color box for not-published
				$box_class = 'mnm-queued';
				break;
			case 'abuse': // another color box for discarded
			case 'discard': // another color box for discarded
				$box_class = 'mnm-discarded';
				break;
			case 'published': // default for published
			default:
				$box_class = 'mnm-published';
				break;
		}
		echo '<div class="news-shakeit">';
		echo '<div class="'.$box_class.'">';
		echo '<a id="a-votes-'.$this->id.'" href="'.$this->get_relative_permalink().'">'.($this->votes+$this->anonymous).'</a>'._('meneos').'</div>';
		echo '<div class="menealo" id="a-va-'.$this->id.'">';

		if ($this->votes_enabled == false) {
			echo '<span>'._('cerrado').'</span>';
		} elseif( !$this->voted) {
			echo '<a href="javascript:menealo('."$current_user->user_id,$this->id,$this->id,"."'".$this->md5."'".')" id="a-shake-'.$this->id.'">'._('menéalo').'</a>';
		} else {
			if ($this->voted > 0) $mess = _('&#161;chachi!');
			else $mess = ':-(';
			echo '<span id="a-shake-'.$this->id.'">'.$mess.'</span>';
		}
		echo '</div>'."\n";
		echo '</div>'."\n";
	}

	function print_warn() {
		global $db;

		if ( $this->status != 'discard' && $this->status != 'abuse' &&  $this->negatives > 3 && $this->negatives > $this->votes/10 ) {
			$this->warned = true;
			echo '<div class="warn"><strong>'._('Aviso automático').'</strong>: ';
			if ($this->status == 'published') {
				echo _('noticia controvertida, por favor lee los comentarios');
			} else {
				// Only says "what" if most votes are "wrong" or "duplicated" 
				$negatives = $db->get_row("select vote_value, count(vote_value) as count from votes where vote_type='links' and vote_link_id=$this->id and vote_value < 0 group by vote_value order by count desc limit 1");
				if ($negatives->count > 2 && $negatives->count >= $this->negatives/2 && ($negatives->vote_value == -6 || $negatives->vote_value == -8)) {
					echo _('Esta noticia podría ser <strong>'). get_negative_vote($negatives->vote_value) . '</strong>. ';
				} else {
					echo _('Esta noticia tiene varios votos negativos.');
				}
				if( $this->votes_enabled && !$this->voted ) {
					echo ' <a href="'.$this->get_relative_permalink().'/voters">' ._('Asegúrate').'</a> ' . _('antes de menear') . '.';
				}
			}
			echo "</div>\n";
		} else {
			$this->warned = false;
		}
	}

	function print_problem_form() {
		global $current_user, $db, $anon_karma, $anonnymous_vote, $globals, $site_key;

		echo '<form  class="tool" action="" id="problem-'.$this->id.'">';
		echo '<select '.$status.' name="ratings"  onchange="';
		echo 'report_problem(this.form,'."$current_user->user_id, $this->id, "."'".$this->md5."'".')';
		echo '">';
		echo '<option value="0" selected="selected">'._('problema').'</option>';
		foreach (array_keys($globals['negative_votes_values']) as $pvalue) {
			echo '<option value="'.$pvalue.'">'.$globals['negative_votes_values'][$pvalue].'</option>';
		}
		echo '</select>';
//		echo '<input type="hidden" name="return" value="" disabled />';
		echo '</form>';
	}

	function vote_exists($user) {
		require_once(mnminclude.'votes.php');
		$vote = new Vote;
		$vote->user=$user;
		$vote->link=$this->id;
		return $vote->exists();	
	}
	
	function votes($user) {
		require_once(mnminclude.'votes.php');

		$vote = new Vote;
		$vote->user=$user;
		$vote->link=$this->id;
		return $vote->count();
	}

	function insert_vote($user, $value) {
		global $db, $current_user;
		require_once(mnminclude.'votes.php');

		$vote = new Vote;
		$vote->user=$user;
		$vote->link=$this->id;
		if ($vote->exists()) return false;
		$vote->value=$value;
		// For karma calculation
		if ($this->status != 'published') {
			if($value < 0 && $user > 0) {
				//$karma_value = round(($value - $current_user->user_karma)/2);
				$karma_value = round(-$current_user->user_karma);
			} else {
				$karma_value=round($value);
			}
		} else {
			$karma_value = 0;
		}
		if($vote->insert()) {
			if ($value < 0) {
				$db->query("update links set link_negatives=link_negatives+1, link_karma=link_karma+$karma_value where link_id = $this->id");
			} else {
				if ($user > 0)  $db->query("update links set link_votes = link_votes+1, link_karma=link_karma+$karma_value where link_id = $this->id");
				else  $db->query("update links set link_anonymous = link_anonymous+1, link_karma=link_karma+$karma_value where link_id = $this->id");
			}
			$new = $db->get_row("select link_votes, link_anonymous, link_negatives, link_karma from links where link_id = $this->id");
			$this->votes = $new->link_votes;
			$this->anonymous = $new->link_anonymous;
			$this->negatives = $new->link_negatives;
			$this->karma = $new->link_karma;
			return true;
		}
		return false;
	}

	function publish() {
		global $globals;
		if(!$this->read) $this->read_basic();
		$this->published_date = $globals['now'];
		$this->status = 'published';
		$this->store_basic();
	}

	function update_comments() {
		global $db;
		$this->comments = $db->get_var("SELECT count(*) FROM comments WHERE comment_link_id = $this->id");
		$db->query("update links set link_comments = $this->comments where link_id = $this->id");
	}

	function is_editable() {
		global $current_user, $db, $globals;

		if($current_user->user_id ==  0) return false;
		if($this->status != 'published' && 
			(($this->author == $current_user->user_id && $current_user->user_level == 'normal' && $globals['now'] - $this->date < 1800) 
					|| ($current_user->user_level == 'special' && $globals['now'] - $this->date < 10400))
			|| $current_user->user_level == 'admin' || $current_user->user_level == 'god') {
				return true;
			}
		return false;
	}

	function is_map_editable() {
		global $current_user, $db, $globals;

		if($current_user->user_id ==  0) return false;
		if( ($this->author == $current_user->user_id && $current_user->user_level == 'normal' && $globals['now'] - $this->date < 9800) 
					|| ($current_user->user_level == 'special' && $globals['now'] - $this->date < 14400)
			|| $current_user->user_level == 'admin' || $current_user->user_level == 'god') {
				return true;
			}
		return false;
	}

	function is_votable() {
		global $globals;

		if($globals['bot'] || $this->status == 'abuse' || 
				($globals['time_enabled_votes'] > 0 && $this->date < $globals['now'] - $globals['time_enabled_votes']))  {
			$this->votes_enabled = false;
		} else {
			$this->votes_enabled = true;
		}
		return $this->votes_enabled;
	}

	function negatives_allowed() {
		global $globals, $current_user;


		return  $current_user->user_id > 0  &&
				$this->votes > 0 &&
				$this->status != 'abuse' &&
				$current_user->user_karma >= $globals['min_karma_for_negatives'] &&
				($this->status != 'published' || 
				// Allows to vote negative to published with high ratio of negatives
				// or a link recently published
					$this->status == 'published' && ($this->published_date > $globals['now'] - 3600 || $this->negatives > $this->votes/10) 
					|| $this->warned);
	}

	function get_uri() {
		global $db, $globals;
		$seq = 0;
		require_once(mnminclude.'uri.php');
		$new_uri = $base_uri = get_uri($this->title);
		while ($db->get_var("select count(*) from links where link_uri='$new_uri' and link_id != $this->id") && $seq < 20) {
			$seq++;
			$new_uri = $base_uri . "-$seq";
		}
		// In case we tried 20 times, we just add the id of the article
		if ($seq >= 20) {
			$new_uri = $base_uri . "-$this->id";
		}
		$this->uri = $new_uri;
	}
	
	function get_relative_permalink() {
		global $globals;
		if (!empty($this->uri) && !empty($globals['base_story_url']) ) {
			return $globals['base_url'] . $globals['base_story_url'] . $this->uri;
		} else {
			return $globals['base_url'] . 'story.php?id=' . $this->id;
		}
	}
	function get_permalink() {
		return 'http://'.get_server_name().$this->get_relative_permalink();
	}

	function get_trackback() {
		global $globals;
		return "http://".get_server_name().$globals['base_url'].'trackback.php?id='.$this->id;
	}

	function get_latlng() {
		require_once(mnminclude.'geo.php');
		return geo_latlng('link', $this->id);
	}

	function lucene_update() {
		global $globals;

		// Lucene needs to define an UTF-8 locale, otherwise fails
		setlocale(LC_CTYPE, "en_US.utf-8");
		require_once(mnminclude.'Zend/Search/Lucene.php');

		if (!$this->id) return;
		if (file_exists(mnmpath.'/'.$globals['cache_dir'].'/lucene/link_index')) {
			$index = Zend_Search_Lucene::open(mnmpath.'/'.$globals['cache_dir'].'/lucene/link_index');
		} else {
			print "Creando dir\n";
			@mkdir(mnmpath.'/'.$globals['cache_dir'].'/lucene');
			@chmod(mnmpath.'/'.$globals['cache_dir'].'/lucene', 0777);
			$index = Zend_Search_Lucene::create(mnmpath.'/'.$globals['cache_dir'].'/lucene/link_index');
			@chmod(mnmpath.'/'.$globals['cache_dir'].'/lucene/link_index', 0777);
		}
		// Retrieving documents with termDocs() method
		$term = new Zend_Search_Lucene_Index_Term($this->id, 'link_id');
		$docIds  = $index->termDocs($term);
		foreach ($docIds as $hit) {
			$index->delete($hit);
		}

		if ($this->votes <= 0 || empty($this->title) || empty($this->content) || $this->status == 'discard' || $this->status == 'abuse' ) return;
		$doc = new Zend_Search_Lucene_Document();
		$doc->addField(Zend_Search_Lucene_Field::Keyword('link_id', $this->id));
		$doc->addField(Zend_Search_Lucene_Field::Keyword('date', $this->date));
		$doc->addField(Zend_Search_Lucene_Field::UnStored('tags', $this->tags));
		$doc->addField(Zend_Search_Lucene_Field::Unstored('title', $this->title));
		$doc->addField(Zend_Search_Lucene_Field::UnStored('content', $this->content));
		$index->addDocument($doc);
	}

}
