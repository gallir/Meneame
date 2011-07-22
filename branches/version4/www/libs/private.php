<?
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Menéame and Ricardo Galli <gallir at gallir dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once(mnminclude.'favorites.php');

class PrivateMessage extends LCPBase {
	var $id = 0;
	var $randkey = 0;
	var $author = 0;
	var $date = false;
	var $content = '';

	const SQL = " SQL_NO_CACHE privates.id as id, privates.user as author, users.user_login as username, privates.`to` as `to`, users_to.user_login as to_username, users_to.user_avatar as to_avatar, randkey, privates.ip, users.user_avatar as avatar, texts.content as content, UNIX_TIMESTAMP(privates.date) as date, UNIX_TIMESTAMP(privates.read) as date_read, media.size as media_size, media.mime as media_mime, media.access as media_access FROM privates
	LEFT JOIN users on (user_id = privates.user)
	LEFT JOIN users as users_to on (users_to.user_id = privates.to)
	LEFT JOIN texts on (texts.key = 'privates' and texts.id = privates.id)
	LEFT JOIN media ON (media.type='private' and media.id = privates.id and media.version = 0) ";

	// Regular expression to detect referencies to other post, like @user,post_id
	const REF_PREG = "/(^|\W)@([^\s<>;:,\?\)]+(?:,\d+){0,1})/u";

	static function from_db($id) {
		global $db, $current_user;
		if(($result = $db->get_object("SELECT".PrivateMessage::SQL."WHERE privates.id = $id", 'PrivateMessage'))) {
			return $result;
		}
		return null;
	}

	static function get_unread($id) {
		global $db;
		return (int) $db->get_var("select count(*) from privates where `to` = $id and `read` = 0");
	}

	static function can_send($from, $to) {
		global $db;

		$friendship = User::friend_exists($to, $from);
		return $friendship > 0 || 
			(! $friendship && intval($db->get_var("select count(*) from privates where user = $to and `to` = $from and date > date_sub(now(), interval 15 day)")) > 0);
	}

	function store($full = true) {
		global $db, $current_user, $globals;

		$db->transaction();
		if(!$this->date) $this->date=time();
		$content = $db->escape($this->normalize_content());
		if($this->id===0) {
			$this->ip = $db->escape($globals['user_ip']);
			$db->query("INSERT INTO privates (user, `to`, ip, date, randkey) VALUES ($this->author, $this->to, '$this->ip', FROM_UNIXTIME($this->date), $this->randkey)");
			$this->id = $db->insert_id;
		} else {
			$db->query("UPDATE privates set date=FROM_UNIXTIME($this->date) WHERE post_id=$this->id");
		}
		if ($this->id > 0) {
			$db->query("REPLACE INTO texts (`key`, id, content) VALUES ('privates', $this->id, '$content')");
		}
		$db->commit();
	}

	function mark_read() {
		global $db, $current_user;

		if ($this->id > 0 && $this->to == $current_user->user_id) {
			$db->query("update privates set privates.read = now() where id = $this->id");
			$this->date_read = time();
		}
	}

	function print_summary($length=0) {
		global $current_user, $globals;

		if ($current_user->user_id != $this->author && $current_user->user_id != $this->to) return; // Security check

		$post_meta_class = 'comment-meta';
		$post_class = 'comment-body';

		if ($this->date_read < $this->date) {
			$post_class .= ' new';
		}

		if ($this->author != $current_user->user_id) {
			$author = '<a href="'.get_user_uri($this->username).'">' . ' ' . $this->username.'</a>';
		} else {
			$author = 'ti';
		}

		// Print dates
		if ($globals['now'] - $this->date > 604800) { // 7 days
			$this->comment_info = sprintf(_('el %s %s por %s'), get_date_time($this->date), '', $author);
		} else {
			$this->comment_info = sprintf(_('hace %s %s por %s'), txt_time_diff($this->date), '', $author);
		}

		if ($length > 0) {
			$this->content = text_to_summary($this->content, $length);
		}
		$this->content = $this->to_html($this->content) . $expand;


		$vars = compact('post_meta_class', 'post_class', 'length');
		/* reference $this to use in the template */
		$vars['self'] = $this;
		return Haanga::Load('priv_summary.html', $vars);
	}

	function print_user_avatar($size=40) {
		global $globals;
		echo '<a href="'.get_user_uri($this->username).'" class="tooltip u:'.$this->author.'"><img class="avatar" src="'.get_avatar_url($this->author, $this->avatar, $size).'" width="'.$size.'" height="'.$size.'" alt="'.$this->username.'"/></a>';
	}

	function print_text($length = 0) {
		global $current_user, $globals;
	}

	function print_edit_form() {
		global $globals, $current_user;

		if ($this->id == 0) {
			$this->randkey = rand(1000000,100000000);
		}
		if ($this->to > 0) {
			$this->to_username = User::get_username($this->to);
		}

		$this->body_left = $globals['posts_len'] - mb_strlen(html_entity_decode($this->content, ENT_COMPAT, 'UTF-8'), 'UTF-8');

		$vars = array();
		$vars['self'] = $this;
		return Haanga::Load('priv_edit.html', $vars);
	}

	function normalize_content() {
		$this->content = clean_lines(clear_whitespace(normalize_smileys($this->content)));
		return $this->content;
	}

	function store_image($file) {
		$media = new Upload('private', $this->id, 0);
		$media->to = $this->to;
		$media->access = 'private';
		if ($media->from_temporal($file, 'image')) {
			$this->media_size = $media->size;
			$this->media_mime = $media->mime;
			return true;
		}
		return false;
	}

	function delete_image() {
		$media = new Upload('private', $this->id, 0);
		$media->delete();
		$this->media_size = 0;
		$this->media_mime = '';
	}

}
