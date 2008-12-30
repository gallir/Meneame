<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once(mnminclude.'link.php');

class LinkMobile extends Link{
	function print_summary($type='full', $karma_best_comment = 0, $show_tags = true) {
		global $current_user, $current_user, $globals, $db;

		if(!$this->read) return;
		if($this->is_votable()) {
			$this->voted = $this->vote_exists($current_user->user_id);
			if (!$this->voted) $this->md5 = md5($current_user->user_id.$this->id.$this->randkey.$globals['user_ip']);
		}

		echo '<div class="news-summary">';
		echo '<div class="news-body">';
		if ($globals['link']) $this->print_warn();

		if (! $globals['link']) {
			$url = $this->get_permalink();
			$nofollow = '';
		} else {
			if ($this->status != 'published') $nofollow = ' rel="nofollow"';
			else $nofollow = '';
			$url = htmlspecialchars($this->url);
		}

		if ($type != 'preview' && !empty($this->title) && !empty($this->content)) {
			$this->print_shake_box();
		}


		echo '<h1>';
		echo '<a href="'.$url.'"'.$nofollow.'>'. $this->title. '</a>';
		echo '</h1>';

		if ($globals['link']) {
			echo '<div class="news-submitted">';
			echo _('por').' <a href="'.get_user_uri($this->username, 'history').'">'.$this->username.'</a> ';
			// Print dates
			if ($globals['now'] - $this->date > 604800) { // 7 days
				if($this->status == 'published')
					echo _('publicado el').get_date_time($this->date);
				else
					echo _('el').get_date_time($this->sent_date);
			} else {
				if($this->status == 'published')
					echo _('publicado hace').txt_time_diff($this->date);
				else
					echo _('hace').txt_time_diff($this->sent_date);
			}
			echo "</div>\n";
			echo text_to_html($this->content);
		}


		echo '<div class="news-details">';
		if($this->comments > 0) {
			$comments_mess = $this->comments . ' ' . _('comentarios');
		} else  {
			$comments_mess = _('sin comentarios');
		}
		echo '<span class="comments">'.$comments_mess. '</span>';
	
		if ($globals['link']) {
			// Print meta and category
			echo ' <span class="tool">'._('en').': ';
			echo $this->meta_name.', ';
			echo $this->category_name;
			echo '</span>';
			echo ' <span class="tool">karma: <span id="a-karma-'.$this->id.'">'.intval($this->karma).'</span></span>';
		}

			echo '</div>'."\n";
			// End news details

		if ($globals['link']) {
			echo '<div class="news-details">';
			echo '<strong>'._('votos negativos').'</strong>: <span id="a-neg-'.$this->id.'">'.$this->negatives.'</span>&nbsp;&nbsp;';
			echo '<strong>'._('usuarios').'</strong>: <span id="a-usu-'.$this->id.'">'.$this->votes.'</span>&nbsp;&nbsp;';
			echo '<strong>'._('anónimos').'</strong>: <span id="a-ano-'.$this->id.'">'.$this->anonymous.'</span>&nbsp;&nbsp;';
			echo '</div>' . "\n";
		}

		echo '</div>'."\n";
		echo '</div>'."\n";

	}
	
	function print_shake_box() {
		global $current_user, $anonnymous_vote, $site_key, $globals;
		
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
		echo '<div class="news-shakeit">';
		echo '<div class="'.$box_class.'">';
		echo '<span id="a-votes-'.$this->id.'">'.($this->votes+$this->anonymous).'</span></div>';

		if ($globals['link']) {
			echo '<div class="menealo" id="a-va-'.$this->id.'">';

			if ($this->votes_enabled == false) {
				echo '<span>'._('cerrado').'</span>';
			} elseif( !$this->voted) {
				echo '<a href="javascript:menealo('."$current_user->user_id,$this->id,$this->id,"."'".$this->md5."'".')" id="a-shake-'.$this->id.'">'._('menéalo').'</a>';
			} else {
				if ($this->voted > 0) $mess = _('&#161;chachi!');
				else $mess = ':-(';
				echo '<span id="a-shake-'.$this->id.'">'.$mess.'</span>';
			}
			echo '</div>'."\n";
		}
		echo '</div>'."\n";
	}

	function print_warn() {
		global $db, $globals;

		if ($this->status == 'abuse') {
			echo '<div class="warn"><strong>'._('Aviso').'</strong>: ';
			echo _('noticia descartada por violar las').' <a href="'.$globals['legal'].'#tos">'._('normas de uso').'</a>';
			echo "</div>\n";
		} elseif ( $this->votes_enabled  && !$this->is_discarded() &&  $this->negatives > 3 && $this->negatives > $this->votes/10 ) {
			$this->warned = true;
			echo '<div class="warn"><strong>'._('Aviso automático').'</strong>: ';
			if ($this->status == 'published') {
				echo _('noticia errónea o controvertida, por favor lee los comentarios.');
			} elseif ($this->author == $current_user->user_id && $this->is_editable()) {
					echo _('Esta noticia tiene varios votos negativos.').' '._('Tu karma no será afectado si la descartas manualmente.');
			} else {
				// Only says "what" if most votes are "wrong" or "duplicated" 
				$negatives = $db->get_row("select SQL_CACHE vote_value, count(vote_value) as count from votes where vote_type='links' and vote_link_id=$this->id and vote_value < 0 group by vote_value order by count desc limit 1");
				if ($negatives->count > 2 && $negatives->count >= $this->negatives/2 && ($negatives->vote_value == -6 || $negatives->vote_value == -8)) {
					echo _('Esta noticia podría ser <strong>'). get_negative_vote($negatives->vote_value) . '</strong>. ';
				} else {
					echo _('Esta noticia tiene varios votos negativos.');
				}
				if(!$this->voted ) {
					echo ' <a href="'.$this->get_relative_permalink().'/voters">' ._('Asegúrate').'</a> ' . _('antes de menear') . '.';
				}
			}
			echo "</div>\n";
		} else {
			$this->warned = false;
		}
	}

	function print_problem_form() {
		global $current_user, $db, $anon_karma, $anonnymous_vote, $globals, $site_key;

		echo '<form  class="tool" action="" id="problem-'.$this->id.'">';
		echo '<select '.$status.' name="ratings"  onchange="';
		echo 'report_problem(this.form,'."$current_user->user_id, $this->id, "."'".$this->md5."'".')';
		echo '">';
		echo '<option value="0" selected="selected">'._('problema').'</option>';
		foreach (array_keys($globals['negative_votes_values']) as $pvalue) {
			echo '<option value="'.$pvalue.'">'.$globals['negative_votes_values'][$pvalue].'</option>';
		}
		echo '</select>';
//		echo '<input type="hidden" name="return" value="" disabled />';
		echo '</form>';
	}

}
