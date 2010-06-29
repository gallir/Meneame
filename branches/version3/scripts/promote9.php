<?
include('../config.php');
include(mnminclude.'external_post.php');
include_once(mnminclude.'log.php');
include_once(mnminclude.'ban.php');

define('DEBUG', false);

header("Content-Type: text/html");
echo '<html><head><title>promote9.php</title></head><body>';
ob_end_flush();

$min_karma_coef = 0.87;


define(MAX, 1.15);
define (MIN, 1.0);
define (PUB_MIN, 20);
define (PUB_MAX, 75);
define (PUB_PERC, 0.11);



$links_queue = $db->get_var("SELECT SQL_NO_CACHE count(*) from links WHERE link_date > date_sub(now(), interval 24 hour) and link_status in ('published', 'queued')");
$links_queue_all = $db->get_var("SELECT SQL_NO_CACHE count(*) from links WHERE link_date > date_sub(now(), interval 24 hour) and link_votes > 0");


$pub_estimation = intval(max(min($links_queue * PUB_PERC, PUB_MAX), PUB_MIN));
$interval = intval(86400 / $pub_estimation);

$now = time();
echo "BEGIN\n";
$output .= "<p><b>BEGIN</b>: ".get_date_time($now)."<br/>\n";

$from_time = "date_sub(now(), interval 5 day)";
#$from_where = "FROM votes, links WHERE  


$last_published = $db->get_var("SELECT SQL_NO_CACHE UNIX_TIMESTAMP(max(link_date)) from links WHERE link_status='published'");
if (!$last_published) $last_published = $now - 24*3600*30;
$links_published = (int) $db->get_var("select SQL_NO_CACHE count(*) from links where link_status = 'published' and link_date > date_sub(now(), interval 24 hour)");
$links_published_projection = 4 * (int) $db->get_var("select SQL_NO_CACHE count(*) from links where link_status = 'published' and link_date > date_sub(now(), interval 6 hour)");

$diff = $now - $last_published;
// If published and estimation are lower than projection then
// fasten decay
if ($diff < $interval && ($links_published_projection < $pub_estimation * 0.9 && $links_published < $pub_estimation * 0.9 )) {
	$diff = max($diff * 2, $interval);
}

$decay = min(MAX, MAX - ($diff/$interval)*(MAX-MIN) );
/*
if ($decay > MIN && ($links_published_projection < $pub_estimation * 0.9 || $links_published < $pub_estimation * 0.9)) {
	$decay = MIN;
}
*/
$decay = max($min_karma_coef, $decay);

if ($diff > $interval * 2) {
	$must_publish = true;
	$output .= "Delayed! <br/>";
}
$output .= "Last published at: " . get_date_time($last_published) ."<br/>\n";
$output .= "24hs queue: $links_queue/$links_queue_all, Published: $links_published -> $links_published_projection Published goal: $pub_estimation, Interval: $interval secs, difference: ". intval($now - $last_published)." secs, Decay: $decay<br/>\n";

$continue = true;
$published=0;

$past_karma_long = intval($db->get_var("SELECT SQL_NO_CACHE avg(link_karma) from links WHERE link_date >= date_sub(now(), interval 7 day) and link_status='published'"));
$past_karma_short = intval($db->get_var("SELECT SQL_NO_CACHE avg(link_karma) from links WHERE link_date >= date_sub(now(), interval 12 hour) and link_status='published'"));

$past_karma = 0.5 * max(40, $past_karma_long) + 0.5 * max($past_karma_long*0.8, $past_karma_short);
$min_past_karma = (int) ($past_karma * $min_karma_coef);
$last_resort_karma = (int) $past_karma * 0.8;


//////////////
$min_karma = round(max($past_karma * $decay, 20));

if ($decay >= 1) $max_to_publish = 3;
else $max_to_publish = 1;

$min_votes = 5;
/////////////

$limit_karma = round(min($past_karma,$min_karma) * 0.60);
$bonus_karma = round(min($past_karma,$min_karma) * 0.40);


/// Coeficients to balance metacategories
$days = 2;
$total_published = (int) $db->get_var("select SQL_NO_CACHE count(*) from links where link_status = 'published' and link_date > date_sub(now(), interval $days day)");
$db_metas = $db->get_results("select category_id, category_name, category_calculated_coef from categories where category_parent = 0 and category_id in (select category_parent from categories where category_parent > 0)");
foreach ($db_metas as $dbmeta) {
	$meta = $dbmeta->category_id;
	$meta_previous_coef[$meta] = $dbmeta->category_calculated_coef;
	$meta_names[$meta] = $dbmeta->category_name;
	$x = (int) $db->get_var("select SQL_NO_CACHE count(*) from links, categories where link_status = 'published' and link_date > date_sub(now(), interval $days day) and link_category = category_id and category_parent = $meta");
	$y = (int) $db->get_var("select SQL_NO_CACHE count(*) from links, categories where link_status in ('published', 'queued') and link_date > date_sub(now(), interval $days day) and link_category = category_id and category_parent = $meta");
	if ($y == 0) $y = 1;
	$meta_coef[$meta] = $x/$y;
	if ($total_published == 0) $total_published = 1;
	$meta_coef[$meta] = 0.7 * $meta_coef[$meta] + 0.3 * $x / $total_published / count($db_metas) ;
	$meta_avg += $meta_coef[$meta] / count($db_metas);
	$output .= "$days days stats for <b>$meta_names[$meta]</b> (queued/published/total): $y/$x/$total_published -> $meta_coef[$meta]<br/>";
	//echo "$meta: $meta_coef[$meta] - $x / $y<br>";
}
foreach ($meta_coef as $m => $v) {
	if ($v == 0) $v = 1;
	$meta_coef[$m] = max(min($meta_avg/$v, 1.5), 0.7);
	if ($meta_previous_coef[$m]  > 0.6 && $meta_previous_coef[$m]  < 1.5) {
		//echo "Previous: $meta_previous_coef[$m], current: $meta_coef[$m] <br>";
		$meta_coef[$m] = 0.05 * $meta_coef[$m] + 0.95 * $meta_previous_coef[$m] ;
	}
	$output .= "Karma coefficient for <b>$meta_names[$m]</b>: $meta_coef[$m]<br/>";
	// Store current coef in DB
	if (! DEBUG) {
		$db->query("update categories set category_calculated_coef = $meta_coef[$m] where (category_id = $m || category_parent = $m)");
	}
	$log = new Annotation("metas-coef");
	$log->text = serialize($meta_coef);
	$log->store();
}

echo "DONE METAS\n";
// Karma average:  It's used for each link to check the balance of users' votes

$globals['users_karma_avg'] = (float) $db->get_var("select SQL_NO_CACHE avg(link_votes_avg) from links where link_status = 'published' and link_date > date_sub(now(), interval 72 hour)");

$output .= "Karma average for each link: ".$globals['users_karma_avg'].", Past karma. Long term: $past_karma_long, Short term: $past_karma_short, Average: <b>$past_karma</b><br/>\n";
$output .= "<b>Current MIN karma: $min_karma</b>, absolute min karma: $min_past_karma, analizing from $limit_karma<br/>\n";
$output .= "</p>\n";




$where = "link_date > $from_time AND link_status = 'queued' AND link_votes>=$min_votes  AND (link_karma > $limit_karma or (link_date > date_sub(now(), interval 2 hour) and link_karma > $bonus_karma)) and user_id = link_author and category_id = link_category";
$sort = "ORDER BY link_karma DESC, link_votes DESC";

$thumbs_queue = array();

$links = $db->get_results("SELECT SQL_NO_CACHE link_id, link_karma as karma, category_parent as parent from links, users, categories where $where $sort LIMIT 30");
$rows = $db->affected_rows;
echo "SELECTED $rows ARTICLES\n";
if (!$rows) {
	$output .= "There are no articles<br/>\n";
	$output .= "--------------------------<br/>\n";
	echo $output;
	echo "</body></html>\n";
	if (! DEBUG) {
		$annotation = new Annotation('promote');
		$annotation->text = $output;
		$annotation->store();
	} else {
		echo "OUTPUT:\n$output\n";
	}
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
		$db->transaction();
		$link->read();
		echo "START WITH $link->uri\n";
		$user = new User;
		$user->id = $link->author;
		$user->read();
		$karma_pos_user = 0;
		$karma_neg_user = 0;
		$karma_pos_ano = 0;


		User::calculate_affinity($link->author, $past_karma*0.3);

		// Calculate the real karma for the link
		$link->calculate_karma();

		if ($link->coef > 1) {
			if ($decay > 1) 
				$karma_threshold = $past_karma;
			else
				$karma_threshold = $min_karma;
		} else {
			// Otherwise use normal decayed min_karma
			$karma_threshold = $min_karma;
		}


		//$karma_new = $link->karma * $meta_coef[$dblink->parent];
		$karma_new = $link->karma;
		$link->message = '';
		$changes = 0;
		if (DEBUG ) $link->message .= "Meta: $link->meta_id coef: ".$meta_coef[$link->meta_id]." Init values: previous: $link->old_karma calculated: $link->karma new: $karma_new<br>\n";

		// Verify last published from the same site
		$hours = 8;
		$min_pub_coef = 0.8;
		$last_site_published = (int) $db->get_var("select SQL_NO_CACHE UNIX_TIMESTAMP(max(link_date)) from links where link_blog = $link->blog and link_status = 'published' and link_date > date_sub(now(), interval $hours hour)");
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
		} elseif ($user->level == 'disabled' ) {
			// Check if the user is banned disabled
			if (preg_match('/^_+[0-9]+_+$/', $user->username)) {
				$link->message .= "$user->username disabled herself, penalized.<br/>";
			} else {
				$link->message .= "$user->username disabled, probably due to abuses, penalized.<br/>";
			}
			$karma_new *= 0.5;
			$link->annotation .= _('cuenta deshabilitada'). "<br/>";
		} elseif (check_ban($link->url, 'punished_hostname', false, true)) {
			// Check domain and user punishments
			$karma_new *= 0.75;
			$link->message .= $globals['ban_message'].'<br/>';
		} elseif ($meta_coef[$dblink->parent] < 1 && ($link->content_type == 'image')) {
			// check if it's "media" and the metacategory coefficient is low
			$karma_new *= 0.9;
			$link->message .= 'Image/Video '.$meta_coef[$dblink->parent].'<br/>';
		}

		$link->karma = round($karma_new);

		// check differences, if > 4 store it
		if (abs($link->old_karma - $link->karma) > 6) {
			$link->message = sprintf ("updated karma: %6d (%d, %d, %d) -> %-6d<br/>\n", $link->old_karma, $link->votes, $link->anonymous, $link->negatives, $link->karma ) . $link->message;
			//$link->annotation .= _('ajuste'). ": $link->old_karma -&gt; $link->karma <br/>";
			if ($link->old_karma > $link->karma) $changes = 1; // to show a "decrease" later	
			else $changes = 2; // increase
			if (! DEBUG) {
				$link->store_basic();
				$link->save_annotation('link-karma');
			} else {
				$link->message .= "To store: previous: $link->old_karma new: $link->karma<br>\n";
			}
		}
		$db->commit();

		echo "THUMB: $link->uri $link->thumb_status $link->karma > $limit_karma\n";
		if (! DEBUG && $link->thumb_status == 'unknown' && $link->karma > $limit_karma ) {
			echo "Adding $link->id to thumb queue\n";
			array_push($thumbs_queue, $link->id);
		}

		if ($link->votes >= $min_votes && $karma_new >= $karma_threshold && $published < $max_to_publish) {
			$published++;
			publish($link);
			$changes = 3; // to show a "published" later	
		} else {
			if (( $must_publish || $link->karma > $min_past_karma) 
						&& $link->karma > $limit_karma && $link->karma > $last_resort_karma &&
						$link->votes > $link->negatives*20) {
				$last_resort_id = $link->id;
				$last_resort_karma = $link->karma;
			}
		}
		print_row($link, $changes);

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
			print_row($link, 3);
			publish($link);
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

echo $output;
echo "</body></html>\n";
if (! DEBUG) {
	$annotation = new Annotation('promote');
	$annotation->text = $output;
	$annotation->store();
} else {
	echo "OUTPUT:\n$output\n";
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


function print_row($link, $changes, $log = '') {
	global $globals, $output;
	static $row = 0;

	$mod = $row%2;

	$output .= "<tr><td class='tnumber$mod'>".$link->votes."</td><td class='tnumber$mod'>".$link->anonymous."</td><td class='tnumber$mod'>".$link->negatives."</td><td class='tnumber$mod'>" . sprintf("%0.2f", $link->coef). "</td><td class='tnumber$mod'>".intval($link->karma)."</td>";
	$output .= "<td class='tdata$mod'>$link->meta_name</td>\n";
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

}


function publish($link) {
	global $globals, $db;

	//return;
	if (DEBUG) return;

	// Calculate votes average
	// it's used to calculate and check future averages
	$votes_avg = (float) $db->get_var("select SQL_NO_CACHE avg(vote_value) from votes, users where vote_type='links' AND vote_link_id=$link->id and vote_user_id > 0 and vote_value > 0 and vote_user_id = user_id and user_level !='disabled'");
	if ($votes_avg < $globals['users_karma_avg']) $link->votes_avg = max($votes_avg, $globals['users_karma_avg']*0.97);
	else $link->votes_avg = $votes_avg;

	$link->status = 'published';
	$link->date = $link->published_date=time();
	//$link->store_basic();
	$db->query("update links set link_status='published', link_date=now(), link_votes_avg=$link->votes_avg where link_id=$link->id");

	// Increase user's karma
	$user = new User;
	$user->id = $link->author;
	if ($user->read()) {
		$user->karma = min(20, $user->karma + $globals['instant_karma_per_published']);
		$user->store();
		$annotation = new Annotation("karma-$user->id");
		$annotation->append(_('noticia publicada').": +". $globals['instant_karma_per_published'] .", karma: $user->karma\n");
	}

	// Add the publish event/log
	log_insert('link_publish', $link->id, $link->author);
	$link->annotation .= _('publicación'). "<br/>";
	$link->save_annotation('link-karma');

	if ($globals['url_shortener']) {
		$short_url = $link->get_short_permalink();
	} else {
		$short_url = fon_gs($link->get_permalink());
	}
	if ($globals['twitter_user'] && $globals['twitter_password']) {
		twitter_post($link->title, $short_url); 
	}
	if ($globals['jaiku_user'] && $globals['jaiku_key']) {
		jaiku_post($link->title, $short_url); 
	}
	if ($globals['pubsub']) {
		pubsub_post();
	}

}

?>
