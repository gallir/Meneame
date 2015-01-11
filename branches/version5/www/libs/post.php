<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once(mnminclude.'favorites.php');

class Post extends LCPBase {
	var $id = 0;
	var $prefix_id = '';
	var $randkey = 0;
	var $author = 0;
	var $date = false;
	var $votes = 0;
	var $voted = false;
	var $karma = 0;
	var $content = '';
	var $src = 'web';
	var $read = false;

	const SQL = " SQL_NO_CACHE post_id as id, post_user_id as author, user_login as username, user_karma, user_level as user_level, post_randkey as randkey, post_votes as votes, post_karma as karma, post_src as src, post_ip_int as ip, user_avatar as avatar, post_content as content, UNIX_TIMESTAMP(posts.post_date) as date, favorite_link_id as favorite, vote_value as voted, media.size as media_size, media.mime as media_mime, media.extension as media_extension, media.access as media_access, UNIX_TIMESTAMP(media.date) as media_date FROM posts
	LEFT JOIN users on (user_id = post_user_id)
	LEFT JOIN favorites ON (@user_id > 0 and favorite_user_id =  @user_id and favorite_type = 'post' and favorite_link_id = post_id)
	LEFT JOIN votes ON (post_date > @enabled_votes and @user_id > 0 and vote_type='posts' and vote_link_id = post_id and vote_user_id = @user_id)
	LEFT JOIN media ON (media.type='post' and media.id = post_id and media.version = 0) ";

	// Regular expression to detect referencies to other post, like @user,post_id
	// const REF_PREG = "/(^|\W)@([^\s<>;:,\?\)]+(?:,\d+){0,1})/u";
	const REF_PREG = "/(^|\W)@([\p{L}\.][\.\d\-_\p{L}]+(?:,\d+){0,1})/u";

	static function from_db($id) {
		global $db, $current_user;
		if(($result = $db->get_object("SELECT".Post::SQL."WHERE post_id = $id", 'Post'))) {
			if ($result->src == 'im') {
				$result->src = 'jabber';
			}
			$result->read = true;
			return $result;
		}
		return null;
	}

	static function update_read_conversation($time = false) {
		global $db, $globals, $current_user;
		$key = 'p_last_read';

		if (! $current_user->user_id ) return false;


		if (! $time) $time = $globals['now'];
		$previous = (int) $db->get_var("select pref_value from prefs where pref_user_id = $current_user->user_id and pref_key = '$key'");
		if ($time > $previous) {
			$r = $db->query("delete from prefs where pref_user_id = $current_user->user_id and pref_key = '$key'");
			if ($r) {
				$db->query("insert into prefs set pref_user_id = $current_user->user_id, pref_key = '$key', pref_value = $time");
			}
		}
		return User::reset_notification($current_user->user_id, 'post');
	}

	static function get_unread_conversations($user = 0) {
		global $db, $globals, $current_user;
		$key = 'p_last_read';

		if (!$user && $current_user->user_id > 0) $user = $current_user->user_id;

		$n = User::get_notification($user, 'post');
		if (is_null($n)) {
			$last_read = intval($db->get_var("select pref_value from prefs where pref_user_id = $user and pref_key = '$key'"));
			$n = (int) $db->get_var("select count(*) from conversations where conversation_user_to = $user and conversation_type = 'post' and conversation_time > FROM_UNIXTIME($last_read)");
			User::reset_notification($user, 'post', $n);
		}
		return $n;
	}

	static function can_add() {
		// Check an user can add a new post
		global $globals, $current_user, $db;
		return (!$globals['min_karma_for_posts'] || $current_user->user_karma >= $globals['min_karma_for_posts'])
				&& !$db->get_var("select post_id from posts where post_user_id=$current_user->user_id and post_date > date_sub(now(), interval ".$globals['posts_period']." second) order by post_id desc limit 1") > 0;
	}

	static function count($force = false) {
		global $db;

		$count = get_count('posts');
		if ($count === false || $force) {
			$count = $db->get_var("select count(*) from posts");
			set_count('posts', $count);
		}
		return $count;
	}

	function store($full = true) {
		global $db, $current_user, $globals;

		if(!$this->date) $this->date=time();
		$post_author = $this->author;
		$post_src = $this->src;
		$post_karma = $this->karma;
		$post_date = $this->date;
		$post_randkey = $this->randkey;
		$post_content = $db->escape($this->normalize_content());
		if($this->id===0) {
			$this->ip = $globals['user_ip_int'];
			$r = $db->query("INSERT INTO posts (post_user_id, post_karma, post_ip_int, post_date, post_randkey, post_src, post_content) VALUES ($post_author, $post_karma, $this->ip, FROM_UNIXTIME($post_date), $post_randkey, '$post_src', '$post_content')");
			$this->id = $db->insert_id;
			if ($this->id > 0) {
				$this->insert_vote($post_author);
				// Insert post_new event into logs
				if ($full) Log::insert('post_new', $this->id, $post_author);
			}
		} else {
			$r = $db->query("UPDATE posts set post_user_id=$post_author, post_karma=$post_karma, post_date=FROM_UNIXTIME($post_date), post_randkey=$post_randkey, post_content='$post_content' WHERE post_id=$this->id");
			// Insert post_new event into logs
			if ($r && $full) Log::conditional_insert('post_edit', $this->id, $post_author, 30);
		}
		if ($r && $full) $this->update_conversation();
	}

	function read() {
		global $db, $current_user;
		if(($result = $db->get_row("SELECT".Post::SQL."WHERE post_id = $this->id"))) {
			foreach(get_object_vars($result) as $var => $value) $this->$var = $value;
			if ($this->src == 'im') {
				$this->src = 'jabber';
			}
			$this->read = true;
			return true;
		}
		$this->read = false;
		return false;
	}

	function read_last($user=0) {
		global $db, $current_user;
		$id = $this->id;
		if ($user > 0) {
			$sql = "select post_id from posts where post_user_id = $user order by post_date desc limit 1";
		} else {
			$sql = "select post_id from posts order by post_date desc limit 1";
		}
		$id = $db->get_var($sql);
		if ($id > 0) {
			$this->id = $id;
			return $this->read();
		}
		return false;
	}

	function print_summary($length=0) {
		global $current_user, $globals;

		if(!$this->read) $this->read();
		$this->hidden = $this->karma < $globals['post_hide_karma'] ||
				$this->user_level == 'disabled';
		$this->ignored = $current_user->user_id > 0 && User::friend_exists($current_user->user_id, $this->author) < 0;

		if ($this->hidden || $this->ignored)  {
			$post_meta_class = 'comment-meta hidden';
			$post_class = 'comment-body hidden';
		} else {
			$post_meta_class = 'comment-meta';
			$post_class = 'comment-body';
			if ($this->karma > $globals['post_highlight_karma']) {
				$post_class .= ' high';
			}
		}
		if ($this->author == $current_user->user_id) {
			$post_class .= ' user';
		}


		$this->is_disabled   = $this->ignored || ($this->hidden && ($current_user->user_comment_pref & 1) == 0);
		$this->can_vote	  = $current_user->user_id > 0 && $this->author != $current_user->user_id &&  $this->date > time() - $globals['time_enabled_votes'];
		$this->user_can_vote =  $current_user->user_karma > $globals['min_karma_for_comment_votes'] && ! $this->voted;
		$this->show_votes	= ($this->votes > 0 && $this->date > $globals['now'] - 30*86400); // Show votes if newer than 30 days
		$this->show_avatar = true;

		$this->prepare_summary_text($length);

		$vars = compact('post_meta_class', 'post_class', 'length');
		/* reference $this to use in the template */
		$vars['self'] = $this;
		return Haanga::Load('post_summary.html', $vars);
	}

	function print_user_avatar($size=40) {
		global $globals;
		echo '<a href="'.get_user_uri($this->username).'" class="tooltip u:'.$this->author.'"><img class="avatar" src="'.get_avatar_url($this->author, $this->avatar, $size).'" width="'.$size.'" height="'.$size.'" alt="'.$this->username.'"/></a>';
	}

	function prepare_summary_text($length = 0) {
		global $current_user, $globals;

		if (empty($this->basic_summary) && (($this->author == $current_user->user_id &&
			time() - $this->date < $globals['posts_edit_time'] ) ||
			 ($current_user->user_level == 'god' && time() - $this->date < $globals['posts_edit_time_admin'] ))) { // Admins can edit up to 10 days
			$this->can_edit = true;

		} else {
			$this->can_edit = false;
		}
		if ($length > 0) {
			$this->content = text_to_summary($this->content, $length);
		}
		$this->content = $this->to_html($this->content) . $expand;

		if ($this->media_size > 0) {
			$this->media_thumb_dir = Upload::get_cache_relative_dir($this->id);
			$this->media_url = Upload::get_url('post', $this->id, 0, $this->media_date, $this->media_mime);
		}
	}

	function print_text($length = 0) {
		global $current_user, $globals;

		$this->prepare_summary_text($length);
		$vars = array('self' => $this);
		return Haanga::Load('post_summary_text.html', $vars);
	}

	function clean_content() {
		// Clean other post references
		return preg_replace('/(@[\S.-]+)(,\d+)/','$1',$this->content);
	}

	function print_edit_form() {
		global $globals, $current_user;

		if ($this->id == 0) {
			$this->randkey = rand(1000000,100000000);
		}
		$this->body_left = $globals['posts_len'] - mb_strlen(html_entity_decode($this->content, ENT_COMPAT, 'UTF-8'), 'UTF-8');

		$vars = array();
		$vars['self'] = $this;
		return Haanga::Load('post_edit.html', $vars);
	}

	function vote_exists() {
		global $current_user;
		$vote = new Vote('posts', $this->id, $current_user->user_id);
		$this->voted = $vote->exists(false);
		if ($this->voted) return $this->voted;
	}

	function insert_vote($user_id = false, $value = 0) {
		global $current_user, $db;

		if (! $user_id) $user_id = $current_user->user_id;
		if (! $value && $current_user->user_karma) {
			$value = $current_user->user_karma;
		}

		$vote = new Vote('posts', $this->id, $user_id);
		$vote->link=$this->id;
		if ($vote->exists(true)) {
			return false;
		}
		$vote->value = $value;
		$db->transaction();
		if(($r = $vote->insert())) {
			if ($current_user->user_id != $this->author) {
				$r = $db->query("update posts set post_votes=post_votes+1, post_karma=post_karma+$value, post_date=post_date where post_id=$this->id");
			}
		}

		$c = $db->commit();

		if ($r && $c) {
			return $vote->value;
		}

        syslog(LOG_INFO, "failed insert post vote for $this->id");
        return false;
	}

	function same_text_count($min=30) {
		global $db;
		// WARNING: $db->escape(clean_lines($comment->content)) should be the sama as in libs/comment.php (unify both!)
		return (int) $db->get_var("select count(*) from posts where post_user_id = $this->author and post_date > date_sub(now(), interval $min minute) and post_content = '".$db->escape(clean_lines($this->content))."'");
	}

	function same_links_count($min=30) {
		global $db;
		$count = 0;
		$localdomain = preg_quote(get_server_name(), '/');
		preg_match_all('/([\(\[:\.\s]|^)(https*:\/\/[^ \t\n\r\]\(\)\&]{5,70}[^ \t\n\r\]\(\)]*[^ .\t,\n\r\(\)\"\'\]\?])/i', $this->content, $matches);
		foreach ($matches[2] as $match) {
			$link=clean_input_url($match);
			$components = parse_url($link);
			if (! preg_match("/.*$localdomain$/", $components[host])) {
				$link = "//$components[host]$components[path]";
				$link=preg_replace('/(_%)/', "\$1", $link);
				$link=$db->escape($link);
				$count = max($count, (int) $db->get_var("select count(*) from posts where post_user_id = $this->author and post_date > date_sub(now(), interval $min minute) and post_content like '%$link%'"));
			}
		}
		return $count;
	}

	function update_conversation() {
		global $db, $globals;

		$previous_ids = $db->get_col("select distinct conversation_to from conversations where conversation_type='post' and conversation_from=$this->id");
		if ($previous_ids) {
			// Select users previous conversation to decrease in the new system
			$previous_users = $db->get_col("select distinct conversation_user_to from conversations where conversation_type='post' and conversation_from=$this->id");
		} else {
			$previous_users = array();
		}

		//$db->query("delete from conversations where conversation_type='post' and conversation_from=$this->id");
		$seen_users = array();
		$seen_ids = array();
		$refs = 0;
		if (!$this->date) $this->date = time();

		if (preg_match_all(Post::REF_PREG, $this->content, $matches)) {
			foreach ($matches[2] as $reference) {
				$user = $db->escape(preg_replace('/,\d+$/', '', $reference));
				$to = $db->get_var("select user_id from users where user_login = '$user'");
				$id = intval(preg_replace('/[^\s]+,(\d+)$/', '$1', $reference));
				if (! $to > 0) continue;

				if (! $id > 0) {
					$id = (int) $db->get_var("select post_id from posts where post_user_id = $to and post_date < FROM_UNIXTIME($this->date) order by post_date desc limit 1");
				}
				if (! in_array($id, $previous_ids) && ! in_array($id, $seen_ids)) {
					if (User::friend_exists($to, $this->author) >= 0
							&& $refs < 10
							&& $this->author != $to // Don't show notification for the same user
							&& ! in_array($to, $seen_users)  // Limit the number of references to avoid abuses/spam and multip
							&& ! in_array($to, $previous_users)) {
						User::add_notification($to, 'post');
					}
					$db->query("insert into conversations (conversation_user_to, conversation_type, conversation_time, conversation_from, conversation_to) values ($to, 'post', from_unixtime($this->date), $this->id, $id)");

				}
				$refs++;
				if (! in_array($id, $seen_ids)) $seen_ids[] = $id;
				if (! in_array($to, $seen_users)) $seen_users[] = $to;
			}
		}

		$to_delete = array_diff($previous_ids, $seen_ids);
		if ($to_delete) {
			$to_delete = implode(',', $to_delete);
			$db->query("delete from conversations where conversation_type='post' and conversation_from=$this->id and conversation_to in ($to_delete)");
		}

		$to_unnotify = array_diff($previous_users, $seen_users);
		foreach ($to_unnotify as $to) {
			User::add_notification($to, 'post', -1);
		}

	}

	function normalize_content() {
		$this->content = clean_lines(clear_whitespace(normalize_smileys($this->content)));
		return $this->content;
	}

	function store_image_from_form($field = 'image') {
		return parent::store_image_from_form('post', $field);
	}

	function store_image($file) {
		return parent::store_image('post', $file);
	}

	function move_tmp_image($file, $mime) {
		return parent::move_tmp_image('post', $file, $mime);
	}

	function delete_image() {
		$media = new Upload('post', $this->id, 0);
		$media->delete();
		$this->media_size = 0;
		$this->media_mime = '';
	}

}
