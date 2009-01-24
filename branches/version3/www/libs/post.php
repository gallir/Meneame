<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Post {
	var $id = 0;
	var $randkey = 0;
	var $author = 0;
	var $date = false;
	var $votes = 0;
	var $voted = false;
	var $karma = 0;
	var $content = '';
	var $src = 'web';
	var $read = false;

	function store() {
		require_once(mnminclude.'log.php');
		global $db, $current_user, $globals;

		if(!$this->date) $this->date=time();
		$post_author = $this->author;
		$post_src = $this->src;
		$post_karma = $this->karma;
		$post_date = $this->date;
		$post_randkey = $this->randkey;
		$post_content = $db->escape(clean_lines($this->content));
		if($this->id===0) {
			$this->ip = $globals['user_ip_int'];
			$db->query("INSERT INTO posts (post_user_id, post_karma, post_ip_int, post_date, post_randkey, post_src, post_content) VALUES ($post_author, $post_karma, $this->ip, FROM_UNIXTIME($post_date), $post_randkey, '$post_src', '$post_content')");
			$this->id = $db->insert_id;

			// Insert post_new event into logs
			log_insert('post_new', $this->id, $post_author);
		} else {
			$db->query("UPDATE posts set post_user_id=$post_author, post_karma=$post_karma, post_ip_int = '$this->ip', post_date=FROM_UNIXTIME($post_date), post_randkey=$post_randkey, post_content='$post_content' WHERE post_id=$this->id");
			// Insert post_new event into logs
			log_conditional_insert('post_edit', $this->id, $post_author, 30);
		}
		$this->update_conversation();
	}

	function read() {
		global $db, $current_user;
		$id = $this->id;
		if(($link = $db->get_row("SELECT posts.*, UNIX_TIMESTAMP(posts.post_date) as date, users.user_login, users.user_avatar, user_karma FROM posts, users WHERE post_id = $id and user_id = post_user_id"))) {
			$this->author=$link->post_user_id;
			$this->username=$link->user_login;
			$this->user_karma=$link->user_karma;
			$this->randkey=$link->post_randkey;
			$this->votes=$link->post_votes;
			$this->karma=$link->post_karma;
			$this->src=$link->post_src;
			$this->ip=$link->post_ip_int;
			$this->avatar=$link->user_avatar;
			$this->content=$link->post_content;
			$this->date=$link->date;
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

	function print_summary($length = 0) {
		global $current_user, $globals;

		if(!$this->read) $this->read(); 

		echo '<li id="pcontainer-'.$this->id.'">';

		$post_meta_class = 'comment-meta';
		$post_class = 'comment-body';
		if ($this->karma > 100) {
			$post_class .= ' high';
		}

		echo '<div class="'.$post_class.'">';

		echo '<a href="'.get_user_uri($this->username).'"><img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$this->author.');" onmouseout="tooltip.clear(event);" class="avatar" src="'.get_avatar_url($this->author, $this->avatar, 40).'" width="40" height="40" alt="'.$this->username.'"/></a>';

		$this->print_text($length);
		echo '</div>';



		// The comments info bar
		echo '<div class="'.$post_meta_class.'">';

		// Print the votes info (left)
		echo '<div class="comment-votes-info">';
		// Check that the user can vote
		if ($current_user->user_id > 0 && $this->author != $current_user->user_id)
					$this->print_shake_icons();
		echo _('votos').': <span id="vc-'.$this->id.'">'.$this->votes.'</span>, karma: <span id="vk-'.$this->id.'">'.$this->karma.'</span>';

		// Add the icon to show votes
		if ($this->votes > 0 && $this->date > $globals['now'] - 30*86400) { // Show votes if newer than 30 days
			echo '&nbsp;&nbsp;<a href="javascript:modal_from_ajax(\''.$globals['base_url'].'backend/get_p_v.php?id='.$this->id.'\')">';
			echo '<img src="'.$globals['base_url'].'img/common/vote-info01.png" width="12" height="12" alt="+ info" title="'._('¿quién ha votado?').'"/>';
			echo '</a>';
		}

		echo '</div>';

		// Print comment info (right)
		echo '<div class="comment-info">';
		echo '<a href="'.post_get_base_url($this->username).'">'. _('nota de') . ' ' . $this->username.'</a> ';
		echo '('.$this->src.') ';
		echo '(<a href="'.post_get_base_url($this->username).'/'.$this->id.'" title="permalink">#</a>) ';
		
		// Print dates
		if (time() - $this->date > 604800) { // 7 days
			echo _('el').get_date_time($this->date);
		} else {
			echo _('hace').' '.txt_time_diff($this->date);
		}
		echo '</div></div>';
		echo "</li>\n";
	}

	function print_text($length = 0) {
		global $current_user, $globals;

		if (($this->author == $current_user->user_id &&
			time() - $this->date < 3600 ) ||
			 ($current_user->user_level == 'god' && time() - $this->date < 864000)) { // Admins can edit up to 10 days
			$expand = '&nbsp;&nbsp;&nbsp;<a href="javascript:get_votes(\'post_edit.php\',\'edit_post\',\'pcontainer-'.$this->id.'\',0,'.$this->id.')" title="'._('editar').'"><img class="mini-icon-text" src="'.$globals['base_url'].'img/common/edit-misc01.png" alt="edit"/></a>';

		}

		echo put_smileys($this->put_tooltips(save_text_to_html($this->content))) . $expand;
		echo "\n";
	}

	function put_tooltips ($str) {
		return preg_replace('/(^|\s)@([\S\.\-]+[\S])/u', "$1<a class='tt' href='/".$globals['base_url']."backend/get_post_url.php?id=$2-".$this->date."' onmouseover=\"return tooltip.ajax_delayed(event, 'get_post_tooltip.php', '$2".'-'.$this->date."');\" onmouseout=\"tooltip.hide(event);\">@$2</a>", $str);
	}

	function print_edit_form() {
		global $globals, $current_user;
		echo '<div class="commentform" id="edit-form">'."\n";
		echo '<fieldset><legend><span class="sign">';
		if ($this->id > 0) {
			echo _('edición nota');
		} else {
			echo _('nueva nota');
			$this->randkey = rand(1000000,100000000);
		}
		echo '</span></legend>';
		echo '<form action="'.$globals['base_url'].'backend/post_edit.php?user='.$current_user->user_id.'" method="post" id="thisform'.$this->id.'" name="thisform'.$this->id.'">'."\n";
		echo '<input type="hidden" name="key" value="'.$this->randkey.'" />'."\n";
		echo '<input type="hidden" name="post_id" value="'.$this->id.'" />'."\n";
		echo '<input type="hidden" name="user_id" value="'.$this->author.'" />'."\n";
		echo '<textarea name="post" rows="3" cols="40" id="post" onKeyDown="textCounter(document.thisform'.$this->id.'.post,document.thisform'.$this->id.'.postcounter,300)" onKeyUp="textCounter(document.thisform'.$this->id.'.post,document.thisform'.$this->id.'.postcounter,300)">'.$this->content.'</textarea>'."\n";
		$body_left = 300 - mb_strlen(html_entity_decode($this->content, ENT_COMPAT, 'UTF-8'), 'UTF-8');
		echo '<br /><input readonly type="text" name="postcounter" size="3" maxlength="3" value="'. $body_left . '" /> <span class="note">' . _('caracteres libres') . '</span>';
		echo '&nbsp;&nbsp;&nbsp;';
		echo '<input class="button" type="submit" value="'._('guardar').'" />'."\n";
		echo '</form>'."\n";
		echo '</fieldset>'."\n";
		echo '</div>'."\n";

		echo'<script type="text/javascript">'."\n";
		// prepare Options Object 
		if ($this->id == 0) {
			echo 'var options = {success:  function(response) {if (/^ERROR:/.test(response)) alert(response); else { $("#newpost").html(response); $("#addpost").hide("fast"); } } }; ';
		} else {
			echo 'var options = {success:  function(response) {if (/^ERROR:/.test(response)) alert(response); else { $("#pcontainer-'.$this->id.'").html(response); } } }; ';
		}
		// wait for the DOM to be loaded 
		echo'$(\'#thisform'.$this->id.'\').ajaxForm(options);' ."\n";
		echo '</script>'."\n";
	}

	function print_post_teaser($rss_option) {
		global $globals, $current_user;


		echo '<div id="addpost">';
		// Print "new note" is the user is authenticated
		if ($current_user->user_id > 0) {
			if (!$this->read_last($current_user->user_id) || time() - $this->date > 60) {
				echo '<a href="javascript:get_votes(\'post_edit.php\',\'edit_comment\',\'addpost\',0,0)" title="'._('insertar una nota').'"><img src="'.$globals['base_url'].'img/common/add-notame01.png" alt="'._("insertar una nota").'"/></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			} else {
				echo '<img src="'.$globals['base_url'].'img/common/add-notame02.png" alt="'._("espera unos minutos para entrar otra nota").'"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}
		}

		echo '<a href="'.$globals['base_url'].'sneakme_rss2.php'.$rss_option.'" title="'._('obtener notas en rss2').'"><img src="'.$globals['base_url'].'img/common/rss-button01.png" alt="rss2"/></a>';
		echo '&nbsp;<a href="http://meneame.wikispaces.com/Notame" title="'._('jabber/google talk para leer y escribir en nótame').'"><img src="'.$globals['base_url'].'img/common/jabber-button01.png" alt="jabber"/></a>';
		echo '</div>'."\n";
		if ($current_user->user_id > 0) {
			echo '<ol class="comments-list" id="newpost"></ol>'."\n";
		}
	}


	function vote_exists() {
		global $current_user;
		require_once(mnminclude.'votes.php');
		$vote = new Vote;
		$vote->user=$current_user->user_id;
		$vote->type='posts';
		$vote->link=$this->id;
		$this->voted = $vote->exists();
		if ($this->voted) return $this->voted;
	}

	function insert_vote() {
		global $current_user;
		require_once(mnminclude.'votes.php');
		$vote = new Vote;
		$vote->user = $current_user->user_id;
		$vote->type='posts';
		$vote->link=$this->id;
		if ($vote->exists()) {
			return false;
		}
		$vote->value = $current_user->user_karma;
		if($vote->insert()) return true;
		return false;
	}
	function print_shake_icons() {
		global $globals, $current_user;
		if ( $current_user->user_karma > $globals['min_karma_for_comment_votes'] && $this->date > time() - $globals['time_enabled_votes'] && ! $this->vote_exists()) {  
		 	echo '<span id="c-votes-'.$this->id.'">';
			echo '<a href="javascript:menealo_post('."$current_user->user_id,$this->id,1".')" title="'._('voto positivo').'"><img src="'.$globals['base_url'].'img/common/vote-up01.png" width="12" height="12" alt="'._('voto positivo').'"/></a>&nbsp;&nbsp;&nbsp;';
		 	echo '<a href="javascript:menealo_post('."$current_user->user_id,$this->id,-1".')" title="'._('voto negativo').'"><img src="'.$globals['base_url'].'img/common/vote-down01.png" width="12" height="12" alt="'._('voto negativo').'"/></a>&nbsp;';
		 	echo '</span>';
		 } else {
		 	if ($this->voted > 0) {
				echo '<img src="'.$globals['base_url'].'img/common/vote-up-gy01.png" width="12" height="12" alt="'._('votado positivo').'" title="'._('votado positivo').'"/>';
			} elseif ($this->voted<0 ) {
				echo '<img src="'.$globals['base_url'].'img/common/vote-down-gy01.png" width="12" height="12" alt="'._('votado negativo').'" title="'._('votado negativo').'"/>';
			}
		}
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

		$db->query("delete from conversations where conversation_type='post' and conversation_from=$this->id");
		$references = array();
		if (preg_match_all('/(^|\s)@([\S\.\-]+[\S])/u', $this->content, $matches)) {
			foreach ($matches[2] as $reference) {
				$references[$db->escape($reference)] += 1;
			}
		}
		foreach ($references as $user => $val) {
			$to = $db->get_row("select user_id from users where user_login = '$user'");
			if ($to && $to->user_id != $this->author) {
				if (!$this->date) $this->date = time();
				$db->query("insert into conversations (conversation_user_to, conversation_type, conversation_time, conversation_from, conversation_to) values ($to->user_id, 'post', from_unixtime($this->date), $this->id, 0)");
			}
		}

	}

}
