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
	var $sent_date = 0;
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
	var $content_type = '';
	var $ip = '';
	var $html = false;
	var $read = false;
	var $voted = false;
	var $banned = false;
	var $thumb_status = 'unknown';

	function json_votes_info($value=false) {
		$dict = array();
		$dict['id'] = $this->id;
		if ($value) $dict['value'] = $value;
		$dict['votes'] = $this->votes;
		$dict['anonymous'] = $this->anonymous;
		$dict['negatives'] = $this->negatives;
		$dict['karma'] = intval($this->karma);
		return json_encode_single($dict);
	}

	function print_html() {
		echo "Valid: " . $this->valid . "<br>\n";
		echo "Url: " . $this->url . "<br>\n";
		echo "Title: " . $this->url_title . "<br>\n";
		echo "encoding: " . $this->encoding . "<br>\n";
	}

	function check_url($url, $check_local = true, $first_level = false) {
		global $globals, $current_user;
		if(!preg_match('/^http[s]*:/', $url)) return false;
		$url_components = @parse_url($url);
		if (!$url_components) return false;
		if (!preg_match('/[a-z]+/', $url_components['host'])) return false;
		$quoted_domain = preg_quote(get_server_name());
		if($check_local && preg_match("/^$quoted_domain$/", $url_components['host'])) {
			$this->ban = array();
			$this->ban['comment'] = _('el servidor es local');
			syslog(LOG_NOTICE, "Meneame, server name is local name ($current_user->user_login): $url");
			return false;
		}
		require_once(mnminclude.'ban.php');
		if(($this->ban = check_ban($url, 'hostname', false, $first_level))) {
			syslog(LOG_NOTICE, "Meneame, server name is banned ($current_user->user_login): $url");
			$this->banned = true;
			return false;
		}
		return true;
	}

	function get($url, $maxlen = 150000, $check_local = true) {
		global $globals, $current_user;
		$url=trim($url);
		$url_components = @parse_url($url);
		if(version_compare(phpversion(), '5.0.0') >= 0) {
			$opts = array(
				'http' => array('user_agent' => $globals['user_agent'], 'max_redirects' => 10, 'timeout' => 10, 'header' => 'Referer: http://'.get_server_name().$globals['base_url']."\r\n" ),
				'https' => array('user_agent' => $globals['user_agent'], 'max_redirects' => 10, 'timeout' => 10, 'header' => 'Referer: http://'.get_server_name().$globals['base_url']."\r\n" ),
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
					if (preg_match('/^content-type: /i', $response)) {
						$answer = split(' ', $response);
						$this->content_type = preg_replace('/\/.*$/', '', $answer[1]);
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
					if (!$this->check_url($new_url, $check_local, true)) {
						$this->url = $new_url;
						return false;
					}
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
		// Fill content type if empty
		// Right now only check for typical image extensions
		if (empty($this->content_type)) {
			if (preg_match('/(jpg|jpeg|gif|png)(\?|#|$)/i', $this->url)) {
				$this->content_type='image';
			}
		}
		// NO more to do
		if (!$url_ok) return true;

		if(preg_match('/charset=([a-zA-Z0-9-_]+)/i', $this->html, $matches)) {
			$this->encoding=trim($matches[1]);
			if(strcasecmp($this->encoding, 'utf-8') != 0) {
				$this->html=iconv($this->encoding, 'UTF-8//IGNORE', $this->html);
			}
		}


		// Check if the author doesn't want to share
		if (preg_match('/<!-- *noshare *-->/', $this->html)) {
			$this->ban = array();
			$this->ban['comment'] = _('el autor no desea que se envíe el artículo, respeta sus deseos');
			syslog(LOG_NOTICE, "Meneame, noshare ($current_user->user_login): $url");
			return false;
		}

		// Now we analyse the html to find links to banned domains
		// It avoids the trick of using google or technorati
		// Ignore it if the link has a rel="nofollow" to ignore comments in blogs
		if (!preg_match('/content="[^"]*(vBulletin|phpBB)/i', $this->html)) {
			preg_match_all('/(< *meta +http-equiv|< *script|< *iframe|< *frame[^<]*>|< *h[0-9][^<]*>[^<]*<a|window\.|document.\|parent\.|location\.|top\.|self\.)[^>]*(href|url|action|src|location|replace) *[=\(] *[\'"]{0,1}https*:\/\/[^\s "\'>]+[\'"\;\)]{0,1}[^>]*>/i', $this->html, $matches);
		} else {
			preg_match_all('/(< *a|<* meta +http-equiv|<* script|<* iframe|<* frame[^<]*>|window\.|document.\|parent\.|location\.|top\.|self\.)[^>]*(href|url|action|src|location|replace) *[=\(] *[\'"]{0,1}https*:\/\/[^\s "\'>]+[\'"\;\)]{0,1}[^>]*>/i', $this->html, $matches);
		}
		$check_counter = 0;
		$second_level = preg_quote(preg_replace('/^(.+\.)*([^\.]+)\.[^\.]+$/', "$2", $url_components['host']));
		foreach ($matches[0] as $match) {
			if (!preg_match('/<a.+rel=.*nofollow.*>/', $match)) {
				preg_match('/(href|url|action|src|location|replace) *[=\(] *[\'"]{0,1}(https*:\/\/[^\s "\'>]+)[\'"\;\)]{0,1}/i', $match, $url_a);
				$embeded_link  = $url_a[2];
				$new_url_components = @parse_url($embeded_link);
				if (! empty($embeded_link) && $check_counter < 5 && ! $checked_links[$new_url_components['host']]) {
					if (! preg_match("/$second_level\.[^\.]+$/", $new_url_components['host']) ) {
						$check_counter++;
					}
					$checked_links[$new_url_components['host']] = true;
					if (!$this->check_url($embeded_link, false) && $this->banned) return false;
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
		$link_thumb = $db->escape($this->thumb);
		$link_thumb_x = intval($this->thumb_x);
		$link_thumb_y = intval($this->thumb_y);
		$link_thumb_status = $db->escape($this->thumb_status);
		$db->query("UPDATE links set link_url='$link_url', link_uri='$link_uri', link_url_title='$link_url_title', link_title='$link_title', link_content='$link_content', link_tags='$link_tags', link_thumb='$link_thumb', link_thumb_x=$link_thumb_x, link_thumb_y=$link_thumb_y, link_thumb_status='$link_thumb_status' WHERE link_id=$this->id");
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
		$link_sent_date = $this->sent_date;
		$link_published_date = $this->published_date;
		$link_content_type = $db->escape($this->content_type);
		$link_ip = $db->escape($this->ip);
		if($this->id===0) {
			$db->query("INSERT INTO links (link_author, link_blog, link_status, link_randkey, link_category, link_date, link_sent_date, link_published_date, link_votes, link_negatives, link_karma, link_anonymous, link_votes_avg, link_content_type, link_ip) VALUES ($link_author, $link_blog, '$link_status', $link_randkey, $link_category, FROM_UNIXTIME($link_date), FROM_UNIXTIME($link_sent_date), FROM_UNIXTIME($link_published_date), $link_votes, $link_negatives, $link_karma, $link_anonymous, $link_votes_avg, '$link_content_type', '$link_ip')");
			$this->id = $db->insert_id;
		} else {
		// update
			$db->query("UPDATE links set link_author=$link_author, link_blog=$link_blog, link_status='$link_status', link_randkey=$link_randkey, link_category=$link_category, link_date=FROM_UNIXTIME($link_date), link_sent_date=FROM_UNIXTIME($link_sent_date), link_published_date=FROM_UNIXTIME($link_published_date), link_votes=$link_votes, link_negatives=$link_negatives, link_comments=$link_comments, link_karma=$link_karma, link_anonymous=$link_anonymous, link_votes_avg=$link_votes_avg, link_content_type='$link_content_type', link_ip='$link_ip' WHERE link_id=$this->id");
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
		if(($link = $db->get_row("SELECT SQL_CACHE link_id, link_author, link_blog, link_status, link_votes, link_negatives, link_anonymous, link_votes_avg, link_comments, link_karma, link_randkey, link_category, link_uri, link_title, UNIX_TIMESTAMP(link_date) as link_ts,  UNIX_TIMESTAMP(link_sent_date) as sent_ts, UNIX_TIMESTAMP(link_published_date) as published_ts, UNIX_TIMESTAMP(link_modified) as modified_ts, link_content_type, link_ip  FROM links WHERE $cond"))) {
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
			$this->sent_date=$link->sent_ts;
			$this->published_date=$link->published_ts;
			$this->modified=$link->modified_ts;
			$this->ip=$link->link_ip;
			$this->content_type=$link->content_type;
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
		if(($link = $db->get_row("SELECT SQL_CACHE links.*, UNIX_TIMESTAMP(link_date) as link_ts,  UNIX_TIMESTAMP(link_sent_date) as sent_ts, UNIX_TIMESTAMP(link_published_date) as published_ts, UNIX_TIMESTAMP(link_modified) as modified_ts, users.user_login, users.user_email, users.user_avatar, users.user_karma, users.user_level, users.user_adcode FROM links, users WHERE $cond AND user_id=link_author"))) {
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
			$this->sent_date=$link->sent_ts;
			$this->published_date=$link->published_ts;
			$this->modified=$link->modified_ts;
			$this->ip=$link->link_ip;
			$this->content_type=$link->link_content_type;
			$this->thumb_status = $link->link_thumb_status;
			$this->thumb_x = $link->link_thumb_x;
			$this->thumb_y = $link->link_thumb_y;
			$this->thumb = $link->link_thumb;
			if ($this->category > 0) {
				$meta_info = $db->get_row("SELECT SQL_CACHE categories.category_name, categories.category_uri, meta.category_name as meta_name, meta.category_uri as meta_uri, meta.category_id as meta_id FROM categories, categories as meta  WHERE categories.category_id = $this->category AND meta.category_id = categories.category_parent");
				$this->category_name=$meta_info->category_name;
				$this->meta_name=$meta_info->meta_name;
				$this->meta_uri=$meta_info->meta_uri;
				$this->meta_id=$meta_info->meta_id;
			}
			$this->read = true;
			return true;
		}
		$this->read = false;
		return false;
	}

	function duplicates($url) {
		global $db;
		$trimmed = $db->escape(preg_replace('/\/$/', '', $url));
		$list = "'$trimmed', '$trimmed/'";
		if (preg_match('/^http:\/\/www\./', $trimmed)) {
			$link_alternative = preg_replace('/^http:\/\/www\./', 'http://', $trimmed);
		} else {
			$link_alternative = preg_replace('/^http:\/\//', 'http://www.', $trimmed);
		}
		$list .= ", '$link_alternative', '$link_alternative/'";
		// If it was abuse o autodiscarded allow other to send it again
		$found = $db->get_var("SELECT link_id FROM links WHERE link_url in ($list) AND link_status not in ('abuse', 'autodiscard') AND link_votes > 0 ORDER by link_id asc limit 1");
		return $found;
	}

	function print_summary($type='full', $karma_best_comment = 0, $show_tags = true) {
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
			$this->print_shake_box();
		}

		$this->print_warn();

		if ($this->status != 'published') $nofollow = ' rel="nofollow"';
		else $nofollow = '';
		echo '<h1>';
		echo '<a href="'.$url.'"'.$nofollow.'>'. $this->title. '</a>';

		// Content type (for video and images)
		if ($this->content_type == 'image') {
			echo '&nbsp;<img src="'.$globals['base_url'].'img/common/is-photo01.png" class="media-icon" width="18" height="15" alt="'._('imagen').'" title="'._('imagen').'" />';
		} elseif ($this->content_type == 'video') {
			echo '&nbsp;<img src="'.$globals['base_url'].'img/common/is-video01.png" class="media-icon" width="18" height="15" alt="'._('vídeo').'" title="'._('vídeo').'" />';
		}
		echo '</h1>';

		if ($this->has_thumb()) {
			echo "<img src='$this->thumb' width='$this->thumb_x' height='$this->thumb_y' alt='' class='thumbnail'/>";
		}

		if (! $globals['bot']) {
			echo '<div class="news-submitted">';
			if ($type != 'short') {
				echo '<a href="'.get_user_uri($this->username).'"><img src="'.get_avatar_url($this->author, $this->avatar, 25).'" width="25" height="25" alt="" onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$this->author.');" onmouseout="tooltip.clear(event);" /></a>';
			}
			echo '<strong>'.htmlentities(preg_replace('/^https*:\/\//', '', txt_shorter($this->url))).'</strong>'."<br />\n";
			echo _('por').' <a href="'.get_user_uri($this->username, 'history').'">'.$this->username.'</a> ';
			// Print dates
			if ($globals['now'] - $this->date > 604800) { // 7 days
				echo _('el').get_date_time($this->sent_date);
				if($this->status == 'published')
					echo ', '  ._('publicado el').get_date_time($this->date);
			} else {
				echo _('hace').txt_time_diff($this->sent_date);
				if($this->status == 'published')
					echo ', '  ._('publicado hace').txt_time_diff($this->date);
			}
			echo "</div>\n";
		}

		if($type=='full' || $type=='preview') {
			echo '<p>';
			echo text_to_html($this->content);
			if ($type != 'preview' ) {
				if ($this->is_editable()) {
					echo '&nbsp;&nbsp;<a href="'.$globals['base_url'].'editlink.php?id='.$this->id.'&amp;user='.$current_user->user_id.'" title="'._('editar noticia').' #'.$this->id.'"><img class="mini-icon-text" src="'.$globals['base_url'].'img/common/edit-misc01.png" alt="edit"/></a>';
				}
				if ($this->geo && $this->is_map_editable()) {
					echo '&nbsp;&nbsp;<a href="#" onclick="$(\'#geoedit\').load(\''.$globals['base_url']."geo/get_form.php?id=$this->id&amp;type=link&amp;icon=$this->status".'\'); return false;"><img class="mini-icon-text" src="'.$globals['base_url'].'img/common/edit-geo01.png" alt="edit" title="'._('editar geolocalización').'"/></a>';
				}
			}
			echo '</p>';
		}

		// Print a summary of the best comment
		if ($karma_best_comment > 0 && 
			($best_comment = $db->get_row("select SQL_CACHE comment_id, comment_order, comment_content from comments where comment_link_id = $this->id and comment_karma > $karma_best_comment order by comment_karma desc limit 1"))) {
			echo '<div style="font-size: 80%; border: 1px solid; border-color: #dadada; background: #fafafa; margin: 7px 50px 7px 25px; padding: 4px; overflow:hidden">';
			$link = $this->get_permalink().get_comment_page_suffix($globals['comments_page_size'], $best_comment->comment_order, $this->comments).'#comment-'.$best_comment->comment_order;
			echo '<a onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_comment_tooltip.php\', \''.$best_comment->comment_id.'\', 10000);" href="'.$link.'"><strong>'.$best_comment->comment_order.'</strong></a>';
			echo ':&nbsp;'.text_to_summary($best_comment->comment_content, 200).'</div>';
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
		// and tags in sent/voted listing
		if ($globals['link'] || $type == 'short') {
			if ($show_tags && !empty($this->tags)) {
				echo '<div class="news-details">';
				echo '<strong>'._('etiquetas').'</strong>:';
				$tags_array = explode(",", $this->tags);
				$tags_counter = 0;
				foreach ($tags_array as $tag_item) {
					$tag_item=trim($tag_item);
					$tag_url = urlencode($tag_item);
					if ($tags_counter > 0) echo ',';
					if ($globals['base_search_url']) {
						echo ' <a href="'.$globals['base_url'].$globals['base_search_url'].'tag:';
					} else {
						echo ' <a href="'.$globals['base_url'].'search.php?p=tag&amp;q=';
					}
					echo $tag_url.'">'.$tag_item.'</a>';
					$tags_counter++;
				}
				echo '</div>'."\n";
			}
			if ($type != 'short') {
				echo '<div class="news-details">';
				echo '<strong>'._('votos negativos').'</strong>: <span id="a-neg-'.$this->id.'">'.$this->negatives.'</span>&nbsp;&nbsp;';
				echo '<strong>'._('usuarios').'</strong>: <span id="a-usu-'.$this->id.'">'.$this->votes.'</span>&nbsp;&nbsp;';
				echo '<strong>'._('anónimos').'</strong>: <span id="a-ano-'.$this->id.'">'.$this->anonymous.'</span>&nbsp;&nbsp;';
				echo '</div>' . "\n";
			}
		} else {
			echo "<!--tags: $this->tags-->\n";
		}

		echo '</div>'."\n";
		echo '</div>'."\n";

		// Geo edit form div
		if ($this->geo && $this->is_map_editable())  {
			echo '<div id="geoedit" class="geoform" style="margin-left:20px">';
			if ($current_user->user_id == $this->author && $this->sent_date > $globals['now'] - 600 && !$this->latlng)  {
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
			case 'autodiscard': // another color box for discarded
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

		if (! $globals['bot']) {
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
		}
		echo '</div>'."\n";
	}

	function print_warn() {
		global $db, $globals;

		if ($this->status == 'abuse') {
			echo '<div class="warn"><strong>'._('Aviso').'</strong>: ';
			echo _('noticia descartada por violar las').' <a href="'.$globals['legal'].'#tos">'._('normas de uso').'</a>';
			echo "</div>\n";
		} elseif ( $this->votes_enabled  && !$this->is_discarded() &&  $this->negatives > 3 && $this->negatives > $this->votes/10 ) {
			$this->warned = true;
			echo '<div class="warn"><strong>'._('Aviso automático').'</strong>: ';
			if ($this->status == 'published') {
				echo _('noticia errónea o controvertida, por favor lee los comentarios.');
			} elseif ($this->author == $current_user->user_id && $this->is_editable()) {
					echo _('Esta noticia tiene varios votos negativos.').' '._('Tu karma no será afectado si la descartas manualmente.');
			} else {
				// Only says "what" if most votes are "wrong" or "duplicated" 
				$negatives = $db->get_row("select SQL_CACHE vote_value, count(vote_value) as count from votes where vote_type='links' and vote_link_id=$this->id and vote_value < 0 group by vote_value order by count desc limit 1");
				if ($negatives->count > 2 && $negatives->count >= $this->negatives/2 && ($negatives->vote_value == -6 || $negatives->vote_value == -8)) {
					echo _('Esta noticia podría ser <strong>'). get_negative_vote($negatives->vote_value) . '</strong>. ';
				} else {
					echo _('Esta noticia tiene varios votos negativos.');
				}
				if(!$this->voted ) {
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

	function insert_vote($value) {
		global $db, $current_user;
		require_once(mnminclude.'votes.php');

		$vote = new Vote;
		$vote->user=$current_user->user_id;
		$vote->link=$this->id;
		if ($vote->exists()) return false;
		// For karma calculation
		if ($this->status != 'published') {
			if($value < 0 && $current_user->user_id > 0) {
				if ($current_user->user_id != $this->author && 
						($affinity = $this->affinity_get($current_user->user_id)) <  0 ) {
					$karma_value = round(min(-5, $current_user->user_karma *  $affinity/100));
				} else {
					$karma_value = round(-$current_user->user_karma);
				}
			} else {
				if ($current_user->user_id  > 0 && $current_user->user_id != $this->author && 
						($affinity = $this->affinity_get($current_user->user_id)) > 0 ) {
					$karma_value = $value = round(max($current_user->user_karma * $affinity/100, 5));
				} else {
					$karma_value=round($value);
				}
			}
		} else {
			$karma_value = 0;
		}
		$vote->value=$value;
		if($vote->insert()) {
			if ($value < 0) {
				$db->query("update links set link_negatives=link_negatives+1, link_karma=link_karma+$karma_value where link_id = $this->id");
			} else {
				if ($current_user->user_id > 0)  $db->query("update links set link_votes = link_votes+1, link_karma=link_karma+$karma_value where link_id = $this->id");
				else  $db->query("update links set link_anonymous = link_anonymous+1, link_karma=link_karma+$karma_value where link_id = $this->id");
			}
			$new = $db->get_row("select link_votes, link_anonymous, link_negatives, link_karma from links where link_id = $this->id");
			$this->votes = $new->link_votes;
			$this->anonymous = $new->link_anonymous;
			$this->negatives = $new->link_negatives;
			$this->karma = $new->link_karma;
			return $value;
		}
		return false;
	}

	function publish() {
		global $globals;
		if(!$this->read) $this->read_basic();
		$this->published_date = $globals['now'];
		$this->date = $globals['now'];
		$this->status = 'published';
		$this->store_basic();
	}

	function update_comments() {
		global $db;
		$db->query("update links set link_comments = (SELECT count(*) FROM comments WHERE comment_link_id = link_id) where link_id = $this->id");
	}

	function is_discarded() {
		return $this->status == 'discard' || $this->status == 'abuse' ||  $this->status == 'autodiscard';
	}

	function is_editable() {
		global $current_user, $db, $globals;

		if($current_user->user_id) {
			if(($this->author == $current_user->user_id && $this->status != 'published' && $this->status != 'abuse' && $globals['now'] - $this->sent_date < 1800)
			|| ($this->author != $current_user->user_id && $current_user->user_level == 'special' && $globals['now'] - $this->sent_date < 10400)
			|| $current_user->user_level == 'admin' || $current_user->user_level == 'god') {
				return true;
			}
		}
		return false;
	}

	function is_map_editable() {
		global $current_user, $db, $globals;

		if($current_user->user_id ==  0) return false;
		if( ($this->author == $current_user->user_id && $current_user->user_level == 'normal' && $globals['now'] - $this->sent_date < 9800) 
					|| ($current_user->user_level == 'special' && $globals['now'] - $this->sent_date < 14400)
			|| $current_user->user_level == 'admin' || $current_user->user_level == 'god') {
				return true;
			}
		return false;
	}

	function is_votable() {
		global $globals;

		if($globals['bot'] || $this->status == 'abuse' || $this->status == 'autodiscard' ||
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
				$this->status != 'abuse' && $this->status != 'autodiscard' &&
				$current_user->user_karma >= $globals['min_karma_for_negatives'] &&
				($this->status != 'published' || 
				// Allows to vote negative to published with high ratio of negatives
				// or a link recently published
					$this->status == 'published' && ($this->date > $globals['now'] - 3600 || $this->negatives > $this->votes/10) 
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

	function get_status_text($status = false) {
		if (!$status) $status = $this->status;
		switch ($status) {
			case ('abuse'):
				return _('abuso');
			case ('discard'):
				return _('descartada');
			case ('autodiscard'):
				return _('autodescartada');
			case ('queued'):
				return _('pendiente');
			case ('published'):
				return _('publicada');
		}
		return $status;
	}

	function get_latlng() {
		require_once(mnminclude.'geo.php');
		return geo_latlng('link', $this->id);
	}

	function print_content_type_buttons() {
		// Is it an image or video?
		switch ($this->content_type) {
			case 'image':
			case 'video':
			case 'text':
				$type[$this->content_type] = 'checked="checked"';
				break;
			default:
				$type['text'] = 'checked="checked"';
		}
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<input type="radio" '.$type['text'].' name="type" value="text"/>';
		echo '&nbsp;'._('texto').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

		echo '<input type="radio" '.$type['image'].' name="type" value="image"/>';
		echo '&nbsp;<img src="'.$globals['base_url'].'img/common/is-photo02.png" class="media-icon" width="18" height="15" alt="'._('¿es una imagen?').'" title="'._('¿es una imagen?').'" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

		echo '<input type="radio" '.$type['video'].' name="type" value="video"/>';
		echo '&nbsp;<img src="'.$globals['base_url'].'img/common/is-video02.png" class="media-icon" width="18" height="15" alt="'._('¿es un vídeo?').'" title="'._('¿es un vídeo?').'" />';
	}

	function read_content_type_buttons($type) {
		switch ($type) {
			case 'image':
				$this->content_type = 'image';
				break;
			case 'video':
				$this->content_type = 'video';
				break;
			case 'text':
			default:
				$this->content_type = 'text';
		}
	}

	// Read affinity values using annotations

	// $this->author is the key in annotations
	function affinity_get($from = false) {
		global $current_user;

		require_once(mnminclude.'annotation.php');

		$log = new Annotation("affinity-$this->author");
		if (!$log->read()) return false;
		$dict = unserialize($log->text);
		if (!$dict || ! is_array($dict)) return false; // Failed to unserialize
		if (!$from) return $dict; // Asked for the whole dict
		if (abs($dict[$from]) <= 100) return intval($dict[$from]); // Asked just a value;
		return false; // Nothing found
	}

	// Thumbnails management

	function get_thumb() {
		global $globals;
		require_once(mnminclude.'webimages.php');
		require_once(mnminclude.'blog.php');
		$site = false;
		if (empty($this->url)) {
			if (!$this->read()) return false;
		}
		$blog = new Blog();
		$blog->id = $this->blog;
		if ($blog->read()) {
			$site = $blog->url;
		}
		$this->image_parser = new HtmlImages($this->url, $site);
		$img = $this->image_parser->get();
		$this->thumb_status = 'checked';
		$this->thumb = '';
		if ($img) {
			$filepath = mnmpath.'/'.$globals['cache_dir'].'/thumbs';
			@mkdir($filepath);
			$l1 = intval($this->id / 100000);
			$l2 = intval(($this->id % 100000) / 1000);
			$filepath .= "/$l1";
			@mkdir($filepath);
			@chmod($filepath, 0777);
			$filepath .= "/$l2";
			@mkdir($filepath);
			@chmod($filepath, 0777);
			$filepath .= "/$this->id.jpg";
			if ($img->type == 'local') {
				$img->scale(60);
				if($img->save($filepath)) {
					@chmod($filepath, 0777);
					$this->thumb = $globals['base_url'].$globals['cache_dir'].'/thumbs';
					$this->thumb .= "/$l1/$l2/$this->id.jpg";
					$this->thumb_x = $img->x;
					$this->thumb_y = $img->y;
					$this->thumb_status='local';
				} else {
					$this->thumb_status = 'error';
				}
			}
		}
		$this->store_thumb();
		return $this->has_thumb();
	}

	function store_thumb() {
		global $db;
		$this->thumb = $db->escape($this->thumb);
		$db->query("update links set link_thumb = '$this->thumb', link_thumb_x = $this->thumb_x, link_thumb_y = $this->thumb_y, link_thumb_status = '$this->thumb_status' where link_id = $this->id");
	}

	function has_thumb() {
		return $this->thumb && $this->thumb_x > 0 && $this->thumb_y > 0;
	}

}
