<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Comment {
	var $id = 0;
	var $randkey = 0;
	var $author = 0;
	var $link = 0;
	var $date = false;
	var $order = 0;
	var $votes = 0;
	var $voted = false;
	var $karma = 0;
	var $content = '';
	var $read = false;
	var $ip = '';

	function store() {
		require_once(mnminclude.'log.php');
		global $db, $current_user, $globals;

		if(!$this->date) $this->date=$globals['now'];
		$comment_author = $this->author;
		$comment_link = $this->link;
		$comment_karma = $this->karma;
		$comment_date = $this->date;
		$comment_randkey = $this->randkey;
		$comment_content = $db->escape(clean_lines($this->content));
		if($this->id===0) {
			$this->ip = $db->escape($globals['user_ip']);
			$db->query("INSERT INTO comments (comment_user_id, comment_link_id, comment_karma, comment_ip, comment_date, comment_randkey, comment_content) VALUES ($comment_author, $comment_link, $comment_karma, '$this->ip', FROM_UNIXTIME($comment_date), $comment_randkey, '$comment_content')");
			$this->id = $db->insert_id;

			// Insert comment_new event into logs
			log_insert('comment_new', $this->id, $current_user->user_id);
		} else {
			$db->query("UPDATE comments set comment_user_id=$comment_author, comment_link_id=$comment_link, comment_karma=$comment_karma, comment_ip = '$this->ip', comment_date=FROM_UNIXTIME($comment_date), comment_randkey=$comment_randkey, comment_content='$comment_content' WHERE comment_id=$this->id");
			// Insert comment_new event into logs
			log_conditional_insert('comment_edit', $this->id, $current_user->user_id, 30);
		}
		$this->update_order();
	}

	function update_order() {
		global $db;

		if ($this->id == 0 || $this->link == 0) return false;
		$order = intval($db->get_var("select count(*) from comments where comment_link_id=$this->link and comment_id < $this->id"))+1;
		if ($order != $this->order) {
			$this->order = $order;
			$db->query("update comments set comment_order=$this->order where comment_id=$this->id");
		}
		return $this->order;
	}
	
	function read() {
		global $db, $current_user;
		$id = $this->id;
		if(($link = $db->get_row("SELECT comments.*, users.user_login, users.user_avatar, users.user_email, user_karma FROM comments, users WHERE comment_id = $id and user_id = comment_user_id"))) {
			$this->author=$link->comment_user_id;
			$this->username=$link->user_login;
			$this->email=$link->user_email;
			$this->user_karma=$link->user_karma;
			$this->randkey=$link->comment_randkey;
			$this->link=$link->comment_link_id;
			$this->order=$link->comment_order;
			$this->votes=$link->comment_votes;
			$this->karma=$link->comment_karma;
			$this->ip=$link->comment_ip;
			$this->avatar=$link->user_avatar;
			$this->content=$link->comment_content;
			$date=$link->comment_date;
			$this->date=$db->get_var("SELECT UNIX_TIMESTAMP('$date')");
			$this->read = true;
			if($this->order == 0) $this->update_order();
			return true;
		}
		$this->read = false;
		return false;
	}

	function print_summary($link, $length = 0, $single_link=true) {
		global $current_user, $globals;

		if(!$this->read) return;

		echo '<li id="ccontainer-'.$this->id.'">';
		$this->hidden = $this->karma < -50;

		if ($this->hidden)  {
			$comment_meta_class = 'comment-meta-hidden';
			$comment_class = 'comment-body-hidden';
		} else {
			$comment_meta_class = 'comment-meta';
			$comment_class = 'comment-body';
		}
		$this->link_permalink =  $link->get_relative_permalink();
		echo '<div class="'.$comment_class.'">';
		echo '<a href="'.$this->link_permalink.'#comment-'.$this->order.'"><strong>#'.$this->order.'</strong></a>';

		if ($single_link) echo '<span id="comment-'.$this->order.'">';
		echo '&nbsp;&nbsp;&nbsp;<span  id="cid-'.$this->id.'">';

		if ($this->hidden && ($current_user->user_comment_pref & 1) == 0) {
			echo '&#187;&nbsp;<a href="javascript:get_votes(\'get_comment.php\',\'comment\',\'cid-'.$this->id.'\',0,'.$this->id.')" title="'._('ver comentario').'">'._('ver comentario').'</a>';
		} else {
			$this->print_text($length, $single_link);
		}
		if ($single_link) echo '</span>';
		echo '</span></div>';


		// The comments info bar
		echo '<div class="'.$comment_meta_class.'">';
		// Print the votes info (left)
		echo '<div class="comment-votes-info">';
		// Check that the user can vote
		if ($current_user->user_id > 0 && $this->author != $current_user->user_id && $single_link)
					$this->print_shake_icons();
		echo _('votos').': <span id="vc-'.$this->id.'">'.$this->votes.'</span>, karma: <span id="vk-'.$this->id.'">'.$this->karma.'</span>';
		echo '</div>';


		// Print comment info (right)
		echo '<div class="comment-info">';
		echo _('por'). ' ';

		if ($single_link)
			echo '<a href="'.get_user_uri($this->username).'" title="karma:&nbsp;'.$this->user_karma.'" id="cauthor-'.$this->order.'">'.$this->username.'</a> ';
		else
			echo '<a href="'.get_user_uri($this->username).'" title="karma:&nbsp;'.$this->user_karma.'">'.$this->username.'</a> ';

		// Print dates
		if ($globals['now'] - $this->date > 604800) { // 7 days
			echo _('el').get_date_time($this->date);
		} else {
			echo _('hace').' '.txt_time_diff($this->date);
		}
		if (!$this->hidden) echo '<img src="'.get_avatar_url($this->author, $this->avatar, 20).'" width="20" height="20" alt="'.$this->username.'" title="'.$this->username.',&nbsp;karma:&nbsp;'.$this->user_karma.'" />';
		echo '</div></div>';
		echo "</li>\n";
	}

	function print_shake_icons() {
		global $globals, $current_user;
		if ( $current_user->user_karma > $globals['min_karma_for_comment_votes'] && $this->date > $globals['now'] - $globals['time_enabled_votes'] && ! $this->vote_exists()) {  
		 	echo '<span id="c-votes-'.$this->id.'">';
			echo '<a href="javascript:menealo_comment('."$current_user->user_id,$this->id,1".')" title="'._('voto positivo').'"><img src="'.$globals['base_url'].'img/common/vote-up01.png" width="12" height="12" alt="'._('voto positivo').'"/></a>&nbsp;&nbsp;&nbsp;';
		 	echo '<a href="javascript:menealo_comment('."$current_user->user_id,$this->id,-1".')" title="'._('voto negativo').'"><img src="'.$globals['base_url'].'img/common/vote-down01.png" width="12" height="12" alt="'._('voto negativo').'"/></a>&nbsp;';
		 	echo '</span>';
		 } else {
		 	if ($this->voted > 0) {
				echo '<img src="'.$globals['base_url'].'img/common/vote-up-gy01.png" width="12" height="12" alt="'._('votado positivo').'" title="'._('votado positivo').'"/>';
			} elseif ($this->voted<0 ) {
				echo '<img src="'.$globals['base_url'].'img/common/vote-down-gy01.png" width="12" height="12" alt="'._('votado negativo').'" title="'._('votado negativo').'"/>';
			}
		}
	}

	function vote_exists() {
		global $current_user;
		require_once(mnminclude.'votes.php');
		$vote = new Vote;
		$vote->user=$current_user->user_id;
		$vote->type='comments';
		$vote->link=$this->id;
		$this->voted = $vote->exists();
		if ($this->voted) return $this->voted;
	}

	function insert_vote() {
		global $current_user;
		require_once(mnminclude.'votes.php');
		$vote = new Vote;
		$vote->user = $current_user->user_id;
		$vote->type='comments';
		$vote->link=$this->id;
		if ($vote->exists()) {
			return false;
		}
		$vote->value = $current_user->user_karma;
		if($vote->insert()) return true;
		return false;
	}


	function print_text($length = 0, $single_link=true) {
		global $current_user, $globals;

		if (($this->author == $current_user->user_id &&
			$globals['now'] - $this->date < $globals['comment_edit_time']) || $current_user->user_level == 'god') { // gods can always edit 
			$expand = '&nbsp;&nbsp;<a href="javascript:get_votes(\'comment_edit.php\',\'edit_comment\',\'ccontainer-'.$this->id.'\',0,'.$this->id.')" title="'._('editar comentario').'"><img src="'.$globals['base_url'].'img/common/edit-misc01.png" alt="edit"/></a>';

		} elseif ($length>0 && mb_strlen($this->content) > $length + $length/2) {
			$this->content = preg_replace('/&\w*$/', '', mb_substr($this->content, 0 , $length));
			$expand = '...&nbsp;&nbsp;' .
				'<a href="javascript:get_votes(\'get_comment.php\',\'comment\',\'cid-'.$this->id.'\',0,'.$this->id.')" title="'._('resto del comentario').'">&#187;&nbsp;'._('ver todo el comentario').'</a>';
		}

		echo put_smileys($this->put_comment_tooltips(save_text_to_html($this->content), $single_link)) . $expand;
		echo "\n";
	}

	function username() {
		global $db;
//TODO
		$this->username = $db->get_var("SELECT user_login FROM users WHERE user_id = $this->author");
		return $this->username;
	}

	// Add calls for tooltip javascript functions
	function put_comment_tooltips($str, $single_link) {
		if ($single_link) {
			return preg_replace('/(^|[\s\W])#([1-9][0-9]*)/', "$1<a class='tt' href=\"".$this->link_permalink."#comment-$2\" onmouseover=\"return tooltip.c_show(event, 'id', '$2');\" onmouseout=\"tooltip.hide(event);\"  onclick=\"tooltip.hide(this);\">#$2</a>", $str);
		} else {
			return preg_replace('/(^|[\s\W])#([1-9][0-9]*)/', "$1<a class='tt' href=\"".$this->link_permalink."#comment-$2\" onmouseover=\"return tooltip.c_show(event, 'order', '$2', '".$this->link."');\" onmouseout=\"tooltip.hide(event);\"  onclick=\"tooltip.hide(this);\">#$2</a>", $str);
		}
	}

	function same_text_count($min=30) {
		global $db;
		// WARNING: $db->escape(clean_lines($comment->content)) should be the sama as in libs/comment.php (unify both!)
		return (int) $db->get_var("select count(*) from comments where comment_user_id = $this->author  and comment_date > date_sub(now(), interval $min minute) and comment_content = '".$db->escape(clean_lines($this->content))."'");
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
				$count = max($count, (int) $db->get_var("select count(*) from comments where comment_user_id = $this->author and comment_date > date_sub(now(), interval $min minute) and comment_content like '%$link%'"));
			}
		}
		return $count;
	}

}
