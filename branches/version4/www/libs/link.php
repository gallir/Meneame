<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
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
	var $clicks = 0;

	// sql fields to build an object from mysql
	const SQL = " link_id as id, link_author as author, link_blog as blog, link_status as status, link_votes as votes, link_negatives as negatives, link_anonymous as anonymous, link_votes_avg as votes_avg, link_votes + link_anonymous as total_votes, link_comments as comments, link_karma as karma, link_randkey as randkey, link_category as category, link_url as url, link_uri as uri, link_url_title as title, link_title as title, link_tags as tags, link_content as content, UNIX_TIMESTAMP(link_date) as date,  UNIX_TIMESTAMP(link_sent_date) as sent_date, UNIX_TIMESTAMP(link_published_date) as published_date, UNIX_TIMESTAMP(link_modified) as modified, link_content_type as content_type, link_ip as ip, link_thumb_status as thumb_status, link_thumb_x as thumb_x, link_thumb_y as thumb_y, link_thumb as thumb, user_login as username, user_email as email, user_avatar as avatar, user_karma as user_karma, user_level as user_level, user_adcode, cat.category_name as category_name, cat.category_uri as category_uri, meta.category_id as meta_id, meta.category_name as meta_name, meta.category_uri as meta_uri, favorite_link_id as favorite, clicks.counter as clicks, votes.vote_value as voted FROM links
	INNER JOIN users on (user_id = link_author)
	LEFT JOIN (categories as cat, categories as meta) on (cat.category_id = links.link_category AND meta.category_id = cat.category_parent)
	LEFT JOIN votes ON (link_date > @enabled_votes and vote_type='links' and vote_link_id = links.link_id and vote_user_id = @user_id and ( @user_id > 0  OR vote_ip_int = @ip_int ) )
	LEFT JOIN favorites ON (@user_id > 0 and favorite_user_id =  @user_id and favorite_type = 'link' and favorite_link_id = links.link_id)
	LEFT JOIN link_clicks as clicks on (clicks.id = links.link_id) ";

	static function from_db($id, $key = 'id') {
		global $db, $current_user;

		if ($key == 'uri') {
			$id = $db->escape($id);
			$selector = " link_uri = '$id' ";
		} else {
			$id = intval($id);
			$selector = " link_id = $id ";
		}

		if(($object = $db->get_object("SELECT".Link::SQL."WHERE $selector", 'Link'))) {
			$object->read = true;
			return $object;
		}
		return false;
	}

	static function count($status='', $cat='', $force = false) {
		global $db;

		if (!$status) return Link::count('published', $cat, $force) +
							Link::count('queued', $cat, $force) +
							Link::count('discard', $cat, $force) +
							Link::count('abuse', $cat, $force) +
							Link::count('autodiscard', $cat, $force);


		$count = get_count("$status.$cat");
		if ($count === false || $force) {
			if ($cat) $cond = " and link_category in ($cat) ";
			$count = $db->get_var("select count(*) from links where link_status = '$status' $cond");
			set_count("$status.$cat", $count);
		}
		return $count;
	}

	static function duplicates($url) {
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
		$found = $db->get_var("SELECT link_id FROM links WHERE link_url in ($list) AND link_status not in ('abuse') AND link_votes > 0 ORDER by link_id asc limit 1");
		return $found;
	}

	function json_votes_info($value=false) {
		$dict = array();
		$dict['id'] = $this->id;
		if ($value) {
			$dict['value'] = $value;
			if ($value < 0) {
				$dict['vote_description'] = get_negative_vote($value);
			} else {
				$dict['vote_description'] = _('¡chachi!');
			}
		}
		$dict['votes'] = $this->votes;
		$dict['anonymous'] = $this->anonymous;
		$dict['negatives'] = $this->negatives;
		$dict['karma'] = intval($this->karma);
		return json_encode($dict);
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


		if (($response = get_url($url)) ) {
			$this->content_type = preg_replace('#^(\w+).+#', '$1', $response['content_type']);

			// Check if it has pingbacks
			if (preg_match('/X-Pingback: *(.+)/i', $response['header'], $match)) {
				$this->pingback = 'ping:'.clean_input_url($match[1]);
			}

			/* Were we redirected? */
			if ($response['redirect_count'] > 0) {
				/* update $url with where we were redirected to */
				$new_url = clean_input_url($response['location']);
			}
			if (!empty($new_url) && $new_url != $url) {
				syslog(LOG_NOTICE, "Meneame, redirected ($current_user->user_login): $url -> $new_url");
				/* Check again the url */
				if (!$this->check_url($new_url, $check_local, true)) {
					$this->url = $new_url;
					return false;
				}
				// Change the url if we were directed to another host
				if (strlen($new_url) < 300	&& ($new_url_components = @parse_url($new_url))) {
					if ($url_components['host'] != $new_url_components['host']) {
						syslog(LOG_NOTICE, "Meneame, changed source URL ($current_user->user_login): $url -> $new_url");
						$url = $new_url;
						$url_components = $new_url_components;
					}
				}
			}
			$this->html = $response['content'];
			$url_ok = true;
		} else {
			syslog(LOG_NOTICE, "Meneame, error getting ($current_user->user_login): $url");
			$url_ok = false;
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
			preg_match_all('/(< *meta +http-equiv|< *iframe|< *frame[^<]*>|window\.|document.\|parent\.|location\.|top\.|self\.)[^><]*(url|action|src|location|replace) *[=\(] *[\'"]{0,1}https*:\/\/[^\s "\'>]+[\'"\;\)]{0,1}[^>]*>/i', $this->html, $matches);
		} else {
			preg_match_all('/(< *a|<* meta +http-equiv|<* iframe|<* frame[^<]*>|window\.|document.\|parent\.|location\.|top\.|self\.)[^><]*(href|url|action|src|location|replace) *[=\(] *[\'"]{0,1}https*:\/\/[^\s "\'>]+[\'"\;\)]{0,1}[^>]*>/i', $this->html, $matches);
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

		if(preg_match('/< *meta +name=[\'"]description[\'"] +content=[\'"]([^<>]+)[\'"] *\/*>/si', $this->html, $matches)) {
			$url_description=clean_text($matches[1]);
			// Further clean up to eliminate links and tags inside the description
			$url_description = html_entity_decode($url_description, ENT_COMPAT, 'UTF-8');
			$url_description = strip_tags($url_description);
			$url_description = @htmlspecialchars($url_description, ENT_COMPAT, 'UTF-8');
			if (mb_strlen($url_description) > 20) {
				$this->url_description=text_to_summary($url_description, 400);
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
		}  elseif (preg_match('/(http:\/\/[^\s#]+\/trackback[\/\w]*)/i', $this->html, $matches)) {
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

	function enqueue() {
		global $db, $globals, $current_user;
		// Check this one was not already queued
		if($this->votes == 0 && $this->author == $current_user->user_id && $this->status != 'queued') {
			$db->transaction();
			$this->status='queued';
			$this->sent_date = $this->date=time();
			$this->get_uri();
			$this->store();
			$this->insert_vote($current_user->user_karma);
			$db->commit();

			// Add the new link log/event
			require_once(mnminclude.'log.php');
			log_conditional_insert('link_new', $this->id, $this->author);

			$db->query("delete from links where link_author = $this->author and link_date > date_sub(now(), interval 30 minute) and link_status='discard' and link_votes=0");
			if(!empty($_POST['trackback'])) {
				$trackres = new Trackback;
				$trackres->url=clean_input_url($_POST['trackback']);
				$trackres->link_id=$this->id;
				$trackres->link=$this->url;
				$trackres->author=$this->author;
				$trackres->status = 'pendent';
				$trackres->store();
			}
			fork("backend/send_pingbacks.php?id=$this->id");
		}
	}


	function has_rss() {
		return preg_match('/<link[^>]+(text\/xml|application\/rss\+xml|application\/atom\+xml)[^>]+>/i', $this->html);
	}

	function create_blog_entry() {
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
		global $db, $current_user, $globals;

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
		$db->transaction();
		$this->store_basic();
		$db->query("UPDATE links set link_url='$link_url', link_uri='$link_uri', link_url_title='$link_url_title', link_title='$link_title', link_content='$link_content', link_tags='$link_tags', link_thumb='$link_thumb', link_thumb_x=$link_thumb_x, link_thumb_y=$link_thumb_y, link_thumb_status='$link_thumb_status' WHERE link_id=$this->id");
		$db->commit();
	}

	function store_basic() {
		global $db, $current_user, $globals;

		if(!$this->date) $this->date=$globals['now'];
		$link_author = $this->author;
		$link_blog = $this->blog;
		$link_status = $db->escape($this->status);
		$link_anonymous = $this->anonymous;
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
			$db->query("INSERT INTO links (link_author, link_blog, link_status, link_randkey, link_category, link_date, link_sent_date, link_published_date, link_karma, link_anonymous, link_votes_avg, link_content_type, link_ip) VALUES ($link_author, $link_blog, '$link_status', $link_randkey, $link_category, FROM_UNIXTIME($link_date), FROM_UNIXTIME($link_sent_date), FROM_UNIXTIME($link_published_date), $link_karma, $link_anonymous, $link_votes_avg, '$link_content_type', '$link_ip')");
			$this->id = $db->insert_id;
		} else {
		// update
			$db->query("UPDATE links set link_author=$link_author, link_blog=$link_blog, link_status='$link_status', link_randkey=$link_randkey, link_category=$link_category, link_date=FROM_UNIXTIME($link_date), link_sent_date=FROM_UNIXTIME($link_sent_date), link_published_date=FROM_UNIXTIME($link_published_date), link_karma=$link_karma, link_votes_avg=$link_votes_avg, link_content_type='$link_content_type', link_ip='$link_ip' WHERE link_id=$this->id");
		}
		if ($this->votes == 1 && $this->negatives == 0 && $this->status == 'queued') {
			// This is a new link, add it to the events, it an additional control
			// just in case the user dind't do the last submit phase and voted later
			log_conditional_insert('link_new', $this->id, $this->author);
		}

		$this->update_votes();
		$this->update_comments();
	}

	function update_votes() {
		global $db, $globals;

		if ($this->date < time() - ($globals['time_enabled_votes'] + 3600)) return; // ALERT: Do not modify if votes are already closed

		$db->query("update links set link_votes=(select count(*) from votes where vote_type='links' and vote_link_id=$this->id and vote_user_id > 0 and vote_value > 0), link_anonymous = (select count(*) from votes where vote_type='links' and vote_link_id=$this->id and vote_user_id = 0 and vote_value > 0), link_negatives = (select count(*) from votes where vote_type='links' and vote_link_id=$this->id and vote_user_id > 0 and vote_value < 0) where link_id = $this->id");
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
		if(($result = $db->get_row("SELECT link_id as id, link_author as author, link_blog as blog, link_status as status, link_votes as votes, link_negatives as negatives, link_anonymous as anonymous, link_votes_avg as votes_avg, link_votes + link_anonymous as total_votes, link_comments as comments, link_karma as karma, link_randkey as randkey, link_category as category, link_uri as uri, link_title as title, UNIX_TIMESTAMP(link_date) as date,  UNIX_TIMESTAMP(link_sent_date) as sent_date, UNIX_TIMESTAMP(link_published_date) as published_date, UNIX_TIMESTAMP(link_modified) as modified, link_content_type as content_type, link_ip as ip FROM links WHERE $cond"))) {
			foreach(get_object_vars($result) as $var => $value) $this->$var = $value;
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
		if(($result = $db->get_row("SELECT".Link::SQL."WHERE $cond"))) {
			foreach(get_object_vars($result) as $var => $value) $this->$var = $value;
			$this->read = true;
			return true;
		}
		$this->read = false;
		return false;
	}

	function print_summary($type='full', $karma_best_comment = 0, $show_tags = true) {
		global $current_user, $current_user, $globals, $db;

		if(!$this->read) return;


		$this->is_votable();

		$this->show_tags = $show_tags;
		$this->permalink	 = $this->get_permalink();
		$this->show_shakebox = $type != 'preview' && $this->votes > 0;
		$this->has_warning	 = !(!$this->check_warn() || $this->is_discarded());
		$this->is_editable	= $this->is_editable();
		$this->url_str	   = htmlentities(txt_shorter(preg_replace('/^https*:\/\//', '', $this->url), 60));
		$this->username_str = ' <a href="'.get_user_uri($this->username, 'history').'">'.$this->username.'</a> ';
		$this->print_date	= $globals['now'] - $this->date > 604800 || empty($_SERVER['HTTP_USER_AGENT']); // 7 days or user agent is empty
		$this->thumb_url	= $this->has_thumb();
		$this->map_editable = $this->geo && $this->is_map_editable();
		$this->can_vote_negative = !$this->voted && $this->votes_enabled &&
				$this->negatives_allowed($globals['link_id'] > 0) &&
				$type != 'preview';


		if ($this->status == 'abuse' || $this->has_warning) {
			$this->negative_text = FALSE;
			$negatives = $db->get_row("select SQL_CACHE vote_value, count(vote_value) as count from votes where vote_type='links' and vote_link_id=$this->id and vote_value < 0 group by vote_value order by count desc limit 1");

			if ($negatives->count > 2 && $negatives->count >= $this->negatives/2 && ($negatives->vote_value == -6 || $negatives->vote_value == -8)) {
				$this->negative_text = get_negative_vote($negatives->vote_value);
			}
		}

		if ($karma_best_comment > 0 && $this->comments > 0 && $this->comments < 50 && $globals['now'] - $this->date < 86400) {
			$this->best_comment = $db->get_row("select SQL_CACHE comment_id, comment_order, substr(comment_content, 1, 225) as content from comments where comment_link_id = $this->id and comment_karma > $karma_best_comment and comment_votes > 0 order by comment_karma desc limit 1");
		} else {
			$this->best_comment  = FALSE;
		}

		if ($this->geo && $this->map_editable && $current_user->user_id == $this->author && $this->sent_date > $globals['now'] - 600 && !$this->latlng)  {
			$this->add_geo = TRUE;
		} else {
			$this->add_geo = FALSE;
		}

		$this->get_box_class();

		if ($this->do_inline_friend_votes)
			$this->friend_votes = $db->get_results("SELECT vote_user_id as user_id, vote_value, user_avatar, user_login, UNIX_TIMESTAMP(vote_date) as ts,inet_ntoa(vote_ip_int) as ip FROM votes, users, friends WHERE vote_type='links' and vote_link_id=$this->id AND vote_user_id=friend_to AND vote_user_id > 0 AND user_id = vote_user_id AND friend_type = 'manual' AND friend_from = $current_user->user_id AND friend_value > 0 AND vote_value > 0 AND vote_user_id != $this->author ORDER BY vote_date DESC");

		$vars = compact('type');
		$vars['self'] = $this;
		return Haanga::Load("link_summary.html", $vars);

	}

	function get_box_class() {
		switch ($this->status) {
			case 'queued': // another color box for not-published
				$this->box_class = 'mnm-queued';
				break;
			case 'abuse': // another color box for discarded
			case 'autodiscard': // another color box for discarded
			case 'discard': // another color box for discarded
				$this->box_class = 'mnm-discarded';
				break;
			case 'published': // default for published
			default:
				$this->box_class = 'mnm-published';
				break;
		}
	}

	function check_warn() {
		global $db, $globals;

		if ($this->status == 'published') $neg_percent = 0.1;
		else $neg_percent = 0.1;
		if (!$this->votes_enabled || $this->negatives < 4 || $this->negatives < $this->votes * $neg_percent ) {
			$this->warned = false;
			return $this->warned;
		}
		// Dont do further analisys for published or discarded links
		if ($this->status == 'published' || $this->is_discarded() || $globals['now'] - $this->date > 86400*3) {
			$this->warned = true;
			return $this->warned;
		}
		// Check positive and negative karmas
		$pos = $db->get_row("select sum(vote_value) as karma, avg(vote_value) as avg from votes where vote_type = 'links' and vote_link_id = $this->id and vote_value > 0 and vote_user_id > 0");
		$neg = $db->get_row("select sum(user_karma) as karma, avg(user_karma) as avg from votes, users where vote_type = 'links' and vote_link_id = $this->id and vote_value < 0 and user_id = vote_user_id and user_level not in ('autodisabled','disabled')");
		$karma_neg_corrected = $neg->karma * $neg->avg/$pos->avg; // Adjust to averages for each type
		//echo "Pos: $pos->karma avg: $pos->avg Neg: $neg->karma avg: $neg->avg Corrected: $karma_neg_corrected<br/>\n";
		if ($karma_neg_corrected < $pos->karma*$neg_percent) {
			$this->warned = false;
			return $this->warned;
		}
		$this->warned = true;
		return $this->warned;
	}

	function vote_exists($user) {
		$vote = new Vote('links', $this->id, $user);
		return $vote->exists(false);
	}

	function votes($user) {
		$vote = new Vote('links', $this->id, $user);
		return $vote->count();
	}

	function insert_vote($value) {
		global $db, $current_user, $globals;

		$vote = new Vote('links', $this->id, $current_user->user_id);
		if ($vote->exists(true)) return false;
		// For karma calculation
		if ($this->status != 'published') {
			if($value < 0 && $current_user->user_id > 0) {
				if ($current_user->user_id != $this->author &&
						($affinity = User::get_affinity($this->author, $current_user->user_id)) <  0 ) {
					$karma_value = round(min(-5, $current_user->user_karma *  $affinity/100));
				} else {
					$karma_value = round(-$current_user->user_karma);
				}
			} else {
				if ($current_user->user_id  > 0 && $current_user->user_id != $this->author &&
						($affinity = User::get_affinity($this->author, $current_user->user_id)) > 0 ) {
					$karma_value = $value = round(max($current_user->user_karma * $affinity/100, 5));
				} else {
					$karma_value=round($value);
				}
			}
		} else {
			$karma_value = 0;
		}
		$vote->value=$value;
		$db->transaction();
		if($vote->insert()) {
			// For published links we update counter fields
			if ($this->status == 'published') {
				if ($vote->user > 0) {
					if ($value > 0) {
						$db->query("update links set link_votes=link_votes+1 where link_id = $this->id");
					} else {
						$db->query("update links set link_negatives=link_negatives+1 where link_id = $this->id");
					}
				} else {
					$db->query("update links set link_anonymous=link_anonymous+1 where link_id = $this->id");
				}
			} else {
				// If not published we update karma and count all votes
				$db->query("update links set link_karma=link_karma+$karma_value where link_id = $this->id");
				$this->update_votes();
			}
			$db->commit();
			$this->read_basic();
		} else {
			$db->commit();
			$value = false;
		}
		return $value;
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
		$this->comments = $db->get_var("select link_comments from links where link_id = $this->id");
	}

	function is_discarded() {
		return $this->status == 'discard' || $this->status == 'abuse' ||  $this->status == 'autodiscard';
	}

	function is_editable() {
		global $current_user, $db, $globals;

		if($current_user->user_id) {
			if(($this->author == $current_user->user_id
					&& ($this->status == 'queued' || ($this->status == 'discard' && $this->votes == 0) )
					&& $globals['now'] - $this->sent_date < 1800)
			|| ($this->author != $current_user->user_id
					&& $current_user->special
					&& $this->status == 'queued'
					&& $globals['now'] - $this->sent_date < 10400)
			|| ($this->author != $current_user->user_id
					&& $current_user->user_level == 'blogger'
					&& $globals['now'] - $this->date < 3600)
			|| $current_user->admin) {
				return true;
			}
		}
		return false;
	}

	function is_map_editable() {
		global $current_user, $db, $globals;

		if(! $globals['google_maps_in_links'] || ! $current_user->user_id || $this->votes < 1) return false;
		if( ($this->author == $current_user->user_id
				&& $current_user->user_level == 'normal'
				&& $globals['now'] - $this->sent_date < 9800)
			|| ($current_user->special
				&& $globals['now'] - $this->sent_date < 14400)
			|| $current_user->admin) {
				return true;
			}
		return false;
	}

	function is_votable() {
		global $globals;

		if($globals['bot'] || $this->status == 'abuse' || $this->status == 'autodiscard' ||
				// Close the votes after x hours if the user disabled her account
				($this->user_level == 'autodisabled' && $this->date < $globals['now'] - 3600*6) ||
				($globals['time_enabled_votes'] > 0 && $this->date < $globals['now'] - $globals['time_enabled_votes']))  {
			$this->votes_enabled = false;
		} else {
			$this->votes_enabled = true;
		}
		return $this->votes_enabled;
	}

	function negatives_allowed($extended = false) {
		global $globals, $current_user;


		if ($extended) {
			$period = $globals['time_enabled_votes'];
		} else {
			$period = $globals['time_enabled_negative_votes'];
		}
		return	$current_user->user_id > 0	&&
				$this->votes > 0 &&
				$this->status != 'abuse' && $this->status != 'autodiscard' &&
				$current_user->user_karma >= $globals['min_karma_for_negatives'] &&
				($this->status != 'published' ||
				// Allows to vote negative to published with high ratio of negatives
				// or a link recently published
					$this->status == 'published' && ($this->date > $globals['now'] - $period || $this->negatives > $this->votes/10)
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

	function get_short_permalink() {
		global $globals;

		if ($globals['url_shortener']) {
			$server_name = $globals['url_shortener'] . '/';
			$id = base_convert($this->id, 10, 36);
		} else {
			$server_name = get_server_name().$globals['base_url'].$globals['base_story_url'].'0';
			$id = $this->id;
		}
		if ($globals['base_story_url']) {
			return 'http://'.$server_name.$id;
		} else {
			return $this->get_permalink();
		}
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
		echo '&nbsp;<img src="'.$globals['base_static'].'img/common/is-photo02.png" class="media-icon" width="18" height="15" alt="'._('¿es una imagen?').'" title="'._('¿es una imagen?').'" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

		echo '<input type="radio" '.$type['video'].' name="type" value="video"/>';
		echo '&nbsp;<img src="'.$globals['base_static'].'img/common/is-video02.png" class="media-icon" width="18" height="15" alt="'._('¿es un vídeo?').'" title="'._('¿es un vídeo?').'" />';
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

	// Calculate real karma of the link
	function calculate_karma() {
		global $db, $globals;

		require_once(mnminclude.'log.php');
		require_once(mnminclude.'ban.php');

		$this->old_karma = round($this->karma);
		if (! $globals['users_karma_avg'] ) {
			$globals['users_karma_avg'] = (float) $db->get_var("select SQL_NO_CACHE avg(link_votes_avg) from links where link_status = 'published' and link_date > date_sub(now(), interval 72 hour)");
		}
		$this->annotation = '';
		// Read the stored affinity for the author
		$affinity = User::get_affinity($this->author);

		// high =~ users with higher karma greater than average
		// low =~ users with higher karma less-equal than average
		$votes_pos = $votes_neg = $karma_pos_user_high = $karma_pos_user_low = $karma_neg_user = 0;

		$db->transaction();
		$votes_pos_anon = intval($db->get_var("select SQL_NO_CACHE count(*) from votes where vote_type='links' AND vote_link_id=$this->id and vote_user_id = 0 and vote_value > 0"));

		$votes = $db->get_results("select SQL_NO_CACHE user_id, vote_value, user_karma from votes, users where vote_type='links' AND vote_link_id=$this->id and vote_user_id > 0 and vote_user_id = user_id and user_level !='disabled'");
		$n = $vlow = $vhigh = 0;
		foreach ($votes as $vote) {
			if ($vote->vote_value > 0) {
				$votes_pos++;
				if ($affinity && $affinity[$vote->user_id] > 0) {
					$n++;
					// Change vote_value if there is affinity
					//echo "$vote->vote_value -> ";
					$vote->vote_value = max($vote->user_karma * $affinity[$vote->user_id]/100, 6);
					//echo "$vote->vote_value ($this->author -> $vote->user_id)\n";
				}
				if ($vote->vote_value >=  $globals['users_karma_avg']) {
					$karma_pos_user_high += $vote->vote_value;
					$vhigh++;
				} else {
					$karma_pos_user_low += $vote->vote_value;
					$vlow++;
				}
			} else {
				$votes_neg++;
				if ($affinity && $affinity[$vote->user_id] < 0) {
					$karma_neg_user += min(-6, $vote->user_karma *	$affinity[$vote->user_id]/100);
					//echo "Negativo: " .  min(-5, $vote->user_karma *	$affinity[$vote->user_id]/100) . "$vote->user_id\n";
				} else {
					$karma_neg_user -= $vote->user_karma;
				}
			}
		}
		if ($n > $votes_pos/5) {
			$perc = intval($n/$votes_pos * 100);
			$this->annotation .= $perc. _('% de votos con afinidad elevada'). "<br/>";
		}
		$karma_pos_ano = intval($db->get_var("select SQL_NO_CACHE sum(vote_value) from votes where vote_type='links' AND vote_link_id=$this->id and vote_user_id = 0 and vote_value > 0"));
		$db->commit();

		if ($this->votes != $votes_pos || $this->anonymous != $votes_pos_anon || $this->negatives != $votes_neg) {
			$this->votes = $votes_pos;
			$this->anonymous = $votes_pos_anon;
			$this->negatives = $votes_neg;
		}

		// Make sure we don't deviate too much from the average (it avoids vote spams and abuses)
		if ($karma_pos_user_low/$karma_pos_user_high > 1.15) {
			$perc = intval($vlow/($vlow+$vhigh) * 100);
			$this->annotation .= $perc._('% de votos con karma menores que la media')." (".round($globals['users_karma_avg'],2).")<br/>";
		}
		$karma_pos_user = (int) $karma_pos_user_high + (int) min(max($karma_pos_user_high * 1.1, 4), $karma_pos_user_low); // Allowed difference up to 10% of $karma_pos_user_high
		$karma_pos_ano = min($karma_pos_user_high*0.1, $karma_pos_ano);

		// Small quadratic punishment for links having too many negatives
		if ($karma_pos_user+$karma_pos_ano > abs($karma_neg_user) && abs($karma_neg_user)/$karma_pos_user > 0.075) {
			$r = min(max(0,abs($karma_neg_user)*2/$karma_pos_user), 0.5);
			$karma_neg_user = max(-($karma_pos_user+$karma_pos_ano), $karma_neg_user * pow((1+$r), 2));
		}

		// Get met categories coefficientes that will be used below
		$meta_coef = $this->metas_coef_get();

		// BONUS
		// Give more karma to news voted very fast during the first two hours (ish)
		if (abs($karma_neg_user)/$karma_pos_user < 0.05
			&& $globals['now'] - $this->sent_date < 7200
			&& $globals['now'] - $this->sent_date > 600) {
			$this->coef = $globals['bonus_coef'] - ($globals['now']-$this->sent_date)/7200;
			// It applies the same meta coefficient to the bonus'
			// Check 1 <= bonus <= $bonus_coef
			$this->coef = max(min($this->coef, $globals['bonus_coef']), 1);
			// if it's has bonus and therefore time-related, use the base min_karma
		} elseif ($karma_pos_user+$karma_pos_ano > abs($karma_neg_user)) {
			// Aged karma
			if ($globals['news_meta'] > 0 && $this->meta_id != $globals['news_meta']) {
				$plain_hours = $globals['karma_start_decay'];
				$max_hours = $globals['karma_decay'];
			} else {
				$plain_hours = $globals['karma_news_start_decay'];
				$max_hours = $globals['karma_news_decay'];
			}
			$d = 3600*$max_hours*(1+$globals['min_decay']);
			$diff = max(0, $globals['now'] - ($this->sent_date + $plain_hours*3600));
			$c = 1 - $diff/$d;
			$c = max($globals['min_decay'], $c);
			$c = min(1, $c);
			$this->coef = $c;
		} else {
			$this->coef = 1;
		}
		if ($this->coef < .99) {
			$this->annotation .= _('Noticia «antigua»'). "<br/>";
		} elseif ($this->coef > 1.01) {
			$this->annotation .= _('Bonus por noticia reciente'). "<br/>";
		}

		// Give the "new source" only if if has less than %5 of negative karma
		if (abs($karma_neg_user)/$karma_pos_user < 0.05) {
			$c = $this->calculate_source_bonus();
			if ($c > 1) {
				$this->coef = min($globals['bonus_coef'], $this->coef*$c);
				$c = round($c, 2);
				$this->annotation .= _('Bonus por fuente esporádica'). " ($c)<br/>";
			}
		}

		$this->karma = ($karma_pos_user+$karma_pos_ano+$karma_neg_user)*$this->coef;
		if ($meta_coef && $meta_coef[$this->meta_id]) {
			$this->karma *= $meta_coef[$this->meta_id];
			// Annotate meta's coeeficient if the variation > 5%
			if (abs(1 - $meta_coef[$this->meta_id]) > 0.05) {
				$this->annotation .= _('Coeficiente categoría').': '.round($meta_coef[$this->meta_id], 2)."<br/>";
			}
		}

		// Give a small bonus (= $w) to links according to their clicks
		if ($globals['click_counter'] && $this->id >= $globals['click_counter']
			&& $globals['karma_clicks_bonus'] > 0
			&& $this->negatives < $this->votes/5) {
			$w = $globals['karma_clicks_bonus'];
			$this->clicks = $this->get_clicks(); // Just in case it was not read
			$c = $w * log10($this->clicks/($this->total_votes+$this->negatives));
			$c = min($w*1.5, $c); $c = max($c, 0);
			$this->karma = $this->karma * (1+$c);
			$this->karma = round($this->karma);
		}
	}

	// Bonus for sources than are not frequently sent
	function calculate_source_bonus() {
		global $db, $globals;
		$hours = $db->get_var("select ($this->date - unix_timestamp(link_date))/3600 from links where link_blog=$this->blog and link_id < $this->id order by link_id desc limit 1");
		if (!isset($hours) || $hours > $globals['new_source_max_hours']) $hours = $globals['new_source_max_hours'];
		if ($hours >= 24) {
			return 1 + ($globals['new_source_bonus'] - 1) * ($hours - $globals['new_source_min_hours']) / ($globals['new_source_max_hours'] - $globals['new_source_min_hours']);
		}
		return 0;
	}

	function save_annotation($key) {
		global $globals;

		$key .= "-$this->id";
		$log = new Annotation($key);
		if ($log->read()) $array = unserialize($log->text);
		if (!$array || ! is_array($array)) $array = array();
		$dict = array();
		$dict['time'] = time();
		$dict['positives'] = $this->votes;
		$dict['negatives'] = $this->negatives;
		$dict['anonymous'] = $this->anonymous;
		$dict['old_karma'] = $this->old_karma;
		$dict['karma'] = $this->karma;
		$dict['coef'] = sprintf("%.2f",$this->coef);
		$dict['annotation'] = $this->annotation;
		array_unshift($array, $dict);
		$log->text = serialize($array);
		$log->store();
		$this->annotation = '';
	}

	function read_annotation($key) {
		global $globals;

		$key .= "-$this->id";
		$log = new Annotation($key);
		if ($log->read()) $array = unserialize($log->text);
		if (!$array || ! is_array($array)) return array(); // Return an array, always, for the template
		return $array;
	}

	// Read affinity values using annotations
	function metas_coef_get() {
		$log = new Annotation("metas-coef");
		if (!$log->read()) return false;
		$dict = unserialize($log->text);
		if (!$dict || ! is_array($dict)) return false; // Failed to unserialize
		return $dict; // Asked for the whole dict
	}

	// Thumbnails management

	function get_thumb($debug = false) {
		global $globals;

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
		$this->image_parser->debug = $debug;
		$this->image_parser->referer = $this->get_permalink();
		echo "<!-- Meneame, before image_parser -->\n";
		$img = $this->image_parser->get();
		echo "<!-- Meneame, after image_parser -->\n";
		$this->thumb_status = 'checked';
		$this->thumb = '';
		if ($img) {
			Upload::create_cache_dir($this->id);
			$filepath = Upload::get_cache_dir($this->id) . "/thumb-$this->id.jpg";
			if ($img->type == 'local') {
				$img->scale($globals['thumb_size']);
				if($img->save($filepath)) {
					@chmod($filepath, 0777);
					$this->thumb_x = $img->x;
					$this->thumb_y = $img->y;
					// Upload to S3
					if ($globals['Amazon_S3_media_bucket'] && $globals['Amazon_S3_media_url'] && Media::put($filepath, 'thumbs', "$this->id.jpg")) {
							//$this->thumb = $globals['Amazon_S3_media_url'] . "/thumbs/$this->id.jpg";
							$this->thumb_status = 'remote';
					} else {
						//$this->thumb = $globals['base_url'].$globals['cache_dir'].'/thumbs';
						//$this->thumb .= "/$chain/$this->id.jpg";
						$this->thumb_status='local';
					}
					syslog(LOG_NOTICE, "Meneame, new thumbnail $img->url to " . $this->get_permalink());
					if ($debug)
						echo "<!-- Meneame, new thumbnail $img->url -->\n";
				} else {
					$this->thumb_status = 'error';
					if ($debug)
						echo "<!-- Meneame, error saving thumbnail ".$this->get_permalink()." -->\n";
				}
			}
			if ($img->video) $this->content_type = 'video';
		} elseif ($this->thumb_x || $this->thumb_y) {
			$this->delete_thumb();
			return false;
		}
		$this->store_thumb();
		return $this->has_thumb();
	}

	function store_thumb() {
		global $db;
		$this->thumb = $db->escape($this->thumb);
		$db->query("update links set link_content_type = '$this->content_type', link_thumb = '$this->thumb', link_thumb_x = $this->thumb_x, link_thumb_y = $this->thumb_y, link_thumb_status = '$this->thumb_status' where link_id = $this->id");
	}

	function delete_thumb() {
		global $globals;
		$this->thumb = '';
		$this->thumb_status = 'deleted';
		$this->thumb_x = 0;
		$this->thumb_y = 0;
		$this->store_thumb();
		if ($globals['Amazon_S3_media_bucket'] && $globals['Amazon_S3_media_url']) {
			Media::rm("thumbs/$this->id*");
		}
	}

	function has_thumb() {
		global $globals;
		if ($this->thumb_x > 0 && $this->thumb_y > 0) {
			if (!$globals['Amazon_S3_local_cache'] && $globals['Amazon_S3_media_url']) {
				return $globals['Amazon_S3_media_url']."/thumbs/$this->id.jpg";
			}

			$file = Upload::get_cache_relative_dir($this->id) . "/thumb-$this->id.jpg";
			$filepath = mnmpath."/$file";
			if (is_readable($filepath)) {
				return $globals['base_static'] . $file;
			} elseif ($globals['Amazon_S3_media_bucket'] && $globals['Amazon_S3_local_cache']) {
				Upload::create_cache_dir($this->id);
				// Get thumbnail from S3
				if (Media::get("$this->id.jpg", 'thumbs', $filepath)) {
					return $globals['base_static'] . $file;
				} else {
					// Do extra check, if S3 is working, mark thumb as deleted
					if (($buckets = Media::buckets(false)) && in_array($globals['Amazon_S3_media_bucket'], $buckets)
							&& is_writable(mnmpath.'/'.$globals['cache_dir'])) { // Double check
						syslog(LOG_NOTICE, "Meneame, deleting unexisting thumb for $this->id");
						$this->delete_thumb();
					}
				}
			}
		}
		return false;
	}

	function get_related($max = 10) {
		global $globals, $db;


		$related = array();
		$phrases = 0;

		// Only work with sphinx
		if (!$globals['sphinx_server']) return $related;
		require(mnminclude.'search.php');

		$maxid = $db->get_var("select max(link_id) from links");
		if ($this->status == 'published') {
			$_REQUEST['s'] = '! abuse discard autodiscard';
		}

		$words = array();

		// Filter title
		$a = preg_split('/[\s,\.;:“”–\"\'\-\(\)\[\]«»<>\/\?¿¡!]+/u',
			preg_replace('/[\[\(] *\w{1,6} *[\)\]]/', ' ', htmlspecialchars_decode($this->title, ENT_QUOTES)) // delete [lang] and (lang)
			, -1, PREG_SPLIT_NO_EMPTY);
		$i = 0;
		$n = count($a);
		foreach ($a as $w) {
			$wlower = mb_strtolower($w);
			$len = mb_strlen($w);
			if ( ! isset($words[$wlower])
				&& ($len > 2 || preg_match('/^[A-Z]{2,}$/', $w))
				&& !preg_match('/^\d{1,3}\D{0,1}$/', $w) ) {
				$h = sphinx_doc_hits($wlower);
				if ($h < 2 || $h > $maxid/10) continue; // If 0 or 1 it won't help to the search, too frequents neither
				if (preg_match('/^[A-Z]/', $w)) {
					$coef = 2 * log10($maxid/$h);
				} else $coef = 2;

				// Increase coefficient if a name appears also in tags
				// s{0,1} is a trick for plurals, until we use stemmed words
				if (preg_match('/(^|[ ,])'.preg_quote($w).'s{0,1}([ ,]|$)/ui', $this->tags)) {
					$coef *= 2;
					if ($i == 0 || $i == $n - 1) $coef *= 2; // It's the first or last word
				}

				$words[$wlower] = intval($h/$coef);
			}
			$i++;
		}

		// Filter tags
		$a = preg_split('/,+/', $this->tags, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($a as $w) {
			$w = trim($w);
			$wlower = mb_strtolower($w);
			$len = mb_strlen($w);
			if (isset($words[$wlower])) continue;
			if (preg_match('/\s/', $w)) {
					$wlower = "\"$wlower\"";
					$phrases++;
			}
			$h = sphinx_doc_hits($wlower);
			if ($h < 2 || $h > $maxid/10) continue; // If 0 or 1 it won't help to the search, too frequents neither
			$words[$wlower] = intval($h/2);
		}

		// Filter content, check length and that it's begin con capital
		$a = preg_split('/[\s,\.;:“”–\"\'\-\(\)\[\]«»<>\/\?¿¡!]+/u',
				preg_replace('/https{0,1}:\/\/\S+|[\[\(] *\w{1,6} *[\)\]]/i', '', text_sanitize($this->content)), // Delete parenthesided and links too
				 -1, PREG_SPLIT_NO_EMPTY);
		foreach ($a as $w) {
			$wlower = mb_strtolower($w);
			$len = mb_strlen($w);
			if ( ! isset($words[$wlower])
				&& ($len > 3 || preg_match('/^[A-Z]{2,}$/', $w))
				&& !preg_match('/^\d{1,3}\D{0,1}$/', $w) ) {
				$h = sphinx_doc_hits($wlower);
				if ($h < 2 || $h > $maxid/50) continue; // If 0 or 1 it won't help to the search, too frequents neither
				if (preg_match('/^[A-Z]/', $w) && $h < $maxid/1000) $coef = max(log10($maxid/$h) - 1, 1);
				else $coef = 1;
				$words[$wlower] = intval($h/$coef);
			}
		}

		// Increase "hits" proportional to word's lenght
		// because longer words tends to appear less
		foreach ($words as $w => $v) {
			$len = mb_strlen($w);
			if ($len > 6 && ! preg_match('/ /', $w)) {
				$words[$w] = $v * $len/6;
			}
		}

		asort($words);
		$i = 0;
		$text = '';
		foreach ($words as $w => $v) {
			$i++;
			if ($i > 14 or ($i > 8 && $v > $maxid/2000)) break;
			$text .= "$w ";
		}

		echo "\n<!-- Search terms: $text Phrases: $phrases -->\n";
		$_REQUEST['q'] = $text;

		// Center the date about the the link's date
		$_REQUEST['root_time'] = $this->date;
		if ($globals['now'] - $this->date > 86400*5) $this->old = true;
		else $this->old = false;


		$response = do_search(false, 0, $max+1, false);
		if ($response && isset($response['ids'])) {
			foreach($response['ids'] as $id) {
				if ($id == $this->id) continue;
				$l = Link::from_db($id);
				if (empty($l->permalink)) $l->permalink = $l->get_permalink();
				$related[] = $l;
			}
		}
		return $related;
	}

	function get_clicks() {
		global $db, $globals;
		if ($globals['click_counter'] && $this->id > $globals['click_counter'] && !$this->clicks > 0) {
			$this->clicks = intval($db->get_var("select counter from link_clicks where id = $this->id"));
		}
		return $this->clicks;

	}

	function user_clicked() {
		if (!isset($_COOKIE['v']) || ! preg_match('/(x|^)'.$this->id.'(x|$)/', $_COOKIE['v'])) {
			return false;
		} else {
			return true;
		}
	}

}
