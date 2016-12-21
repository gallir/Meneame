<?php

final class Tabs
{

	static function renderForSection($section, $options, $tab_class)
	{
		switch ($section) {
			case _('portada'):
				return self::renderForIndex($options, $tab_class);
				break;
			case _('nuevas'):
				return self::renderForShakeIt($options, $tab_class);
				break;
			case _('populares'):
				return self::renderForTopStories($options, $tab_class);
				break;
			case _('más visitadas'):
				return self::renderForTopclicked($options, $tab_class);
				break;
			case _('nótame'):
				return self::renderForSneakme($options, $tab_class);
				break;
			case _('privados'):
				return self::renderForSneakmePrivates($options, $tab_class);
				break;
			case (_('profile') && $options == 'friends'):
				return self::renderForProfileFriends($options, $tab_class);
				break;
			case (_('profile') && $options == 'friend_of'):
				return self::renderForProfileFriendsOf($options, $tab_class);
				break;
			case (_('profile') && $options == 'ignored'):
				return self::renderForProfileIgnored($options, $tab_class);
				break;
			case (_('profile') && $options == 'friends_new'):
				return self::renderForProfileFriendsNew($options, $tab_class);
				break;
			case (_('profile') && $options == 'history'):
				return self::renderForProfileHistory($options, $tab_class);
				break;
			case (_('profile') && $options == 'shaken'):
				return self::renderForProfileShaken($options, $tab_class);
				break;
			case (_('profile') && $options == 'favorites'):
				return self::renderForProfileFavorites($options, $tab_class);
				break;
			case (_('profile') && $options == 'friends_shaken'):
				return self::renderForProfileFriendsShaken($options, $tab_class);
				break;
			case (_('profile') && $options == 'commented'):
				return self::renderForProfileCommented($options, $tab_class);
				break;
			case (_('profile') && $options == 'conversation'):
				return self::renderForProfileConversation($options, $tab_class);
				break;
			case (_('profile') && $options == 'shaken_comments'):
				return self::renderForProfileShakenComments($options, $tab_class);
				break;
			case (_('profile') && $options == 'favorite_comments'):
				return self::renderForProfileFavoriteComments($options, $tab_class);
				break;
			default:
				return false;
				break;
		}
	}

	static function renderForIndex($option, $tab_class)
	{
		global $globals, $current_user;

		if (($globals['mobile'] && !$current_user->has_subs) || (!empty($globals['submnm']) && !$current_user->user_id)) {
			return;
		}

		$items = array();
		$items[] = array('id' => 0, 'url' => $globals['meta_skip'], 'title' => _('todas'));

		if (isset($current_user->has_subs)) {
			$items[] = array('id' => 7, 'url' => $globals['meta_subs'], 'title' => _('suscripciones'));
		}

		if (!$globals['mobile'] && empty($globals['submnm']) && ($subs = SitesMgr::get_sub_subs())) {
			foreach ($subs as $sub) {
				$items[] = array(
					'id' => 9999, /* fake number */
					'url' => 'm/' . $sub->name,
					'selected' => false,
					'title' => $sub->name,
				);
			}
		}

		$items[] = array('id' => 8, 'url' => '?meta=_*', 'title' => _('m/*'));

		// RSS teasers
		switch ($option) {
			case 7: // Personalised, published
				$feed = array("url" => "?subs=" . $current_user->user_id, "title" => _('suscripciones'));
				break;
			default:
				$feed = array("url" => '', "title" => "");
				break;
		}

		if ($current_user->user_id > 0) {
			$items[] = array('id' => 1, 'url' => '?meta=_friends', 'title' => _('amigos'));
		}

		return Haanga::Load('print_tabs.html', compact('items', 'option', 'feed', 'tab_class'), true);
	}


	public static function renderForShakeIt($option = -1, $tab_class)
	{
		global $globals, $current_user;

		$items = array();
		$items[] = array('id' => 1, 'url' => 'queue' . $globals['meta_skip'], 'title' => _('todas'));
		if ($current_user->has_subs) {
			$items[] = array('id' => 7, 'url' => 'queue' . $globals['meta_subs'], 'title' => _('suscripciones'));
		}

		if (empty($globals['submnm']) && !$globals['mobile']) {
			$subs = SitesMgr::get_sub_subs();
			foreach ($subs as $sub) {
				$items[] = array(
					'id' => 9999, /* fake number */
					'url' => 'm/' . $sub->name . '/queue',
					'selected' => false,
					'title' => $sub->name
				);
			}
		}

		$items [] = array('id' => 8, 'url' => 'queue?meta=_*', 'title' => _('m/*'));

		$items[] = array('id' => 3, 'url' => 'queue?meta=_popular', 'title' => _('candidatas'));

		if ($current_user->user_id > 0) {
			$items[] = array('id' => 2, 'url' => 'queue?meta=_friends', 'title' => _('amigos'));
		}

		if (!$globals['bot']) {
			$items [] = array('id' => 5, 'url' => 'queue?meta=_discarded', 'title' => _('descartadas'));
		}

		// Print RSS teasers
		if (!$globals['mobile']) {
			switch ($option) {
				case 7: // Personalised, queued
					$feed = array("url" => "?status=queued&amp;subs=" . $current_user->user_id, "title" => "");
					break;
				default:
					$feed = array("url" => "?status=queued", "title" => "");
					break;
			}
		}
		$vars = compact('items', 'option', 'feed', 'tab_class');
		return Haanga::Load('print_tabs.html', $vars, true);
	}

	public static function renderForTopstories($options, $tab_class)
	{
		global $range_values, $range_names, $month, $year;

		$html = '';

		if (!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) $current_range = 0;
		$html .= '<ul class="' . $tab_class . '">' . "\n";
		if ($month > 0 && $year > 0) {
			$html .= '<li class="selected"><a href="popular?month=' . $month . '&amp;year=' . $year . '">' . "$month-$year" . '</a></li>' . "\n";
			$current_range = -1;
		} elseif (!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) {
			$current_range = 0;
		}

		for ($i = 0; $i < count($range_values) /* && $range_values[$i] < 60 */; $i++) {
			if ($i == $current_range) {
				$active = ' class="selected"';
			} else {
				$active = "";
			}
			$html .= '<li' . $active . '><a href="popular?range=' . $i . '">' . $range_names[$i] . '</a></li>' . "\n";
		}
		$html .= '</ul>' . "\n";

		return $html;
	}

	public static function renderForTopclicked($options, $tab_class)
	{
		global $range_values, $range_names;

		$html = '';

		$html .= '<ul class="' . $tab_class . '">' . "\n";
		if (!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) {
			$current_range = 0;
		}

		for ($i = 0; $i < count($range_values) /* && $range_values[$i] < 60 */; $i++) {
			if ($i == $current_range) {
				$active = ' class="selected"';
			} else {
				$active = "";
			}
			$html .= '<li' . $active . '><a href="top_visited?range=' . $i . '">' . $range_names[$i] . '</a></li>' . "\n";
		}
		$html .= '</ul>' . "\n";

		return $html;
	}

	public static function renderForSneakme($options, $tab_class)
	{
		global $globals, $current_user;

		list($content, $selected, $rss, $rss_title) = $options;

		$html = '';

		// arguments: hash array with "button text" => "button URI"; Nº of the selected button
		$html .= '<ul class="' . $tab_class . '">' . "\n";

		if (is_array($content)) {
			$n = 0;
			foreach ($content as $text => $url) {
				if ($selected === $n) $class_b = ' class = "selected"';
				else {
					if ($n > 4) $class_b = ' class="wideonly"';
					else $class_b = '';
				}
				$html .= '<li' . $class_b . '>' . "\n";
				$html .= '<a href="' . $url . '">' . $text . "</a>\n";
				$html .= '</li>' . "\n";
				$n++;
			}
		} elseif (!empty($content)) {
			$html .= '<li>' . $content . '</li>';
		}

		if ($rss && !empty ($content)) {
			if (!$rss_title) $rss_title = 'rss2';
		}
		$html .= '<li class="icon wideonly"><a href="' . $globals['base_url'] . $rss . '" title="' . $rss_title . '"><i class="fa fa-rss-square"></i></a></li>';

		$html .= '</ul>' . "\n";

		return $html;
	}

	public static function renderForSneakmePrivates($options, $tab_class)
	{

		list($content, $selected) = $options;

		$html = '';

		// arguments: hash array with "button text" => "button URI"; Nº of the selected button
		$html .= '<ul class="' . $tab_class . '">' . "\n";

		if (is_array($content)) {
			$n = 0;
			foreach ($content as $text => $url) {
				if ($selected === $n) $class_b = ' class = "selected"';
				else $class_b = '';
				$html .= '<li' . $class_b . '>' . "\n";
				$html .= '<a href="' . $url . '">' . $text . "</a>\n";
				$html .= '</li>' . "\n";
				$n++;
			}
		} elseif (!empty($content)) {
			$html .= '<li>' . $content . '</li>';
		}
		$html .= '</ul>' . "\n";

		return $html;
	}

	public static function renderForProfileFriends($view, $tab_class)
	{
		global $user, $current_user;

		$options = array(_('amigos') => get_user_uri($user->username, 'friends'), _('elegido por') => get_user_uri($user->username, 'friend_of'));
		if ($user->id == $current_user->user_id) {
			$options[_('ignorados')] = get_user_uri($user->username, 'ignored');
			$options[_('nuevos')] = get_user_uri($user->username, 'friends_new');
		}

		return self::renderUserProfileSubheader($options, 0, 'rss?friends_of=' . $user->id, _('envíos de amigos en rss2'), $tab_class);
	}

	public static function renderUserProfileSubheader($options, $selected = false, $rss = false, $rss_title = '', $tab_class)
	{

		global $user, $current_user;

		if ($current_user->user_id > 0 && $user->id != $current_user->user_id) { // Add link to discussion among them
			$between = "type=comments&amp;u1=$user->username&amp;u2=$current_user->user_login";
		} else {
			$between = false;
		}

		// arguments: hash array with "button text" => "button URI"; Nº of the selected button

		$vars = compact(
			'options', 'selected', 'rss', 'rss_title', 'between', 'tab_class'
		);

		return Haanga::Load('user/subheader.html', $vars, true);
	}

	public static function renderForProfileFriendsOf($view, $tab_class)
	{

		global $user, $current_user;

		$options = array(_('amigos') => get_user_uri($user->username, 'friends'), _('elegido por') => get_user_uri($user->username, 'friend_of'));
		if ($user->id == $current_user->user_id) {
			$options[_('ignorados')] = get_user_uri($user->username, 'ignored');
			$options[_('nuevos')] = get_user_uri($user->username, 'friends_new');
		}
		return self::renderUserProfileSubheader($options, 1, false, '', $tab_class);
	}

	public static function renderForProfileIgnored($view, $tab_class)
	{
		global $user, $current_user;

		$options = array(_('amigos') => get_user_uri($user->username, 'friends'), _('elegido por') => get_user_uri($user->username, 'friend_of'));
		if ($user->id == $current_user->user_id) {
			$options[_('ignorados')] = get_user_uri($user->username, 'ignored');
			$options[_('nuevos')] = get_user_uri($user->username, 'friends_new');
		}
		return self::renderUserProfileSubheader($options, 2, false, '', $tab_class);
	}

	public static function renderForProfileFriendsNew($view, $tab_class)
	{

		global $user, $current_user;

		$options = array(_('amigos') => get_user_uri($user->username, 'friends'), _('elegido por') => get_user_uri($user->username, 'friend_of'));
		if ($user->id == $current_user->user_id) {
			$options[_('ignorados')] = get_user_uri($user->username, 'ignored');
			$options[_('nuevos')] = get_user_uri($user->username, 'friends_new');
		}
		return self::renderUserProfileSubheader($options, 3, false, '', $tab_class);
	}

	public static function renderForProfileHistory($view, $tab_class)
	{
		global $user;

		return self::renderUserProfileSubheader(array(_('envíos propios') => get_user_uri($user->username, 'history'), _('votados') => get_user_uri($user->username, 'shaken'), _('favoritos') => get_user_uri($user->username, 'favorites'), _('votados por amigos') => get_user_uri($user->username, 'friends_shaken')), 0,
			'rss?sent_by=' . $user->id, _('envíos en rss2'), $tab_class);
	}

	public static function renderForProfileShaken($view, $tab_class)
	{
		global $user;

		return self::renderUserProfileSubheader(array(_('envíos propios') => get_user_uri($user->username, 'history'), _('votados') => get_user_uri($user->username, 'shaken'), _('favoritos') => get_user_uri($user->username, 'favorites'), _('votados por amigos') => get_user_uri($user->username, 'friends_shaken')), 1,
			'rss?voted_by=' . $user->id, _('votadas en rss2'), $tab_class);

	}

	public static function renderForProfileFavorites($view, $tab_class)
	{
		global $user;

		return self::renderUserProfileSubheader(array(_('envíos propios') => get_user_uri($user->username, 'history'), _('votados') => get_user_uri($user->username, 'shaken'), _('favoritos') => get_user_uri($user->username, 'favorites'), _('votados por amigos') => get_user_uri($user->username, 'friends_shaken')), 2,
			'rss?voted_by=' . $user->id, _('votadas en rss2'), $tab_class);

	}

	public static function renderForProfileFriendsShaken($view, $tab_class)
	{
		global $user;

		return self::renderUserProfileSubheader(array(_('envíos propios') => get_user_uri($user->username, 'history'), _('votados') => get_user_uri($user->username, 'shaken'), _('favoritos') => get_user_uri($user->username, 'favorites'), _('votados por amigos') => get_user_uri($user->username, 'friends_shaken')), 3, false, '', $tab_class);
	}

	public static function renderForProfileCommented($view, $tab_class)
	{
		global $user, $globals;

		return self::renderUserProfileSubheader(array($user->username => get_user_uri($user->username, 'commented'), _('conversación') . $globals['extra_comment_conversation'] => get_user_uri($user->username, 'conversation'), _('votados') => get_user_uri($user->username, 'shaken_comments'), _('favoritos') => get_user_uri($user->username, 'favorite_comments')), 0,
			'comments_rss?user_id=' . $user->id, _('comentarios en rss2'), $tab_class);

	}

	public static function renderForProfileShakenComments($view, $tab_class)
	{
		global $user, $globals;

		return self::renderUserProfileSubheader(array($user->username => get_user_uri($user->username, 'commented'), _('conversación') . $globals['extra_comment_conversation'] => get_user_uri($user->username, 'conversation'), _('votados') => get_user_uri($user->username, 'shaken_comments'), _('favoritos') => get_user_uri($user->username, 'favorite_comments')), 2, false, '', $tab_class);
	}

	public static function renderForProfileFavoriteComments($view, $tab_class)
	{
		global $user, $globals;

		return self::renderUserProfileSubheader(array($user->username => get_user_uri($user->username, 'commented'), _('conversación') . $globals['extra_comment_conversation'] => get_user_uri($user->username, 'conversation'), _('votados') => get_user_uri($user->username, 'shaken_comments'), _('favoritos') => get_user_uri($user->username, 'favorite_comments')), 3, false, '', $tab_class);
	}


}