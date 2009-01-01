<?
// The source code packaged with this file is Free Software, Copyright (C) 2008 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once(mnminclude . 'comment.php');

class CommentMobile extends Comment{
	function print_summary($link, $length = 0) {
		global $current_user, $globals;

		if(!$this->read) return;

		echo '<li id="ccontainer-'.$this->id.'">';

		$this->hidden = $this->karma < -80 || ($this->user_level == 'disabled' && $this->type != 'admin');

		if ($this->hidden)  {
			$comment_meta_class = 'comment-meta-hidden';
			$comment_class = 'comment-body-hidden';
		} else {
			$comment_meta_class = 'comment-meta';
			$comment_class = 'comment-body';
			if ($this->karma > 80) {
				$comment_class .= ' high';
			}
		}
		$this->link_permalink =  $link->get_relative_permalink();
		echo '<div class="'.$comment_class.'">';
		echo '<strong>#'.$this->order.'</strong>';

		echo '&nbsp;&nbsp;<span  id="cid-'.$this->id.'">';

		if ($this->hidden && ($current_user->user_comment_pref & 1) == 0) {
			echo '&#187;&nbsp;<a href="javascript:load_html(\'get_commentmobile.php\',\'comment\',\'cid-'.$this->id.'\',0,'.$this->id.')" title="'._('ver comentario').'">'._('ver comentario').'</a>';
		} else {
			$this->print_text($length);
		}
		echo '</span></div>';


		// The comments info bar
		echo '<div class="'.$comment_meta_class.'">';

		if ($this->type == 'admin') {
			echo '<strong>'._('admin').'</strong> ';
		} else {
			echo '<a href="'.get_user_uri($this->username).'" title="karma:&nbsp;'.$this->user_karma.'">'.$this->username.'</a> ';
		}

		echo ' ('.get_date_time($this->date).')';
		// Check that the user can vote
		if ($this->type != 'admin' && $this->user_level != 'disabled') {
			echo '&nbsp;&nbsp;' . _('votos').': <span id="vc-'.$this->id.'">'.$this->votes.'</span>, karma: <span id="vk-'.$this->id.'">'.$this->karma.'</span>';
		}

		echo '</div>';
		echo "</li>\n";
	}

	function print_text($length = 0) {
		global $current_user, $globals;

		if ($length>0 && mb_strlen($this->content) > $length + $length/2) {
			$this->content = preg_replace('/&\w*$/', '', mb_substr($this->content, 0 , $length));
			$expand = '...&nbsp;&nbsp;' .
				'<a href="javascript:load_html(\'get_commentmobile.php\',\'comment\',\'cid-'.$this->id.'\',0,'.$this->id.')" title="'._('resto del comentario').'">&#187;&nbsp;'._('ver todo el comentario').'</a>';
		}

		echo put_smileys(save_text_to_html($this->content)) . $expand;
		echo "\n";
	}

}
