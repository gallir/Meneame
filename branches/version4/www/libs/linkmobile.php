<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class LinkMobile extends Link{
	function print_summary($type='full', $karma_best_comment = 0, $show_tags = true) {
		global $current_user, $current_user, $globals, $db;

		if(!$this->read) return;
		if($this->is_votable()) {
			$this->voted = $this->vote_exists($current_user->user_id);
		}

		switch ($this->status) {
			case 'queued': // another color box for not-published
				$box_class = 'mnm-queued';
				break;
			case 'abuse': // another color box for discarded
			case 'autodiscard': // another color box for discarded
			case 'discard': // another color box for discarded
				$box_class = 'mnm-discarded';
				break;
			case 'published': // default for published
			default:
				$box_class = 'mnm-published';
				break;
		}

		$this->has_warning = !(!$this->check_warn() || $this->is_discarded());
		$this->is_editable = $this->author == $current_user->user_id && $this->is_editable();
		$this->total_votes = $this->votes+$this->anonymous;
		$this->rpermalink  = $this->get_relative_permalink();
		$this->author_html = '<a href="'.get_user_uri($this->username, 'history').'">'.$this->username.'</a>';
		$this->normal_link = 'http://'.preg_replace('/(\.|^)m\./', '$1', get_server_name()).$this->get_relative_permalink();
		$this->show_shakebox = $type != 'preview' && $this->votes > 0;


        if ($this->status == 'abuse' || $this->has_warning) {
            $this->negative_text = FALSE;
			$negatives = $db->get_row("select SQL_CACHE vote_value, count(vote_value) as count from votes where vote_type='links' and vote_link_id=$this->id and vote_value < 0 group by vote_value order by count desc limit 1");

			if ($negatives->count > 2 && $negatives->count >= $this->negatives/2 && ($negatives->vote_value == -6 || $negatives->vote_value == -8)) {
                $this->negative_text = get_negative_vote($negatives->vote_value);
            }
        }

        $text      = $this->content;
		$my_domain = get_server_name();
		$parent_domain = preg_replace('/m\./', '', $my_domain);
		if ($parent_domain != $my_domain && preg_match('#[^\.]'.preg_quote($parent_domain).'/#', $text)) {
			$text = preg_replace('#([^\.])'.preg_quote($parent_domain).'/#', "$1$my_domain/", $text);
		}

        $vars = compact('type', 'karma_best_comment', 'show_tags', 'box_class', 'nofollow', 'url', 'text');
        $vars['self'] = $this;
        return Haanga::Load('mobile/link_summary.html', $vars);
    }

	function print_problem_form() {
		global $current_user, $db, $anon_karma, $anonnymous_vote, $globals, $site_key;

		echo '<form  class="tool" action="" id="problem-'.$this->id.'">';
		echo '<select '.$status.' name="ratings"  onchange="';
		echo 'report_problem(this.form,'."$current_user->user_id, $this->id".')';
		echo '">';
		echo '<option value="0" selected="selected">'._('problema').'</option>';
		foreach (array_keys($globals['negative_votes_values']) as $pvalue) {
			echo '<option value="'.$pvalue.'">'.$globals['negative_votes_values'][$pvalue].'</option>';
		}
		echo '</select>';
//		echo '<input type="hidden" name="return" value="" disabled />';
		echo '</form>';
	}
	function print_warn() {
		global $db, $globals;


		if ($this->status == 'abuse') {
			echo '<div class="warn"><strong>'._('Aviso').'</strong>: ';
			echo _('noticia descartada por violar las').' <a href="'.$globals['legal'].'#tos">'._('normas de uso').'</a>';
			echo "</div>\n";
			return;
		}
		if (!$this->check_warn() || $this->is_discarded()) return;


		echo '<div class="warn"><strong>'._('Aviso automático').'</strong>: ';
		if ($this->status == 'published') {
			echo _('noticia errónea o controvertida, por favor lee los comentarios.');
		} elseif ($this->author == $current_user->user_id && $this->is_editable()) {
				echo _('Esta noticia tiene varios votos negativos.').' '._('Tu karma no será afectado si la descartas manualmente.');
		} else {
			// Only says "what" if most votes are "wrong" or "duplicated"
			$negatives = $db->get_row("select SQL_CACHE vote_value, count(vote_value) as count from votes where vote_type='links' and vote_link_id=$this->id and vote_value < 0 group by vote_value order by count desc limit 1");
			if ($negatives->count > 2 && $negatives->count >= $this->negatives/2 && ($negatives->vote_value == -6 || $negatives->vote_value == -8)) {
				echo _('Esta noticia podría ser').' <strong>'. get_negative_vote($negatives->vote_value) . '</strong>. ';
			} else {
				echo _('Esta noticia tiene varios votos negativos.');
			}
			if(!$this->voted && ! $globals['link']) {
				echo ' <a href="'.$this->get_relative_permalink().'">' ._('Asegúrate').'</a> ' . _('antes de menear') . '.';
			}
		}
		echo "</div>\n";
	}

}
