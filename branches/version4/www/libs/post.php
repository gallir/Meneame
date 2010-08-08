<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once(mnminclude.'favorites.php');

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

	const SQL = " SQL_NO_CACHE post_id as id, post_user_id as author, user_login as username, user_karma, user_level as user_level, post_randkey as randkey, post_votes as votes, post_karma as karma, post_src as src, post_ip_int as ip, user_avatar as avatar, post_content as content, UNIX_TIMESTAMP(posts.post_date) as date, favorite_link_id as favorite, vote_value as voted FROM posts LEFT JOIN favorites ON (@user_id > 0 and favorite_user_id =  @user_id and favorite_type = 'post' and favorite_link_id = post_id) LEFT JOIN votes ON (@user_id > 0 and vote_type='posts' and vote_link_id = post_id and vote_user_id = @user_id), users ";

	// Regular expression to detect referencies to other post, like @user,post_id
	const REF_PREG = "/(^|\W)@([^\s<>;:,]+(?:,\d+){0,1})/u";

	static function from_db($id) {
		global $db, $current_user;
		if(($result = $db->get_object("SELECT".Post::SQL."WHERE post_id = $id and user_id = post_user_id", 'Post'))) {
			if ($result->src == 'im') {
				$result->src = 'jabber';
			}
			$result->read = true;
			return $result;
		}
		return null;
	}

	static function can_add() {
		// Check an user can add a new post
		global $globals, $current_user, $db;
		return (!$globals['min_karma_for_posts'] || $current_user->user_karma >= $globals['min_karma_for_posts'])
				&& !$db->get_var("select post_id from posts where post_user_id=$current_user->user_id and post_date > date_sub(now(), interval ".$globals['posts_period']." second) order by post_id desc limit 1") > 0;
	}

	function store($full = true) {
		require_once(mnminclude.'log.php');
		global $db, $current_user, $globals;

		$db->transaction();
		if(!$this->date) $this->date=time();
		$post_author = $this->author;
		$post_src = $this->src;
		$post_karma = $this->karma;
		$post_date = $this->date;
		$post_randkey = $this->randkey;
		$post_content = $db->escape($this->normalize_content());
		if($this->id===0) {
			$this->ip = $globals['user_ip_int'];
			$db->query("INSERT INTO posts (post_user_id, post_karma, post_ip_int, post_date, post_randkey, post_src, post_content) VALUES ($post_author, $post_karma, $this->ip, FROM_UNIXTIME($post_date), $post_randkey, '$post_src', '$post_content')");
			$this->id = $db->insert_id;

			$this->insert_vote($post_author);

			// Insert post_new event into logs
			if ($full) log_insert('post_new', $this->id, $post_author);
		} else {
			$db->query("UPDATE posts set post_user_id=$post_author, post_karma=$post_karma, post_ip_int = '$this->ip', post_date=FROM_UNIXTIME($post_date), post_randkey=$post_randkey, post_content='$post_content' WHERE post_id=$this->id");
			// Insert post_new event into logs
			if ($full) log_conditional_insert('post_edit', $this->id, $post_author, 30);
		}
		if ($full) $this->update_conversation();
		$db->commit();
	}

	function read() {
		global $db, $current_user;
		$id = $this->id;
		if(($result = $db->get_row("SELECT".Post::SQL."WHERE post_id = $id and user_id = post_user_id"))) {
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

	function print_summary_tpl($length=0)
	{
		global $current_user, $globals;
		if(!$this->read) $this->read(); 
		$this->hidden = $this->karma < $globals['post_hide_karma'] ||
				$this->user_level == 'disabled';
		$this->ignored = $current_user->user_id > 0 && User::friend_exists($current_user->user_id, $this->author) < 0;

		if ($this->hidden || $this->ignored)  {
			$post_meta_class = 'comment-meta-hidden';
			$post_class = 'comment-body-hidden';
		} else {
			$post_meta_class = 'comment-meta';
			$post_class = 'comment-body';
			if ($this->karma > $globals['post_highlight_karma']) {
				$post_class .= ' high';
			}
		}

		$this->is_disabled   = $this->ignored || ($this->hidden && ($current_user->user_comment_pref & 1) == 0);
		$this->can_vote	  = $current_user->user_id > 0 && $this->author != $current_user->user_id &&  $this->date > time() - $globals['time_enabled_votes'];
		$this->user_can_vote =  $current_user->user_karma > $globals['min_karma_for_comment_votes'] && ! $this->voted;
		$this->show_votes	= ($this->votes > 0 && $this->date > $globals['now'] - 30*86400); // Show votes if newer than 30 days

		$author = '<a href="'.post_get_base_url($this->username).'">' . ' ' . $this->username.'</a> ('.$this->src.')';
		
		// Print dates
		if ($globals['now'] - $this->date > 604800) { // 7 days
			$this->comment_info = sprintf(_('el %s %s por %s'), get_date_time($this->date), '', $author);
		} else {
			$this->comment_info = sprintf(_('hace %s %s por %s'), txt_time_diff($this->date), '', $author);
		}

		$vars = compact('post_meta_class', 'post_class', 'length');
		/* reference $this to use in the template */
		$vars['self'] = $this;

		return Haanga::Load('post_summary.html', $vars);
	}

	function print_summary($length = 0) {
		global $current_user, $globals;

		if (isset($_GET['haanga'])) {
			return $this->print_summary_tpl($length);
		}

		if(!$this->read) $this->read(); 


		$this->hidden = $this->karma < $globals['post_hide_karma'] ||
				$this->user_level == 'disabled';
		$this->ignored = $current_user->user_id > 0 && User::friend_exists($current_user->user_id, $this->author) < 0;

		echo '<div id="pcontainer-'.$this->id.'">';

		if ($this->hidden || $this->ignored)  {
			$post_meta_class = 'comment-meta-hidden';
			$post_class = 'comment-body-hidden';
		} else {
			$post_meta_class = 'comment-meta';
			$post_class = 'comment-body';
			if ($this->karma > $globals['post_highlight_karma']) {
				$post_class .= ' high';
			}
		}

		echo '<div class="'.$post_class.'" id="pid-'.$this->id.'">';


		if ($this->ignored 
				|| ($this->hidden && ($current_user->user_comment_pref & 1) == 0)) {
			echo '&#187;&nbsp;<a href="javascript:get_votes(\'get_post.php\',\'post\',\'pid-'.$this->id.'\',0,'.$this->id.')" title="'._('ver texto').'">'._('ver texto').'</a>';
		} else {
			$this->print_user_avatar(40);
			$this->print_text($length);
		}
		echo '</div>';



		// The comments info bar
		echo '<div class="'.$post_meta_class.'">';

		// Print the votes info (left)
		echo '<div class="comment-votes-info">';

		// Check that the user can vote
		if ($current_user->user_id > 0 && $this->author != $current_user->user_id 
				&&  $this->date > time() - $globals['time_enabled_votes'])
			$this->print_shake_icons();

		echo _('votos').': <span id="vc-'.$this->id.'">'.$this->votes.'</span>, '._('karma').': <span id="vk-'.$this->id.'">'.$this->karma.'</span>&nbsp;';

		// Add the icon to show votes
		if ($this->votes > 0 && $this->date > $globals['now'] - 30*86400) { // Show votes if newer than 30 days
			echo '<a href="javascript:modal_from_ajax(\''.$globals['base_url'].'backend/get_p_v.php?id='.$this->id.'\')">';
			echo '<img src="'.$globals['base_static'].'img/common/vote-info02.png" width="18" height="16" alt="+ info" title="'._('¿quién ha votado?').'"/>';
			echo '</a>';
		}

		// Reply button
		if ($current_user->user_id > 0) {
			echo '<a href="javascript:post_reply('.$this->id.',\''.$this->username.'\')" title="'._('responder').'"><img src="'.$globals['base_static'].'img/common/reply02.png" width="18" height="16"/></a>';
		}

		// Permalink
		echo '<a href="'.post_get_base_url($this->id).'" title="permalink"><img class="link-icon" src="'.$globals['base_static'].'img/common/link-02.png" width="18" height="16" alt="link" title="'._('enlace permanente').'"/></a>';

		// If the user is authenticated, show favorite box
		if ($current_user->user_id > 0)  {
			echo '<a id="fav-'.$this->id.'" href="javascript:get_votes(\'get_favorite_post.php\',\''.$current_user->user_id.'\',\'fav-'.$this->id.'\',0,\''.$this->id.'\')">'.favorite_teaser($current_user->user_id, $this, 'post').'</a>';
		}

		echo '</div>';

		// Print comment info (right)
		echo '<div class="comment-info">';
		$author = '<a href="'.post_get_base_url($this->username).'">' . ' ' . $this->username.'</a> ('.$this->src.')';
		
		// Print dates
		if ($globals['now'] - $this->date > 604800) { // 7 days
			printf(_('el %s %s por %s'), get_date_time($this->date), '', $author);
		} else {
			printf(_('hace %s %s por %s'), txt_time_diff($this->date), '', $author);
		}

		//$this->print_user_avatar(20);

		echo '</div></div>';
		echo "</div>\n";
	}

	function print_user_avatar($size=40) {
		global $globals;
		echo '<a href="'.get_user_uri($this->username).'"><img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$this->author.');" onmouseout="tooltip.clear(event);" class="avatar" src="'.get_avatar_url($this->author, $this->avatar, $size).'" width="'.$size.'" height="'.$size.'" alt="'.$this->username.'"/></a>';
	}

	function print_text($length = 0) {
		global $current_user, $globals;

		if (!$this->basic_summary && (($this->author == $current_user->user_id &&
			time() - $this->date < $globals['posts_edit_time'] ) ||
			 ($current_user->user_level == 'god' && time() - $this->date < $globals['posts_edit_time_admin'] ))) { // Admins can edit up to 10 days
			$expand = '&nbsp;&nbsp;&nbsp;<a href="javascript:post_edit('.$this->id.')" title="'._('editar').'"><img class="mini-icon-text" src="'.$globals['base_static'].'img/common/edit-misc01.png" alt="edit" width="18" height="12"/></a>';

		} elseif ($length > 0) {
			$this->content = text_to_summary($this->content, $length);
		}

		echo put_smileys($this->put_tooltips(save_text_to_html($this->content, 'posts'))) . $expand;
		echo "\n";
	}

	function put_tooltips ($str) {
		global $globals;
		// add links for hashtags
		return preg_replace_callback(Post::REF_PREG, array($this, 'replace_post_link'), $str);
	}

	function clean_content() {
		// Clean other post references
		return preg_replace('/(@[\S.-]+)(,\d+)/','$1',$this->content);
	}

	function replace_post_link($matches) {
			global $globals;

			$pre = $matches[1];
			$a = explode(',', $matches[2]);
			if (count($a) > 1) {
				$user = $a[0];
				$id = ','.$a[1];
			} else {
				$user = $matches[2];
				$id = '';
			}
			$user_url = urlencode($user);
			return "$pre<a class='tt' href='".$globals['base_url']."backend/get_post_url.php?id=$user_url$id-".$this->date."' onmouseover=\"return tooltip.ajax_delayed(event, 'get_post_tooltip.php', '$user_url".$id.'-'.$this->date."');\" onmouseout=\"tooltip.hide(event);\">@$user</a>";
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

		print_simpleformat_buttons('post');


		echo '<form action="'.$globals['base_url'].'backend/post_edit.php?user='.$current_user->user_id.'" method="post" id="thisform'.$this->id.'" name="thisform'.$this->id.'">'."\n";
		echo '<input type="hidden" name="key" value="'.$this->randkey.'" />'."\n";
		echo '<input type="hidden" name="post_id" value="'.$this->id.'" />'."\n";
		echo '<input type="hidden" name="user_id" value="'.$this->author.'" />'."\n";
		echo '<textarea style="clear: both" name="post" rows="6" cols="40" id="post" onKeyDown="textCounter(document.thisform'.$this->id.'.post,document.thisform'.$this->id.'.postcounter,'.$globals['posts_len'].')" onKeyUp="textCounter(document.thisform'.$this->id.'.post,document.thisform'.$this->id.'.postcounter,'.$globals['posts_len'].')">'.$this->content.'</textarea>'."\n";
		$body_left = $globals['posts_len'] - mb_strlen(html_entity_decode($this->content, ENT_COMPAT, 'UTF-8'), 'UTF-8');
		echo '<div style="margin-top:-7px"><input readonly type="text" name="postcounter" size="3" maxlength="3" value="'. $body_left . '" /> <span class="note">' . _('caracteres libres') . '</span></div>';
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
		if($vote->insert()) {
			if ($current_user->user_id != $this->author) {
				$db->query("update posts set post_votes=post_votes+1, post_karma=post_karma+$value, post_date=post_date where post_id=$this->id");
			}
		} else {
			$vote->value = false;
		}
		$db->commit();
		return $vote->value;
	}

	function print_shake_icons() {
		global $globals, $current_user;

		if ( $current_user->user_karma > $globals['min_karma_for_comment_votes'] && ! $this->voted) {  
		 	echo '<span id="c-votes-'.$this->id.'">';
			echo '<a href="javascript:menealo_post('."$current_user->user_id,$this->id,1".')" title="'._('voto positivo').'"><img src="'.$globals['base_static'].'img/common/vote-up02.png" width="18" height="16" alt="'._('voto positivo').'"/></a>&nbsp;';
		 	echo '<a href="javascript:menealo_post('."$current_user->user_id,$this->id,-1".')" title="'._('voto negativo').'"><img src="'.$globals['base_static'].'img/common/vote-down02.png" width="18" height="16" alt="'._('voto negativo').'"/></a>&nbsp;';
		 	echo '</span>';
		 } else {
		 	if ($this->voted > 0) {
				echo '<img src="'.$globals['base_static'].'img/common/vote-up-gy02.png" width="18" height="16" alt="'._('votado positivo').'" title="'._('votado positivo').'"/>';
			} elseif ($this->voted<0 ) {
				echo '<img src="'.$globals['base_static'].'img/common/vote-down-gy02.png" width="18" height="16" alt="'._('votado negativo').'" title="'._('votado negativo').'"/>';
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
		if (preg_match_all(Post::REF_PREG, $this->content, $matches)) {
			foreach ($matches[2] as $reference) {
				if (!$this->date) $this->date = time();
				$user = $db->escape(preg_replace('/,\d+$/', '', $reference));
				$to = $db->get_var("select user_id from users where user_login = '$user'");
				$id = intval(preg_replace('/[^\s]+,(\d+)$/', '$1', $reference));
				if (! $to > 0) continue;
				if (! $id > 0) {
					$id = (int) $db->get_var("select post_id from posts where post_user_id = $to and post_date < FROM_UNIXTIME($this->date) order by post_date desc limit 1");
				}
				if (! $references[$id]) {
					$db->query("insert into conversations (conversation_user_to, conversation_type, conversation_time, conversation_from, conversation_to) values ($to, 'post', from_unixtime($this->date), $this->id, $id)");
					$references[$id] = true;
				}
			}
		}
	}
	
	function normalize_content() {
		$this->content = clean_lines(clear_whitespace(normalize_smileys($this->content)));
		return $this->content;
	}

}


