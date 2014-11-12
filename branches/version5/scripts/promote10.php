<?
include('../config.php');
include('utils.php');
include(mnminclude.'external_post.php');
include_once(mnminclude.'ban.php');

define('DEBUG', false);


header("Content-Type: text/html");


define(MAX, 1.15);
define (MIN, 1.0);
define (PUB_MIN, 1);
define (PUB_MAX, 75);
define (PUB_PERC, 0.15);

$sites = SitesMgr::get_active_sites();


foreach ($sites as $site) {
	echo "*** SITE: ".$site."\n"; // benjami 18-08-2012
	SitesMgr::__init($site);
	$site_info = SitesMgr::get_info($site);

	promote($site);
	if (! $site_info->sub) {
		promote_from_subs($site, 24, 80, 8);
	}
}

function promote_from_subs($destination, $hours, $min_karma, $min_votes) {
	global $db;

	echo "Promote to main: $destination\n";

	$res = $db->get_results("select sub_statuses.*, link_url, link_karma, link_votes from sub_statuses, subs, links where date > date_sub(now(), interval $hours hour) and status = 'published' and link_karma >= $min_karma and sub_statuses.id = origen and subs.id = sub_statuses.id and subs.created_from = $destination and not subs.private and not subs.nsfw and sub_statuses.id not in (select src from subs_copy where dst=$destination) and $destination not in (select id from sub_statuses as t where t.link=sub_statuses.link) and link_id = sub_statuses.link and link_votes >= $min_votes");
	foreach ($res as $status) {

		// If there are more of the same sub in queue or published, multiply minimums
		$promoted = $db->get_var("select count(*) from sub_statuses where id = $destination and origen = $status->id and date > date_sub(now(), interval $hours hour)");
		echo "PROMOTED $status->id -> $destination: $promoted\n";
		if ($promoted > 0 && $status->link_karma < min(4, $promoted*0.5 + 1) * $min_karma && $status->link_votes < min(4, $promoted*0.5 + 1) *  $min_votes) {
			echo "Already in main $promoted, doesn't have minimums, $status->link_url\n";
			continue;
		}

		$properties = SitesMgr::get_extended_properties($status->id);
		if (!empty($properties['no_link']) && empty($status->link_url)) {
			echo "NO LINK, $status->id\n";
			continue;
		}

		if (Link::duplicates($status->link_url, $destination)) {
			echo "Duplicated in destination, $status->link_url\n";
			continue;
		}
		$status->id = $destination;
		$status->status = 'queued';
		echo "--->\n";
		if (! DEBUG) SitesMgr::store($status);
	}
}

function promote($site_id) {
	global $db, $globals, $output;

	SitesMgr::__init($site_id);
	$output = '';

	$min_karma_coef = $globals['min_karma_coef'];


	$links_queue = $db->get_var("SELECT SQL_NO_CACHE count(*) from sub_statuses WHERE id = $site_id and date > date_sub(now(), interval 24 hour) and status in ('published', 'queued')");
	$links_queue_all = $db->get_var("SELECT SQL_NO_CACHE count(*) from sub_statuses, links WHERE id = $site_id and date > date_sub(now(), interval 24 hour) and link_id = link and link_votes > 0");


	$pub_estimation = intval(max(min($links_queue * PUB_PERC, PUB_MAX), PUB_MIN));
	$interval = intval(86400 / $pub_estimation);

	$now = time();
	echo "BEGIN\n";
	$output .= "<p><b>BEGIN</b>: ".get_date_time($now)."<br/>\n";


	$hours = intval($globals['time_enabled_votes']/3600);
	$from_time = "date_sub(now(), interval $hours hour)";

	$last_published = $db->get_var("SELECT SQL_NO_CACHE UNIX_TIMESTAMP(max(date)) from sub_statuses WHERE id = $site_id and status='published'");
	if (!$last_published) $last_published = $now - 24*3600*30;
	$links_published = (int) $db->get_var("select SQL_NO_CACHE count(*) from sub_statuses where id = $site_id and status = 'published' and date > date_sub(now(), interval 24 hour)");
	$links_published_projection = 4 * (int) $db->get_var("select SQL_NO_CACHE count(*) from sub_statuses where id = $site_id and status = 'published' and date > date_sub(now(), interval 6 hour)");

	$diff = $now - $last_published;
	// If published and estimation are lower than projection then
	// fasten decay
	if ($diff < $interval && ($links_published_projection < $pub_estimation * 0.9 && $links_published < $pub_estimation * 0.9 )) {
		$diff = max($diff * 2, $interval);
	}

	$decay = min(MAX, MAX - ($diff/$interval)*(MAX-MIN) );
	$decay = max($min_karma_coef, $decay);

	if ($diff > $interval * 2) {
		$must_publish = true;
		$output .= "Delayed! <br/>";
	}
	$output .= "Last published at: " . get_date_time($last_published) ."<br/>\n";
	$output .= "24hs queue: $links_queue/$links_queue_all, Published: $links_published -> $links_published_projection Published goal: $pub_estimation, Interval: $interval secs, difference: ". intval($now - $last_published)." secs, Decay: $decay<br/>\n";

	$continue = true;
	$published=0;

	$past_karma_long = intval($db->get_var("SELECT SQL_NO_CACHE avg(karma) from sub_statuses WHERE id = $site_id and date >= date_sub(now(), interval 7 day) and status='published'"));
	$past_karma_short = intval($db->get_var("SELECT SQL_NO_CACHE avg(karma) from sub_statuses WHERE id = $site_id and date >= date_sub(now(), interval 12 hour) and status='published'"));

	$past_karma = 0.5 * max(40, $past_karma_long) + 0.5 * max(20, $past_karma_short);
	$min_past_karma = (int) ($past_karma * $min_karma_coef);
	$last_resort_karma = (int) $past_karma * 0.8;


	//////////////
	$min_karma = round(max($past_karma * $decay, 20));

	if ($decay >= 1) $max_to_publish = 3;
	else $max_to_publish = 1;

	$min_votes = 3;
	/////////////

	$limit_karma = round(min($past_karma,$min_karma) * 0.40);
	$bonus_karma = round(min($past_karma,$min_karma) * 0.35);


	/// Get common votes links' averages

	$days = 7;


	// Balance metas
	if (empty($globals['sub_balance_metas']) || ! in_array(SitesMgr::my_id(), $globals['sub_balance_metas'])) {
		$db_metas = array();
	} else {
		$db_metas = $db->get_results("select category, category_name, calculated_coef from sub_categories, categories where id = $site_id and category_id = category and category_parent = 0 and category_id in (select category_parent from sub_categories, categories where id = $site_id and category_id = category and category_parent > 0)");
	}
	$subs_coef = get_subs_coef($site_id, 2);

	$globals['users_karma_avg'] = (float) $db->get_var("select SQL_NO_CACHE avg(link_votes_avg) from links, sub_statuses where id = $site_id and status = 'published' and date > date_sub(now(), interval 72 hour) and link_id = link");

	$output .= "Karma average for each link: ".$globals['users_karma_avg'].", Past karma. Long term: $past_karma_long, Short term: $past_karma_short, Average: <b>$past_karma</b><br/>\n";
	$output .= "<b>Current MIN karma: $min_karma</b>, absolute min karma: $min_past_karma, analizing from $limit_karma<br/>\n";
	$output .= "</p>\n";




	$where = "id = $site_id AND date > $from_time AND status = 'queued' AND link_id = link AND link_votes>=$min_votes AND (link_karma > $limit_karma or (date > date_sub(now(), interval 2 hour) and link_karma > $bonus_karma)) and user_id = link_author ";
	$sort = "ORDER BY link_karma DESC, link_votes DESC";

	$thumbs_queue = array();

	$links = $db->get_results("SELECT SQL_NO_CACHE link_id, link_karma as karma from sub_statuses, links, users  where $where $sort LIMIT 30");
	$rows = $db->affected_rows;
	echo "SELECTED $rows ARTICLES\n";

	if (!$rows) {
		$output .= "There are no articles<br/>\n";
		$output .= "--------------------------<br/>\n";
		echo strip_tags($output)."\n";
		if (! DEBUG) {
			$annotation = new Annotation("promote-$site_id");
			$annotation->text = $output;
			$annotation->store();
		} else {
			echo "OUTPUT:\n. ".strip_tags($output)."\n";
		}
		return;
	}

	$max_karma_found = 0;
	$best_link = 0;
	$best_karma = 0;
	$output .= "<table>\n";
	if ($links) {
		$output .= "<tr class='thead'><th>votes</th><th>anon</th><th>neg.</th><th>coef</th><th>karma</th><th>sub</th><th>title</th><th>changes</th></tr>\n";
		$i=0;
		foreach($links as $dblink) {
			$link = Link::from_db($dblink->link_id);
			$changes = update_link_karma($site_id, $link);
			
			if (! DEBUG && $link->thumb_status == 'unknown' && $link->karma > $limit_karma ) {
				echo "Adding $link->id to thumb queue\n";
				array_push($thumbs_queue, $link->id);
			}

			if (!empty($link->coef) && $link->coef > 1) {
				if ($decay > 1)
					$karma_threshold = $past_karma;
				else
					$karma_threshold = $min_karma;
			} else {
				// Otherwise use normal decayed min_karma
				$karma_threshold = $min_karma;
			}
			if ($link->votes >= $min_votes && $link->karma >= $karma_threshold && $published < $max_to_publish) {
				$published++;
				publish($site_id, $link);
				$changes = 3; // to show a "published" later
			} else {
				if (( $must_publish || $link->karma > $min_past_karma)
							&& $link->karma > $limit_karma && $link->karma > $last_resort_karma &&
							$link->votes > $link->negatives*20) {
					$last_resort_id = $link->id;
					$last_resort_karma = $link->karma;
				}
			}
			$output .= print_row($link, $changes);

			usleep(10000);
			$i++;
		}
		if (! DEBUG && $published == 0 && $links_published_projection < $pub_estimation * 0.9
				&& $must_publish && $last_resort_id  > 0) {
			// Publish last resort
			$link = new Link;
			$link->id = $last_resort_id;
			if ($link->read()) {
				$link->message = "Last resort: selected with the best karma";
				$output .= print_row($link, 3);
				publish($site_id, $link);
				// Recheck for images, some sites add images after the article has been published
				if ($link->thumb_status != 'local' && $link->thumb_status != 'remote'
						&& $link->thumb_status != 'deleted' && ! in_array($link->id, $thumbs_queue) ) {
					echo "Adding $link->id to thumb queue\n";
					array_push($thumbs_queue, $link->id);
				}
			}
		}
		//////////
	}
	$output .= "</table>\n";

	echo strip_tags($output)."\n";

	if (! DEBUG) {
		$annotation = new Annotation("promote-$site_id");
		$annotation->text = $output;
		$annotation->store();
	} else {
		echo "OUTPUT:\n".strip_tags($output)."\n";
	}

	// Get THUMBS
	foreach($thumbs_queue as $id) {
		$link = new Link;
		$link->id=$id;
		$link->read();
		echo "GETTING THUMB $link->id\n";
		$link->get_thumb(true);
		echo "DONE GETTING THUMB\n";
	}
}

function print_row($link, $changes, $log = '') {
	global $globals;
	static $row = 0;

	$mod = $row%2;

	$output = "<tr><td class='tnumber$mod'>".$link->votes."</td><td class='tnumber$mod'>".$link->anonymous."</td><td class='tnumber$mod'>".$link->negatives."</td><td class='tnumber$mod'>" . sprintf("%0.2f", $link->coef). "</td><td class='tnumber$mod'>".intval($link->karma)."</td>";
	$output .= "<td class='tdata$mod'>$link->sub_name</td>\n";
	$output .= "<td class='tdata$mod'><a href='".$link->get_relative_permalink()."/log'>$link->title</a>\n";
	if (!empty($link->message)) {
		$output .= "<br/>$link->message";
	}
	$link->message = '';
	if (DEBUG) $output .= "Annotation: $link->annotation";

	$output .= "</td>\n";
	$output .= "<td class='tnumber$mod'>";
	switch ($changes) {
		case 1:
			$output .= '<img src="'.$globals['base_static'].'img/common/sneak-problem01.png" width="21" height="17" alt="'. _('descenso') .'"/>';
			break;
		case 2:
			$output .= '<img src="'.$globals['base_static'].'img/common/sneak-vote01.png" width="21" height="17" alt="'. _('ascenso') .'"/>';
			break;
		case 3:
			$output .= '<img src="'.$globals['base_static'].'img/common/sneak-published01.png" width="21" height="17" alt="'. _('publicada') .'"/>';
			break;
	}
	$output .= "</td>";
	$output .= "</tr>\n";
	flush();
	$row++;
	return $output;
}


function publish($site, $link) {
	global $globals, $db;

	$site_info = SitesMgr::get_info($site);

	if (DEBUG) return;

	// Calculate votes average
	// it's used to calculate and check future averages
	$votes_avg = (float) $db->get_var("select SQL_NO_CACHE avg(vote_value) from votes, users where vote_type='links' AND vote_link_id=$link->id and vote_user_id > 0 and vote_value > 0 and vote_user_id = user_id and user_level !='disabled'");
	if ($votes_avg < $globals['users_karma_avg']) $link->votes_avg = max($votes_avg, $globals['users_karma_avg']*0.97);
	else $link->votes_avg = $votes_avg;

	$link->status = 'published';
	$link->date = $link->published_date=time();

	$db->transaction();
	$db->query("update links set link_status='published', link_date=now(), link_votes_avg=$link->votes_avg where link_id=$link->id");
	SitesMgr::deploy($link);
	$db->commit();

	// Increase user's karma
	$user = new User($link->author);
	if ($site_info->sub) {
		$karma_bonus = $globals['instant_karma_per_published'] / 10; // currently these published don't receive extra karma
		$log = false;
	} else {
		$karma_bonus = $globals['instant_karma_per_published'];
		$log =  _('noticia publicada');
	}
	if ($user->read) {
		$user->add_karma($karma_bonus, $log);
	}

	// Add the publish event/log
	Log::insert('link_publish', $link->id, $link->author);
	$link->annotation .= _('publicación'). "<br/>";
	$link->save_annotation('link-karma');

	// read twitter and facebok configuration from subs' extended info
	if (! $site_info->sub || $site_info->visible ) { // Only post if it's not a sub or it's visible (dmnm in mnm, f.e.)
		syslog(LOG_INFO, "Meneame, calling: ".dirname(__FILE__)."/post_link.php $site_info->name $link->id");
		passthru(dirname(__FILE__)."/post_link.php $site_info->name $link->id published");
	}

	// Publish the links of the source subs	
	if ($site_info->meta && ($senders = SitesMgr::get_senders($site))) {
		if (in_array($link->sub_id, $senders) && $link->sub_status_origen == 'queued') {
			syslog(LOG_INFO, "Meneame, publishing for sender $link->sub_name ($link->sub_id)");
			// "Simulate" the other site, needed for deploy
			SitesMgr::__init($link->sub_id);
			publish($link->sub_id, $link);
			SitesMgr::__init($site); // Back to the original site
		}
	}

	return;
}

function get_subs_coef($site_id, $days = 3) {
	global $globals, $db, $output;

	if (empty($globals['sub_balance_metas']) || ! in_array(SitesMgr::my_id(), $globals['sub_balance_metas'])) {
		return array();
	}


	/// Coeficients to balance metacategories
	$imported = $db->get_col("select src from subs_copy where dst = $site_id");
	if (empty($imported)) return array();

	$imported = implode(',', $imported);

	$totals = $db->get_results("select SQL_NO_CACHE origen, status, subs.name as name from sub_statuses, subs where sub_statuses.id = $site_id and status in ('queued', 'published') and date > date_sub(now(), interval $days day) and origen in ($imported) and origen = subs.id");
	$totals_sent = array();
	$totals_published = array();
	$names = array();
	$subs = array();

	$total_published = 0;
	$total_sent = 0;
	foreach ($totals as $sub) {
		$names[$sub->origen] = $sub->name;
		switch ($sub->status) {
			case 'published':
				$total_published++;
				$totals_published[$sub->origen]++;
			default:
				$total_sent++;
				$totals_sent[$sub->origen]++;
		}
		if (! in_array($sub->origen, $subs)) {
			$subs[] = $sub->origen;
		}

	}
	$average = $total_published / $total_sent;

	$subs_coef = array();


	foreach ($subs as $s) {
		$x = $totals_published[$s];
		$y = $totals_sent[$s];
		if ($y == 0) $y = 1;

		$c = $x/$y;

		$subs_coef[$s] = 0.7 * $c + 0.3 * $x / $total_published / count($subs);

		$output .= "$days days stats for <b>$names[$s]</b> (queued/published/total): $y/$x/$total_published -> $subs_coef[$s]<br/>";
	}

	foreach ($subs_coef as $s => $v) {
		$subs_coef[$s] = max(min($average/$v, 1.5), 0.7);
		$output .= "Karma coefficient for <b>$names[$s]</b>: $subs_coef[$s]<br/>";
	}

	// Store current coef in DB
	$log = new Annotation("subs-coef-$site_id");
	$log->text = serialize($subs_coef);
	$log->store();

	echo "DONE META SUBS\n";
	return $subs_coef;
}


function update_link_karma($site, $link) {
	global $db, $globals;
	
	if (time() - $link->time_annotation('link-karma') < 75) {
		echo "ALREADY CALCULATED $link->uri, ignoring\n";
		return 0;
	}

	
	$site_info = SitesMgr::get_info($site);
	echo "START $site_info->name WITH $link->uri\n";

	$user = new User;
	$user->id = $link->author;
	$user->read();
	
	$karma_pos_user = 0;
	$karma_neg_user = 0;
	$karma_pos_ano = 0;


	User::calculate_affinity($link->author, $past_karma*0.3);


	// Calculate the real karma for the link
	$link->calculate_karma();

	$karma_new = $link->karma;
	$link->message = '';
	$changes = 0;
	// TODO: $subs_coef is not available 
	// if (DEBUG ) $link->message .= "Sub: $link->sub_id coef: ".$subs_coef[$link->sub_id]." Init values: previous: $link->old_karma calculated: $link->karma new: $karma_new<br>\n";

	// Verify last published from the same site
	$hours = 8;
	$min_pub_coef = 0.8;
	$last_site_published = (int) $db->get_var("select SQL_NO_CACHE UNIX_TIMESTAMP(max(link_date)) from sub_statuses, links where id = $site and status = 'published' and date > date_sub(now(), interval $hours hour) and link_id = link and link_blog = $link->blog ");
	if ($last_site_published > 0) {
		$pub_coef = $min_pub_coef  + ( 1- $min_pub_coef) * (time() - $last_site_published)/(3600*$hours);
		$karma_new *= $pub_coef;
		$link->message .= 'Last published: '. intval((time() - $last_site_published)/3600) . ' hours ago.<br/>';
	}


	if(($ban = check_ban($link->url, 'hostname', false, true))) {
		// Check if the  domain is banned
		$karma_new *= 0.5;
		$link->message .= 'Domain banned.<br/>';
		$link->annotation .= _('dominio baneado').": ".$ban['comment']."<br/>";
	} elseif ($user->level == 'disabled' || $user->level == 'autodisabled' ) {
		// Check if the user is banned disabled
		if ($user->level == 'autodisabled') {
			$link->message .= "$user->username disabled herself, penalized.<br/>";
			$karma_new *= 0.2;
		} else {
			$link->message .= "$user->username disabled, probably due to abuses, penalized.<br/>";
			$karma_new *= 0.4;
		}
		$link->annotation .= _('cuenta deshabilitada'). "<br/>";
	} elseif (check_ban($link->url, 'punished_hostname', false, true)) {
		// Check domain and user punishments
		$karma_new *= 0.75;
		$link->message .= $globals['ban_message'].'<br/>';
	}

	// Check if it was depubished before

	$depublished = (int) $db->get_var("select count(*) from logs where log_type = 'link_depublished' and log_ref_id = $link->id");
	if ($depublished > 0) {
		$karma_new *= 0.4;
		$link->message .= 'Previously depublished' . '<br/>';
		$link->annotation .= _('previamente quitada de portada')."<br/>";
	}

	// Check if the are previously published during last hours from the same sub
	if ($link->sub_id > 0 && $link->is_sub && $link->sub_owner > 0 && $link->sub_id != $site && $site_info->owner == 0) {
		$sub_published = $db->get_var("select UNIX_TIMESTAMP(date) from sub_statuses where id = $site and origen = $link->sub_id and status = 'published' and date > date_sub(now(), interval 24 hour) order by date desc limit 1");
		if ($sub_published > 0) {
			$m_diff = intval((time() - $sub_published) / 60);
			$c = min(1, max(0.3, $m_diff/1440));
			$karma_new *= $c;
			$link->message .= 'Published from the same sub, c' . sprintf(': %4.2f <br/>', $c);
			$link->annotation .= _('publicada del mismo sub recientemente, coeficiente').sprintf(': %4.2f <br/>', $c);
		}
	}


	$link->karma = round($karma_new);

	/// Commons votes
	if ($link->karma > 20) {
		$days = 7;
		$commons_votes = $db->get_col("select SQL_NO_CACHE value from sub_statuses, link_commons where id = $site and status = 'published' and sub_statuses.date > date_sub(now(), interval $days day) and link_commons.link = sub_statuses.link order by value asc");
		$common = $link->calculate_common_votes();
		echo "Calculating diversity ($common-" .  count($commons_votes) . ")\n";
		if ($common != false && $commons_votes && count($commons_votes) > 5) {
			$common_probability =  cdf($commons_votes, $common);
			$p = round($common_probability, 2);
			echo "common: $common common_probability: $common_probability\n";
			$link->common_probability = $common_probability;
			$link->message .= 'Voters density: ' . sprintf("%5.2f", $common) . ' diversity coef: '.sprintf("%3.2f%%", (1-$common_probability)*100)." Probability: $p<br/>";
			$link->annotation .= _('Densidad diversidad').': '. sprintf("%5.2f", $common) . ' ' . _('coeficiente').": ".sprintf("%3.2f%%", (1-$common_probability)*100)." ("._('probabilidad').": $p)<br/>";

			// Bonus for diversity
			$c = $common_probability/0.5;
			if ($c <= 1) {
				$c = 1 - $c;
				if ($link->low_karma_perc > 50) {
					$low_karma_coef =  (100 - ($link->low_karma_perc - 50)) / 100;
				} else {
					$low_karma_coef = 1;
				}
				$bonus = round($c * 0.5 * $link->karma * $low_karma_coef * (1 - 5 * $link->negatives/$link->votes));
				echo "BONUS: $link->karma $p, $c -> $bonus ($link->low_karma_perc, $low_karma_coef, $link->negatives/$link->votes)\n";
			} else {
				// Decrease for high affinity between voters
				$c = $c - 1;
				$bonus = - round($c * 0.5 * $link->karma);
				echo "PENALIZATION: $link->karma $p, $c -> $bonus\n";
			}
			if (abs($bonus) > 10) {
				$old = $link->karma;
				$link->karma += $bonus;
				$link->annotation .= _('Karma por diversidad').": $old -> $link->karma<br/>";
			}
		}
	}

	// check differences, if > 4 store it
	if (abs($link->old_karma - $link->karma) > 6) {
		// Check percentage of low karma votes if difference > 20 (to avoid sending too many messages
		if ($link->old_karma > $link->karma + 20  && !empty($globals['adm_email']) && intval($link->low_karma_perc) >= 90 && $link->votes > 50) {
			echo "LOW KARMA WARN $link->uri\n";
			$subject = _('AVISO: enlace con muchos votos de karma menor que la media');
			$body = "Perc: $link->low_karma_perc% User votes: $link->votes Negatives: $link->negatives\n\n";
			$body .= $link->get_permalink();
			mail($globals['adm_email'], $subject, $body);
		}

		$link->message = sprintf ("updated karma: %6d (%d, %d, %d) -> %-6d<br/>\n", $link->old_karma, $link->votes, $link->anonymous, $link->negatives, $link->karma ) . $link->message;
		//$link->annotation .= _('ajuste'). ": $link->old_karma -&gt; $link->karma <br/>";
		if ($link->old_karma > $link->karma) $changes = 1; // to show a "decrease" later
		else $changes = 2; // increase
		if (! DEBUG) {
			$link->save_annotation('link-karma', $site_info->name);
			// Update relevant values
			$db->query("UPDATE links set link_karma=$link->karma, link_votes_avg=$link->votes_avg WHERE link_id=$link->id");
		} else {
			$link->message .= "To store: previous: $link->old_karma new: $link->karma<br>\n";
		}
	}
	return $changes;

}
