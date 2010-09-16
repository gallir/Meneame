<?
// The source code packaged with this file is Free Software, Copyright (C) 2008 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class CommentMobile extends Comment{
	function print_summary($link = 0, $length = 0, $single_link=true) {
		global $current_user, $globals;

		if(!$this->read) return;

		$this->check_visibility();

		if ($this->hidden)	{
			$comment_meta_class = 'comment-meta-hidden';
			$comment_class = 'comment-body-hidden';
		} else {
			$comment_meta_class = 'comment-meta';
			$comment_class = 'comment-body';
			if ($this->karma > $globals['comment_highlight_karma']) {
				$comment_class .= ' high';
			}
		}

		$this->truncate($length);

		$this->txt_content =  put_smileys(save_text_to_html($this->content));

		if ($this->type == 'admin') {
			$author = '<strong>'._('admin').'</strong> ';
		} else {
			$author = '<a href="'.get_user_uri($this->username).'" title="karma:&nbsp;'.$this->user_karma.'">'.$this->username.'</a> ';
		}

		$vars = compact('comment_meta_class', 'comment_class', 'author');
		$vars['self'] = $this;
		return Haanga::Load('mobile/comment_summary.html', $vars);
	}

}
