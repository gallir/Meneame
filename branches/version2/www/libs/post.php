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
			log_insert('post_new', $this->id, $current_user->user_id);
		} else {
			$db->query("UPDATE posts set post_user_id=$post_author, post_karma=$post_karma, post_ip_int = '$this->ip', post_date=FROM_UNIXTIME($post_date), post_randkey=$post_randkey, post_content='$post_content' WHERE post_id=$this->id");
			// Insert post_new event into logs
			log_conditional_insert('post_edit', $this->id, $current_user->user_id, 30);
		}
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
		echo '<div class="'.$post_class.'">';

		echo '<a href="'.get_user_uri($this->username).'"><img onmouseover="return tooltip.ajax_delayed(event, \'get_user_info.php\', '.$this->author.');" onmouseout="tooltip.clear(event);" style="float: left; margin: 2px 10px 2px 0;" src="'.get_avatar_url($this->author, $this->avatar, 40).'" width="40" height="40" alt="'.$this->username.'"/></a>';
		echo '<span  id="cid-'.$this->id.'">';

		$this->print_text($length);
		echo '</span></div>';


		// The comments info bar
		echo '<div class="'.$post_meta_class.'">';

		// Print comment info (right)
		echo '<div class="comment-info">';
		echo '<a href="'.post_get_base_url($this->username).'">'. _('nota de') . ' ' . $this->username.'</a> ';
		if ($this->src == 'im') {
			$this->src = 'jabber';
		}
		echo '('.$this->src.') ';
		echo '(<a href="'.post_get_base_url($this->username).'/'.$this->id.'" title="permalink">#</a>) ';
		
		//echo '<a href="'.get_user_uri($this->username).'" title="karma:&nbsp;'.$this->user_karma.'">'.$this->username.'</a> ';

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
			 ($current_user->user_level == 'god' && time() - $this->date < 86400)) { // Admins can edit up to 24 hs hours
			$expand = '<br /><br />&#187;&nbsp;' . 
				'<a href="javascript:get_votes(\'post_edit.php\',\'edit_post\',\'pcontainer-'.$this->id.'\',0,'.$this->id.')" title="'._('editar').'">'._('editar').'</a>';

		}

		echo put_smileys(save_text_to_html($this->content)) . $expand;
		echo "\n";
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
		echo '<br /><input readonly type="text" name="postcounter" size="3" maxlength="3" value="'. $body_left . '" /> <span class="genericformnote">' . _('caracteres libres') . '</span>';
		echo '&nbsp;&nbsp;&nbsp;';
		echo '<input class="submit" type="submit" value="'._('guardar').'" />'."\n";
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
		echo'$(document).ready(function() { $(\'#thisform'.$this->id.'\').ajaxForm(options); });' ."\n";
		echo '</script>'."\n";
	}

	function print_new_form() {
		echo '<div id="addpost">';
		echo '<a href="javascript:get_votes(\'post_edit.php\',\'edit_comment\',\'addpost\',0,0)" title="'._('insertar una nota').'">&#187;&nbsp;'._('nueva nota').'</a><br />&nbsp;';
		echo '</div>'."\n";
		echo '<ol class="comments-list" id="newpost"></ol>'."\n";
	}

}
