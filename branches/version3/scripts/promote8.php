<?
include('../config.php');
include(mnminclude.'link.php');
include(mnminclude.'user.php');
include_once(mnminclude.'log.php');
include_once(mnminclude.'ban.php');
include_once(mnminclude.'annotation.php');

header("Content-Type: text/html");
echo '<html><head><title>promote8.php</title></head><body>';
ob_end_flush();

$min_karma_coef = 0.86;
define(MAX, 1.15);
define (MIN, 1.0);
define (PUB_MIN, 20);
define (PUB_MAX, 75);
define (PUB_PERC, 0.10);


$links_queue = $db->get_var("SELECT SQL_NO_CACHE count(*) from links WHERE link_date > date_sub(now(), interval 24 hour) and link_status in ('published', 'queued')");
$links_queue_all = $db->get_var("SELECT SQL_NO_CACHE count(*) from links WHERE link_date > date_sub(now(), interval 24 hour) and link_votes > 0");


$pub_estimation = intval(max(min($links_queue * PUB_PERC, PUB_MAX), PUB_MIN));
$interval = intval(86400 / $pub_estimation);

$now = time();
$output .= "<p><b>BEGIN</b>: ".get_date_time($now)."<br/>\n";

$from_time = "date_sub(now(), interval 5 day)";
#$from_where = "FROM votes, links WHERE  


$last_published = $db->get_var("SELECT SQL_NO_CACHE UNIX_TIMESTAMP(max(link_date)) from links WHERE link_status='published'");
if (!$last_published) $last_published = $now - 24*3600*30;
$links_published = (int) $db->get_var("select count(*) from links where link_status = 'published' and link_date > date_sub(now(), interval 24 hour)");
$links_published_projection = 4 * (int) $db->get_var("select count(*) from links where link_status = 'published' and link_date > date_sub(now(), interval 6 hour)");

$diff = $now - $last_published;

$decay = min(MAX, MAX - ($diff/$interval)*(MAX-MIN) );
$decay = max($min_karma_coef, $decay);

if ($diff > $interval * 3) {
	$must_publish = true;
	$output .= "Delayed! <br/>";
}
$output .= "Last published at: " . get_date_time($last_published) ."<br/>\n";
$output .= "24hs queue: $links_queue/$links_queue_all, Published: $links_published -> $links_published_projection Published goal: $pub_estimation, Interval: $interval secs, difference: $diff secs, Decay: $decay<br/>\n";

$continue = true;
$published=0;

$past_karma_long = intval($db->get_var("SELECT SQL_NO_CACHE avg(link_karma) from links WHERE link_date >= date_sub(now(), interval 7 day) and link_status='published'"));
$past_karma_short = intval($db->get_var("SELECT SQL_NO_CACHE avg(link_karma) from links WHERE link_date >= date_sub(now(), interval 8 hour) and link_status='published'"));

$past_karma = 0.5 * max(40, $past_karma_long) + 0.5 * max($past_karma_long*0.8, $past_karma_short);
$min_past_karma = (int) ($past_karma * $min_karma_coef);


//////////////
$min_karma = round(max($past_karma * $decay, 20));

if ($decay >= 1) $max_to_publish = 3;
else $max_to_publish = 1;

$min_votes = 5;
/////////////

$limit_karma = round(min($past_karma,$min_karma) * 0.65);
$bonus_karma = round(min($past_karma,$min_karma) * 0.50);


/// Coeficients to balance metacategories
$days = 2;
$total_published = (int) $db->get_var("select count(*) from links where link_status = 'published' and link_date > date_sub(now(), interval $days day)");
$db_metas = $db->get_results("select category_id, category_name, category_calculated_coef from categories where category_parent = 0 and category_id in (select category_parent from categories where category_parent > 0)");
foreach ($db_metas as $dbmeta) {
	$meta = $dbmeta->category_id;
	$meta_previous_coef[$meta] = $dbmeta->category_calculated_coef;
	$meta_names[$meta] = $dbmeta->category_name;
	$x = (int) $db->get_var("select count(*) from links, categories where link_status = 'published' and link_date > date_sub(now(), interval $days day) and link_category = category_id and category_parent = $meta");
	$y = (int) $db->get_var("select count(*) from links, categories where link_status in ('published', 'queued') and link_date > date_sub(now(), interval $days day) and link_category = category_id and category_parent = $meta");
	$meta_coef[$meta] = $x/$y;
	$meta_coef[$meta] = 0.7 * $meta_coef[$meta] + 0.3 * $x / $total_published / count($db_metas) ;
	$meta_avg += $meta_coef[$meta] / count($db_metas);
	$output .= "$days days stats for <b>$meta_names[$meta]</b> (queued/published/total): $y/$x/$total_published -> $meta_coef[$meta]<br/>";
	//echo "$meta: $meta_coef[$meta] - $x / $y<br>";
}
foreach ($meta_coef as $m => $v) {
	$meta_coef[$m] = max(min($meta_avg/$v, 1.4), 0.7);
	if ($meta_previous_coef[$m]  > 0.6 && $meta_previous_coef[$m]  < 1.5) {
		//echo "Previous: $meta_previous_coef[$m], current: $meta_coef[$m] <br>";
		$meta_coef[$m] = 0.05 * $meta_coef[$m] + 0.95 * $meta_previous_coef[$m] ;
	}
	$output .= "Karma coefficient for <b>$meta_names[$m]</b>: $meta_coef[$m]<br/>";
	// Store current coef in DB
	$db->query("update categories set category_calculated_coef = $meta_coef[$m] where (category_id = $m || category_parent = $m)");
}


// Karma average:  It's used for each link to check the balance of users' votes

global $users_karma_avg;
$users_karma_avg = (float) $db->get_var("select avg(link_votes_avg) from links where link_status = 'published' and link_date > date_sub(now(), interval 72 hour)");

$output .= "Karma average for each link: $users_karma_avg, Past karma. Long term: $past_karma_long, Short term: $past_karma_short, Average: <b>$past_karma</b><br/>\n";
$output .= "<b>Current MIN karma: $min_karma</b>, absolute min karma: $min_past_karma, analizing from $limit_karma<br/>\n";
$output .= "</p>\n";




$where = "link_date > $from_time AND link_status = 'queued' AND link_votes>=$min_votes  AND (link_karma > $limit_karma or (link_date > date_sub(now(), interval 2 hour) and link_karma > $bonus_karma)) and user_id = link_author and category_id = link_category";
$sort = "ORDER BY link_karma DESC, link_votes DESC";

$links = $db->get_results("SELECT SQL_NO_CACHE link_id, link_karma as karma, category_parent as parent from links, users, categories where $where $sort LIMIT 30");
$rows = $db->num_rows;
if (!$rows) {
	$output .= "There are no articles<br/>\n";
	$output .= "--------------------------<br/>\n";
	die;
}
	
$max_karma_found = 0;
$best_link = 0;
$best_karma = 0;
$output .= "<table>\n";	
if ($links) {
	$output .= "<tr class='thead'><th>votes</th><th>anon</th><th>neg.</th><th>coef</th><th>karma</th><th>meta</th><th>title</th><th>changes</th></tr>\n";
	$i=0;
	foreach($links as $dblink) {
		$link = new Link;
		$link->id=$dblink->link_id;
		$link->read();
		$user = new User;
		$user->id = $link->author;
		$user->read();
		$karma_pos_user = 0;
		$karma_neg_user = 0;
		$karma_pos_ano = 0;

		$link_modified = 0;


		// Calculate the real karma for the link
		// high =~ users with higher karma greater than average
		// low =~ users with higher karma less-equal than average
		$votes_pos = $votes_neg = $karma_pos_user_high = $karma_pos_user_low = $karma_neg_user = 0;
		$votes_pos_anon = intval($db->get_var("select SQL_NO_CACHE count(*) from votes where vote_type='links' AND vote_link_id=$link->id and vote_user_id = 0 and vote_value > 0"));
		$votes = $db->get_results("select user_id, vote_value, user_karma from votes, users where vote_type='links' AND vote_link_id=$link->id and vote_user_id > 0 and vote_user_id = user_id and user_level !='disabled'");
		$affinity = check_affinity($link->author, $past_karma*0.5);
		foreach ($votes as $vote) {
			if ($vote->vote_value > 0) {
				$votes_pos++;
				if ($affinity && $affinity[$vote->user_id] > 0) {
					//echo "$vote->vote_value -> ";
					$vote->vote_value = max($vote->vote_value * $affinity[$vote->user_id]/100, 5);
					//echo "$vote->vote_value ($link->author -> $vote->user_id)\n";
				}
				if ($vote->vote_value >=  $users_karma_avg) $karma_pos_user_high += $vote->vote_value;
				else $karma_pos_user_low += $vote->vote_value;
			} else {
				$votes_neg++;
				$karma_neg_user -= $vote->user_karma;
			}
		}

		if ($link->votes != $votes_pos || $link->anonymous != $votes_pos_anon || $link->negatives != $votes_neg) {
			$link->votes = $votes_pos;
			$link->anonymous = $votes_pos_anon;
			$link->negatives = $votes_neg;
			$link_modified = 1;
			//$link->store_basic();
		}

	
		// Make sure we don't deviate too much from the average (it avoids vote spams and abuses)
		// Allowed difference up to 3%
		$karma_pos_user = (int) $karma_pos_user_high + (int) min($karma_pos_user_high * 1.07, $karma_pos_user_low);

		// Small punishment for link having too many negatives
		if (abs($karma_neg_user)/$karma_pos_user > 0.05) {
			$r = min(max(0,abs($karma_neg_user)*2/$karma_pos_user), 0.35); 
			$karma_neg_user = $karma_neg_user * pow((1+$r), 2);
		}
	
		// If the user was disabled don't count anon. votes due to abuses
		if ($user->level != 'disabled') {
			$karma_pos_ano = intval($db->get_var("select SQL_NO_CACHE sum(vote_value) from votes where vote_type='links' AND vote_link_id=$link->id and vote_user_id = 0 and vote_value > 0"));
			// To void votes spamming
			// Do not allow annonymous users to give more karma than registered users
			// The ratio up to 10% anonymous
			$karma_pos_ano = min($karma_pos_user_high*0.1, $karma_pos_ano);

		} else {
			$karma_pos_ano = 0;
		}

		// BONUS
		// Give more karma to news voted very fast during the first two hours (ish)
		if (abs($karma_neg_user)/$karma_pos_user < 0.05 && $now - $link->date < 7200 && $now - $link->date > 600) { 
			$link->new_coef = 2 - ($now-$link->date)/7200;
			// It applies the same meta coefficient to the bonus'
			// Check 1 <= bonus <= 2
			$link->new_coef = max(min($link->new_coef, 2), 1);
			// if it's has bonus and therefore time-related, use the base min_karma
			if ($decay > 1) 
				$karma_threshold = $past_karma;
			else
				$karma_threshold = $min_karma;
		} else {
			// Otherwise use normal decayed min_karma
			$karma_threshold = $min_karma;
			// Aged karma
			$diff = max(0, $now - ($link->date + 8*3600)); // 8 hours without decreasing
			$oldd = 1 - $diff/(3600*60);
			$oldd = max(0.4, $oldd);
			$oldd = min(1, $oldd);
			$link->new_coef = $oldd;
		}


		// Applies also category coefficient
		if ( $link->new_coef >= 1) {
			$karma_new = ($karma_pos_user + $karma_neg_user + $karma_pos_ano) * $link->new_coef * $meta_coef[$dblink->parent];
		} else {
			// Decay is not applied to negatives votes, so newers and "clean" are preferred
			$karma_new = $meta_coef[$dblink->parent] * (($karma_pos_user+$karma_pos_ano)*$link->new_coef + $karma_neg_user);
		}

		$dblink->karma =  $karma_new;


		$changes = 0;
		$link->message = '';

		// Verify last published from the same site
		$hours = 8;
		$min_pub_coef = 0.8;
		$last_site_published = (int) $db->get_var("select UNIX_TIMESTAMP(max(link_date)) from links where link_blog = $link->blog and link_status = 'published' and link_date > date_sub(now(), interval $hours hour)");
		if ($last_site_published > 0) {
			$pub_coef = $min_pub_coef  + ( 1- $min_pub_coef) * (time() - $last_site_published)/(3600*$hours);
			$dblink->karma *= $pub_coef;
			$link->message .= '<br/> Last published: '. intval((time() - $last_site_published)/3600) . ' hours ago.';
		}

		
		// Check domain and user punishments
		if (check_ban($link->url, 'punished_hostname', false, true)) {
			$dblink->karma *= 0.75;
			$link->message .= '<br/>' . $globals['ban_message'];
		}

		// check if it's "media" and the metacategory coefficient is low
		if ($meta_coef[$dblink->parent] < 1.1 && ($link->content_type == 'image' || $link->content_type == 'video')) {
			$dblink->karma *= 0.9;
			$link->message .= '<br/>Image/Video '.$meta_coef[$dblink->parent];
		}

		// Check if the user is banned disabled
		if(check_ban($link->url, 'hostname', false, true)) {
			$dblink->karma *= 0.66;
			$link->message .= '<br/>Domain banned. ';
		}

		// Check if the  domain is banned
		if ($user->level == 'disabled' ) {
			if (preg_match('/^_+[0-9]+_+$/', $user->username)) {
				$link->message .= "<br/>$user->username disabled herself, penalized.";
			} else {
				$link->message .= "<br/>$user->username disabled, probably due to abuses, penalized.";
			}
			$dblink->karma *= 0.66;
		}

		//echo "pos: $karma_pos_user_high, $karma_pos_user_low -> $karma_pos_user -> $dblink->karma\n";

		// check differences, if > 4 store it
		if (abs($link->karma - $dblink->karma) > 4) {
			$link->message = sprintf ("<br/>updated karma: %6d (%d, %d, %d) -> %-6d (%d, %d, %d)\n", $link->karma, $link->votes, $link->anonymous, $link->negatives, round($dblink->karma), $votes_pos, $votes_pos_anon, $votes_neg) . $link->message;
			if ($link->karma > $dblink->karma) 
				$changes = 1; // to show a "decrease" later	
			else $changes = 2; // increase
			$link->karma = round($dblink->karma);
			$link->store_basic();
		}

			
		if ($link->votes >= $min_votes && $dblink->karma >= $karma_threshold && $published < $max_to_publish) {
			$published++;
			$link->karma = round($dblink->karma);
			publish($link);
			$changes = 3; // to show a "published" later	
		} else {
			if (( $must_publish || $link->karma > $past_karma * $min_karma_coef) 
						&& $link->karma > $limit_karma && $link->karma > $last_resort_karma &&
						$link->votes > $link->negatives*10) {
				$last_resort_id = $link->id;
				$last_resort_karma = $link->karma;
			}
		}
		print_row($link, $changes);
		usleep(10000);
		$i++;
	}
	if ($published == 0 && ($must_publish || $decay < 0.99) &&  $last_resort_id  > 0) {
		// Publish last resort
		$link = new Link;
		$link->id = $last_resort_id;
		if ($link->read()) {
			$link->message = "Last resort: selected with the best karma";
			print_row($link, 3);
			publish($link);
		}
	}
	$output .= "</table>\n";
	//////////
}  


echo $output;
echo "</body></html>\n";
$annotation = new Annotation('promote');
$annotation->text = $output;
$annotation->store();

function print_row(&$link, $changes, &$log = '') {
	global $globals, $output;
	static $row = 0;

	$mod = $row%2;

	$output .= "<tr><td class='tnumber$mod'>".$link->votes."</td><td class='tnumber$mod'>".$link->anonymous."</td><td class='tnumber$mod'>".$link->negatives."</td><td class='tnumber$mod'>" . sprintf("%0.2f", $link->new_coef). "</td><td class='tnumber$mod'>".intval($link->karma)."</td>";
	$output .= "<td class='tdata$mod'>$link->meta_name</td>\n";
	$output .= "<td class='tdata$mod'><a href='".$link->get_relative_permalink()."'>$link->title</a>\n";
	if (!empty($link->message)) {
		$output .= "$link->message";
	}
	$link->message = '';
	$output .= "</td>\n";
	$output .= "<td class='tnumber$mod'>";
	switch ($changes) {
		case 1:
			$output .= '<img src="'.$globals['base_url'].'img/common/sneak-problem01.png" width="21" height="17" alt="'. _('descenso') .'"/>';
			break;
		case 2:
			$output .= '<img src="'.$globals['base_url'].'img/common/sneak-vote01.png" width="21" height="17" alt="'. _('ascenso') .'"/>';
			break;
		case 3:
			$output .= '<img src="'.$globals['base_url'].'img/common/sneak-published01.png" width="21" height="17" alt="'. _('publicada') .'"/>';
			break;
	}
	$output .= "</td>";
	$output .= "</tr>\n";
	flush();
	$row++;

}


function publish(&$link) {
	global $globals, $db;
	global $users_karma_avg;


	// Calculate votes average
	// it's used to calculate and check future averages
	$votes_avg = (float) $db->get_var("select SQL_NO_CACHE avg(vote_value) from votes, users where vote_type='links' AND vote_link_id=$link->id and vote_user_id > 0 and vote_value > 0 and vote_user_id = user_id and user_level !='disabled'");
	if ($votes_avg < $users_karma_avg) $link->votes_avg = max($votes_avg, $users_karma_avg*0.97);
	else $link->votes_avg = $votes_avg;

	$link->status = 'published';
	$link->date = $link->published_date=time();
	$link->store_basic();

	// Increase user's karma
	$user = new User;
	$user->id = $link->author;
	if ($user->read()) {
		$user->karma = min(20, $user->karma + 1);
		$user->store();
		$nnotation = new Annotation("karma-$user->id");
		$annotation->append(_('Noticia publicada').": +1, karma: $user->karma\n");
	}

	// Add the publish event/log
	log_insert('link_publish', $link->id, $link->author);

	$short_url = fon_gs($link->get_permalink());
	if ($globals['twitter_user'] && $globals['twitter_password']) {
		twitter_post($link, $short_url); 
	}
	if ($globals['jaiku_user'] && $globals['jaiku_key']) {
		jaiku_post($link, $short_url); 
	}

}
function twitter_post($link, $short_url) {
	global $globals;

	$t_status = urlencode($link->title. ' ' . $short_url);
	syslog(LOG_NOTICE, "Meneame: twitter updater called, id=$link->id");
	$t_url = "http://twitter.com/statuses/update.xml";

	if (!function_exists('curl_init')) {
		syslog(LOG_NOTICE, "Meneame: curl is not installed");
		return;
	}
	$session = curl_init();
	curl_setopt($session, CURLOPT_URL, $t_url);
	curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($session, CURLOPT_HEADER, false);
	curl_setopt($session, CURLOPT_USERAGENT, "meneame.net");
	curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 15);
	curl_setopt($session, CURLOPT_USERPWD, $globals['twitter_user'] . ":" . $globals['twitter_password']);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($session, CURLOPT_POST, 1);
	curl_setopt($session, CURLOPT_POSTFIELDS,"status=" . $t_status);
	$result = curl_exec($session);
	curl_close($session);
}


function jaiku_post($link, $short_url) {
	global $globals;

	syslog(LOG_NOTICE, "Meneame: jaiku updater called, id=$link->id");
	$url = "http://api.jaiku.com/json";

	if (!function_exists('curl_init')) {
		syslog(LOG_NOTICE, "Meneame: curl is not installed");
		return;
	}


	$postdata =  "method=presence.send";
	$postdata .= "&user=" . urlencode($globals['jaiku_user']);
	$postdata .= "&personal_key=" . $globals['jaiku_key'];
	$postdata .= "&icon=337"; // Event
	$postdata .= "&message=" . urlencode(html_entity_decode($link->title). ' ' . $short_url);

	$session = curl_init();
	curl_setopt($session, CURLOPT_URL, $url);
	curl_setopt($session, CURLOPT_HEADER, false);
	curl_setopt($session, CURLOPT_USERAGENT, "meneame.net");
	curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 15);
	curl_setopt ($session, CURLOPT_FOLLOWLOCATION,1); 
	curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($session, CURLOPT_POST, 1);
	curl_setopt($session, CURLOPT_POSTFIELDS,$postdata);
	$result = curl_exec($session);
	curl_close($session);
}

function fon_gs($url) {
	if (!function_exists('curl_init')) {
		syslog(LOG_NOTICE, "Meneame: curl is not installed");
		return $url;
	}
	$gs_url = 'http://fon.gs/create.php?url='.urlencode($url);
	$session = curl_init();
	curl_setopt($session, CURLOPT_URL, $gs_url);
	curl_setopt($session, CURLOPT_USERAGENT, "meneame.net");
	curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($session);
	curl_close($session);
	if (preg_match('/^OK/', $result)) {
		$array = explode(' ', $result);
		return $array[1];
	} else return $url;
}

function check_affinity($uid, $min_karma) {
	global $globals, $db;

	$affinity = array();
	$log = new Annotation("affinity-$uid");
	if ($log->read() && $log->time > time() - 3600*12) {
		return unserialize($log->text);
	}
	$db->query("delete from annotations where annotation_key like 'affinity-%' and annotation_time < date_sub(now(), interval 12 hour)");
	$link_ids = $db->get_col("SELECT link_id FROM links WHERE link_date > date_sub(now(), interval 30 day) and link_author = $uid and link_karma > $min_karma");
	$nlinks = count($link_ids);
	if ($nlinks < 3) {
		$log->store();
		return false;
	}

	$links = implode(',', $link_ids);
	$votes = $db->get_results("select vote_user_id as id, sum(vote_value/abs(vote_value)) as count from votes where vote_link_id in ($links) and vote_type='links' group by vote_user_id");
	if ($votes) {
		foreach ($votes as $vote) {
			if ($vote->id > 0 && $vote->id != $uid && $vote->count > max(1, $nlinks/10) ) {
				$affinity[$vote->id] = round((1 - ($vote->count/$nlinks))*100);  // store as int (percent) to save space,
			}
		}
		$log->text = serialize($affinity);
	} else {
		$affinity = false;
	}
	$log->store();
	return $affinity;

}
?>
