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
		if ($this->type == 'admin') $comment_type = 'admin';
		else $comment_type = 'normal';
		if($this->id===0) {
			$this->ip = $db->escape($globals['user_ip']);
			$db->query("INSERT INTO comments (comment_user_id, comment_link_id, comment_type, comment_karma, comment_ip, comment_date, comment_randkey, comment_content) VALUES ($comment_author, $comment_link, '$comment_type', $comment_karma, '$this->ip', FROM_UNIXTIME($comment_date), $comment_randkey, '$comment_content')");
			$this->id = $db->insert_id;

			// Insert comment_new event into logs
			log_insert('comment_new', $this->id, $current_user->user_id);
		} else {
			$db->query("UPDATE comments set comment_user_id=$comment_author, comment_link_id=$comment_link, comment_type='$comment_type', comment_karma=$comment_karma, comment_ip = '$this->ip', comment_date=FROM_UNIXTIME($comment_date), comment_randkey=$comment_randkey, comment_content='$comment_content' WHERE comment_id=$this->id");
			// Insert comment_new event into logs
			log_conditional_insert('comment_edit', $this->id, $current_user->user_id, 60);
		}
		$this->update_order();
		$this->update_conversation();
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
		if(($link = $db->get_row("SELECT SQL_CACHE comments.*, UNIX_TIMESTAMP(comment_date) as date, users.user_login, users.user_avatar, users.user_email, user_karma, user_level FROM comments, users WHERE comment_id = $id and user_id = comment_user_id"))) {
			$this->type = $link->comment_type;
			$this->author=$link->comment_user_id;
			$this->username=$link->user_login;
			$this->email=$link->user_email;
			$this->user_karma=$link->user_karma;
			$this->user_level=$link->user_level;
			$this->randkey=$link->comment_randkey;
			$this->link=$link->comment_link_id;
			$this->order=$link->comment_order;
			$this->votes=$link->comment_votes;
			$this->karma=$link->comment_karma;
			$this->ip=$link->comment_ip;
			$this->avatar=$link->user_avatar;
			$this->content=$link->comment_content;
			$this->date=$link->date;
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


		if ($single_link) $html_id = $this->order;
		else $html_id = $this->id;

		echo '<li id="c-'.$html_id.'">';

		require_once(mnminclude.'user.php');
		$this->ignored = ($current_user->user_id > 0 && $this->type != 'admin' && friend_exists($current_user->user_id, $this->author) < 0);
		$this->hidden = ($globals['comment_highlight_karma'] > 0 && $this->karma < -$globals['comment_highlight_karma'])
						|| ($this->user_level == 'disabled' && $this->type != 'admin');

		if ($this->hidden || $this->ignored)  {
			$comment_meta_class = 'comment-meta-hidden';
			$comment_class = 'comment-body-hidden';
		} else {
			$comment_meta_class = 'comment-meta';
			$comment_class = 'comment-body';
			if ($this->type == 'admin') {
				$comment_class .= ' admin';
			} elseif ($globals['comment_highlight_karma'] > 0 && $this->karma > $globals['comment_highlight_karma']) {
				$comment_class .= ' high';
			}
		}
		$this->link_permalink =  $link->get_relative_permalink();
		echo '<div class="'.$comment_class.'">';
		echo '<a href="'.$this->link_permalink.'/000'.$this->order.'"><strong>#'.$this->order.'</strong></a>';

		echo '&nbsp;&nbsp;&nbsp;<span  id="cid-'.$this->id.'">';

		if ($this->ignored || ($this->hidden && ($current_user->user_comment_pref & 1) == 0)) {
			echo '&#187;&nbsp;<a href="javascript:get_votes(\'get_comment.php\',\'comment\',\'cid-'.$this->id.'\',0,'.$this->id.')" title="'._('ver comentario').'">'._('ver comentario').'</a>';
		} else {
			$this->print_text($length, $html_id);
		}
		echo '</span></div>';


		// The comments info bar
		echo '<div class="'.$comment_meta_class.'">';
		// Check that the user can vote
		if ($this->type != 'admin' && $this->user_level != 'disabled') {
			// Print the votes info (left)
			echo '<div class="comment-votes-info">';

			if ($current_user->user_id > 0 
						&& $this->author != $current_user->user_id 
						&& $single_link
						&& $this->date > $globals['now'] - $globals['time_enabled_comments']
						&& $this->level != 'autodisabled') {
				$this->print_shake_icons();
			}

			echo _('votos').': <span id="vc-'.$this->id.'">'.$this->votes.'</span>, karma: <span id="vk-'.$this->id.'">'.$this->karma.'</span>';
			// Add the icon to show votes
			if ($this->votes > 0 && $this->date > $globals['now'] - 30*86400) { // Show votes if newer than 30 days
				echo '&nbsp;&nbsp;<a href="javascript:modal_from_ajax(\''.$globals['base_url'].'backend/get_c_v.php?id='.$this->id.'\')">';
				echo '<img src="'.$globals['base_static'].'img/common/vote-info01.png" width="12" height="12" alt="+ info" title="'._('¿quién ha votado?').'"/>';
				echo '</a>';
			}
			echo '</div>';
		}


		// Print comment info (right)
		echo '<div class="comment-info">';
		echo _('por'). ' ';

		if ($this->type == 'admin') {
			echo '<strong>'._('admin').'</strong> ';
			if ($current_user->admin) {
				echo ' ('.$this->username.') ';
			}
		} elseif ($single_link) {
			echo '<a href="'.get_user_uri($this->username).'" title="karma:&nbsp;'.$this->user_karma.'" id="cauthor-'.$this->order.'">'.$this->username.'</a> ';
		} else {
			echo '<a href="'.get_user_uri($this->username).'" title="karma:&nbsp;'.$this->user_karma.'">'.$this->username.'</a> ';
		}

		// Print dates
		if ($globals['now'] - $this->date > 604800) { // 7 days
			echo _('el').get_date_time($this->date);
		} else {
			echo _('hace').' '.txt_time_diff($this->date);
		}
		if (!$this->hidden && $this->type != 'admin' && $this->avatar) echo '<img src="'.get_avatar_url($this->author, $this->avatar, 20).'" width="20" height="20" alt="" title="'.$this->username.',&nbsp;karma:&nbsp;'.$this->user_karma.'" />';
		echo '</div></div>';
		echo "</li>\n";
	}

	function print_shake_icons() {
		global $globals, $current_user;

		$this->vote_exists();
		if ( $current_user->user_karma > $globals['min_karma_for_comment_votes'] && ! $this->voted) {  
	 		echo '<span id="c-votes-'.$this->id.'">';
			echo '<a href="javascript:menealo_comment('."$current_user->user_id,$this->id,1".')" title="'._('informativo, opinión razonada, buen humor...').'"><img src="'.$globals['base_static'].'img/common/vote-up01.png" width="12" height="12" alt="'._('voto positivo').'"/></a>&nbsp;&nbsp;&nbsp;';
	 		echo '<a href="javascript:menealo_comment('."$current_user->user_id,$this->id,-1".')" title="'._('abuso, insulto, acoso, spam, magufo...').'"><img src="'.$globals['base_static'].'img/common/vote-down01.png" width="12" height="12" alt="'._('voto negativo').'"/></a>&nbsp;';
	 		echo '</span>';
	 	} else {
	 		if ($this->voted > 0) {
				echo '<img src="'.$globals['base_static'].'img/common/vote-up-gy01.png" width="12" height="12" alt="'._('votado positivo').'" title="'._('votado positivo').'"/>';
			} elseif ($this->voted<0 ) {
				echo '<img src="'.$globals['base_static'].'img/common/vote-down-gy01.png" width="12" height="12" alt="'._('votado negativo').'" title="'._('votado negativo').'"/>';
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
		$this->voted = $vote->exists(false);
		if ($this->voted) return $this->voted;
	}

	function insert_vote() {
		global $current_user;
		require_once(mnminclude.'votes.php');
		$vote = new Vote;
		$vote->user = $current_user->user_id;
		$vote->type='comments';
		$vote->link=$this->id;
		if ($vote->exists(true)) {
			return false;
		}
		$vote->value = $current_user->user_karma;
		if($vote->insert()) return true;
		return false;
	}


	function print_text($length = 0, $html_id=false) {
		global $current_user, $globals;

		if (!$html_id) $html_id = $this->id;

		if (($this->author == $current_user->user_id 
					&& $globals['now'] - $this->date < $globals['comment_edit_time']) 
				|| (($this->author != $current_user->user_id || $this->type == 'admin')
					&& $current_user->user_level == 'god') ) { // gods can always edit 
			$expand = '&nbsp;&nbsp;<a href="javascript:get_votes(\'comment_edit.php\',\'edit_comment\',\'c-'.$html_id.'\',0,'.$this->id.')" title="'._('editar comentario').'"><img class="mini-icon-text" src="'.$globals['base_static'].'img/common/edit-misc01.png" alt="edit" width="18" height="12"/></a>';

		} elseif ($length>0 && mb_strlen($this->content) > $length + $length/2) {
			$this->content = preg_replace('/&\w*$/', '', mb_substr($this->content, 0 , $length));
			$expand = '...&nbsp;&nbsp;' .
				'<a href="javascript:get_votes(\'get_comment.php\',\'comment\',\'cid-'.$this->id.'\',0,'.$this->id.')" title="'._('resto del comentario').'">&#187;&nbsp;'._('ver todo el comentario').'</a>';
		}

		echo put_smileys($this->put_comment_tooltips(save_text_to_html($this->content))) . $expand;
		echo "\n";
	}

	function username() {
		global $db;
//TODO
		$this->username = $db->get_var("SELECT SQL_CACHE user_login FROM users WHERE user_id = $this->author");
		return $this->username;
	}

	// Add calls for tooltip javascript functions
	function put_comment_tooltips($str) {
		return preg_replace('/(^|[\(,;\.\s])#([0-9]+)/', "$1<a class='tt' href=\"".$this->link_permalink."/000$2\" onmouseover=\"return tooltip.c_show(event, 'id', '$2', '".$this->link."');\" onmouseout=\"tooltip.hide(event);\"  onclick=\"tooltip.hide(this);\">#$2</a>", $str);
	}

	function same_text_count($min=30) {
		global $db;
		// WARNING: $db->escape(clean_lines($comment->content)) should be the sama as in libs/comment.php (unify both!)
		return (int) $db->get_var("select count(*) from comments where comment_user_id = $this->author  and comment_date > date_sub(now(), interval $min minute) and comment_content = '".$db->escape(clean_lines($this->content))."'");
	}

	function get_links() {
		global $current_user;

		$this->links = array();
		$this->banned = false;

		$localdomain = preg_quote(get_server_name(), '/');
		preg_match_all('/([\(\[:\.\s]|^)(https*:\/\/[^ \t\n\r\]\(\)\&]{5,70}[^ \t\n\r\]\(\)]*[^ .\t,\n\r\(\)\"\'\]\?])/i', $this->content, $matches);
		foreach ($matches[2] as $match) {
			require_once(mnminclude.'ban.php');
			$link=clean_input_url($match);
			$components = parse_url($link);
			if ($components && ! preg_match("/.*$localdomain$/", $components['host'])) {
				$this->banned |= check_ban($link, 'hostname', false, true); // Mark this comment as containing a banned link
				if ($this->banned) { 	
					syslog(LOG_NOTICE, "Meneame: banned link in comment: $match ($current_user->user_login)");
				}
				if (array_search($components['host'], $this->links) === false)
					array_push($this->links, $components['host']);
			}
		}
	}

	function same_links_count($min=30) {
		global $db, $current_user;

		if ($this->id > 0) {
			$not_me = "and comment_id != $this->id";
		} else {
			$not_me = '';
		}

		$count = 0;
		$localdomain = preg_quote(get_server_name(), '/');
		foreach ($this->links as $host) {
			if ($this->banned) $interval = $min * 2;
			elseif (preg_match("/.*$localdomain$/", $host)) $interval = $min / 3; // For those pointing to dupes
			else $interval = $min;

			$link = '://'.$host;
			$link=preg_replace('/([_%])/', "\$1", $link);
			$link=$db->escape($link);
			$same_count = (int) $db->get_var("select count(*) from comments where comment_user_id = $this->author and comment_date > date_sub(now(), interval $interval minute) and comment_content like '%$link%' $not_me");
			$count = max($count, $same_count);
		}
		return $count;
	}

	function save_from_post($link) {
		global $db, $current_user, $globals;

		$error = '';


		require_once(mnminclude.'ban.php');
		if(check_ban_proxy()) return _('dirección IP no permitida');

		// Check if is a POST of a comment

		if( ! ($link->votes > 0 && $link->date > $globals['now']-$globals['time_enabled_comments']*1.01 && 
				$link->comments < $globals['max_comments'] &&
				intval($_POST['link_id']) == $link->id && $current_user->authenticated && 
				intval($_POST['user_id']) == $current_user->user_id &&
				intval($_POST['randkey']) > 0
				)) {
			return _('comentario o usuario incorrecto');
		}

		if ($current_user->user_karma < $globals['min_karma_for_comments'] && $current_user->user_id != $link->author) {
			return _('karma demasiado bajo');
		}

		$this->link=$link->id;
		$this->ip = $db->escape($globals['user_ip']);
		$this->randkey=intval($_POST['randkey']);
		$this->author=intval($_POST['user_id']);
		$this->karma=round($current_user->user_karma);
		$this->content=clean_text($_POST['comment_content'], 0, false, 10000);
		// Check if is an admin comment
		if ($current_user->user_level == 'god' && $_POST['type'] == 'admin') {
			$this->type = 'admin';
		} 

		// Basic check to avoid abuses from same IP
		if (!$current_user->admin && $current_user->user_karma < 6.2) { // Don't check in case of admin comments or higher karma

			// Avoid astroturfing from the same link's author
			if ($link->status != 'published' && $link->ip == $globals['user_ip'] && $link->author != $this->author) {
				insert_clon($this->author, $link->author, $link->ip);
				syslog(LOG_NOTICE, "Meneame, comment-link astroturfing ($current_user->user_login, $link->ip): ".$link->get_permalink());
				return _('no se puede comentar desde la misma IP del autor del envío');
			}

			// Avoid floods with clones from the same IP
			if (intval($db->get_var("select count(*) from comments where comment_link_id = $link->id and comment_ip='$this->ip' and comment_user_id != $this->author")) > 1) {
				syslog(LOG_NOTICE, "Meneame, comment astroturfing ($current_user->user_login, $this->ip)");
				return _('demasiados comentarios desde la misma IP con usuarios diferentes');
			}
		}


		if (mb_strlen($this->content) < 5 || ! preg_match('/[a-zA-Z:-]/', $_POST['comment_content'])) { // Check there are at least a valid char
			return _('texto muy breve o caracteres no válidos');
		}


		// Check the comment wasn't already stored
		$already_stored = intval($db->get_var("select count(*) from comments where comment_link_id = $this->link and comment_user_id = $this->author and comment_randkey = $this->randkey"));
		if ($already_stored) {
			return _('comentario duplicado');
		}

		if (! $current_user->admin) {
			$this->get_links();
			if ($this->banned && $current_user->Date() > $globals['now'] - 86400) {
				syslog(LOG_NOTICE, "Meneame: comment not inserted, banned link ($current_user->user_login)");
				return _('comentario no insertado, enlace a sitio deshabilitado (y usuario reciente)');
			}

			// Lower karma to comments' spammers
			$comment_count = (int) $db->get_var("select count(*) from comments where comment_user_id = $current_user->user_id and comment_date > date_sub(now(), interval 3 minute)");
			// Check the text is not the same
			$same_count = $this->same_text_count();
			$same_links_count = $this->same_links_count();
			if ($this->banned) $same_links_count *= 2;
			$same_count += $same_links_count;
		} else {
			$comment_count  = $same_count = 0;
		}

		$comment_limit = round(min($current_user->user_karma/6, 2) * 2.5);
		if ($comment_count > $comment_limit || $same_count > 2) {
			require_once(mnminclude.'user.php');
			$reduction = 0;
			if ($comment_count > $comment_limit) {
				$reduction += ($comment_count-3) * 0.1;
			}
			if($same_count > 1) {
				$reduction += $same_count * 0.25;
			}
			if ($reduction > 0) {
				$user = new User;
				$user->id = $current_user->user_id;
				$user->read();
				$user->karma = $user->karma - $reduction;
				syslog(LOG_NOTICE, "Meneame: story decreasing $reduction of karma to $current_user->user_login (now $user->karma)");
				$user->store();
				require_once(mnminclude.'annotation.php');
				$annotation = new Annotation("karma-$user->id");
				$annotation->append(_('texto repetido o abuso de enlaces en comentarios').": -$reduction, karma: $user->karma\n");
				$error .= ' ' . ('penalización de karma por texto repetido o abuso de enlaces');
			}
		}
		$this->store();
		$this->insert_vote();
		$link->update_comments();
		return $error;
	}

	function update_conversation() {
		global $db, $globals;

		$db->query("delete from conversations where conversation_type='comment' and conversation_from=$this->id");
		$orders = array();
		if (preg_match_all('/(^|[\(,;\.\s])#(\d+)/', $this->content, $matches)) {
			foreach ($matches[2] as $order) {
				$orders[$order] += 1;
			}
		}
		foreach ($orders as $order => $val) {
			if ($order == 0) {
				$to = $db->get_row("select 0 as id, link_author as user_id from links where link_id = $this->link");
			} else {
				$to = $db->get_row("select comment_id as id, comment_user_id as user_id from comments where comment_link_id = $this->link and comment_order=$order and comment_type != 'admin'");
			}
			if ($to && $to->user_id != $this->author) {
				if (!$this->date) $this->date = time();
				$db->query("insert into conversations (conversation_user_to, conversation_type, conversation_time, conversation_from, conversation_to) values ($to->user_id, 'comment', from_unixtime($this->date), $this->id, $to->id)");
			}
		}

	}
}
?>
