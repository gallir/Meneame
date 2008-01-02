<?
include('../config.php');
include(mnminclude.'link.php');
include(mnminclude.'user.php');
include_once(mnminclude.'log.php');

header("Content-Type: text/html");
echo '<html><head><title>promote7.php</title></head><body>';
ob_end_flush();
?>
<style type="text/css">
body {
	font-family: Bitstream Vera Sans, Arial, Helvetica, sans-serif;
	font-size: 80%;
	margin: 0px;
	padding: 20px;
}
table {
	width: 90%;
	font-size: 110%;
	margin: 0px;
	padding: 4px;
}
td {
	margin: 0px;
	padding: 4px;
}
.thead {
	font-size: 115%;
	text-transform: uppercase;
	color: #FFFFFF;
	background-color: #FF6600;
	padding: 6px;
}
.tdata0 {
	background-color: #FFF;
}
.tdata1 {
	background-color: #FFF3E8;
}
.tnumber0 {
	text-align: center;
}
.tnumber1 {
	text-align: center;
	background-color: #FFF3E8;
}
</style>
<?

$min_karma_coef = 0.88;
define(MAX, 1.15);
define (MIN, 1.0);
define (PUB_MIN, 20);
define (PUB_MAX, 70);


$links_queue = $db->get_var("SELECT SQL_NO_CACHE count(*) from links WHERE link_date > date_sub(now(), interval 24 hour) and link_status !='discard'");
$links_queue_all = $db->get_var("SELECT SQL_NO_CACHE count(*) from links WHERE link_date > date_sub(now(), interval 24 hour) and link_votes > 0");


$pub_estimation = intval(max(min($links_queue * 0.12, PUB_MAX), PUB_MIN));
$interval = intval(86400 / $pub_estimation);

$now = time();
echo "<p><b>BEGIN</b>: ".get_date_time($now)."<br>\n";

$from_time = "date_sub(now(), interval 5 day)";
#$from_where = "FROM votes, links WHERE  


$last_published = $db->get_var("SELECT SQL_NO_CACHE UNIX_TIMESTAMP(max(link_published_date)) from links WHERE link_status='published'");
if (!$last_published) $last_published = $now - 24*3600*30;
$links_published = (int) $db->get_var("select count(*) from links where link_status = 'published' and link_published_date > date_sub(now(), interval 24 hour)");
$links_published_projection = 4 * (int) $db->get_var("select count(*) from links where link_status = 'published' and link_published_date > date_sub(now(), interval 6 hour)");

$diff = $now - $last_published;

$decay = min(MAX, MAX - ($diff/$interval)*(MAX-MIN) );
$decay = max($min_karma_coef, $decay);
print "Last published at: " . get_date_time($last_published) ."<br>\n";
echo "24hs queue: $links_queue/$links_queue_all, Published: $links_published -> $links_published_projection Published goal: $pub_estimation, Interval: $interval secs, Decay: $decay<br>\n";

$continue = true;
$published=0;

$past_karma_long = intval($db->get_var("SELECT SQL_NO_CACHE avg(link_karma) from links WHERE link_published_date >= date_sub(now(), interval 7 day) and link_status='published'"));
$past_karma_short = intval($db->get_var("SELECT SQL_NO_CACHE avg(link_karma) from links WHERE link_published_date >= date_sub(now(), interval 8 hour) and link_status='published'"));

$past_karma = 0.5 * max(40, $past_karma_long) + 0.5 * max($past_karma_long*0.8, $past_karma_short);
$min_past_karma = (int) ($past_karma * $min_karma_coef);


//////////////
$min_karma = round(max($past_karma * $decay, 20));

if ($decay >= 1) $max_to_publish = 3;
else $max_to_publish = 1;

$min_votes = 5;
/////////////

$limit_karma = round(min($past_karma,$min_karma) * 0.70);
$bonus_karma = round(min($past_karma,$min_karma) * 0.50);


/// Coeficients to even metacategories
$days = 3;
$total_published = (int) $db->get_var("select count(*) from links where link_status = 'published' and link_published_date > date_sub(now(), interval $days day)");
$db_metas = $db->get_results("select category_id, category_name, category_calculated_coef from categories where category_parent = 0 and category_id in (select category_parent from categories where category_parent > 0)");
foreach ($db_metas as $dbmeta) {
	$meta = $dbmeta->category_id;
	$meta_previous_coef[$meta] = $dbmeta->category_calculated_coef;
	$meta_names[$meta] = $dbmeta->category_name;
	$x = (int) $db->get_var("select count(*) from links, categories where link_status = 'published' and link_published_date > date_sub(now(), interval $days day) and link_category = category_id and category_parent = $meta");
	$y = (int) $db->get_var("select count(*) from links, categories where link_status in ('published', 'queued') and link_date > date_sub(now(), interval $days day) and link_category = category_id and category_parent = $meta");
	$meta_coef[$meta] = $x/$y;
	$meta_coef[$meta] = 0.8 * $meta_coef[$meta] + 0.2 * $x / $total_published / count($db_metas) ;
	$meta_avg += $meta_coef[$meta] / count($db_metas);
	echo "$days days stats for <b>$meta_names[$meta]</b> (queued/published/total): $y/$x/$total_published -> $meta_coef[$meta]<br>";
	//echo "$meta: $meta_coef[$meta] - $x / $y<br>";
}
foreach ($meta_coef as $m => $v) {
	$meta_coef[$m] = max(min($meta_avg/$v, 1.25), 0.75);
	if ($meta_previous_coef[$m]  > 0.6 && $meta_previous_coef[$m]  < 1.5) {
		//echo "Previous: $meta_previous_coef[$m], current: $meta_coef[$m] <br>";
		$meta_coef[$m] = 0.05 * $meta_coef[$m] + 0.95 * $meta_previous_coef[$m] ;
	}
	echo "Karma coefficient for <b>$meta_names[$m]</b>: $meta_coef[$m]<br>";
	// Store current coef in DB
	$db->query("update categories set category_calculated_coef = $meta_coef[$m] where (category_id = $m || category_parent = $m)");
}


// Karma average:  It's used for each link to check the balance of users' votes

global $users_karma_avg;
$users_karma_avg = (float) $db->get_var("select avg(link_votes_avg) from links where link_status = 'published' and link_published_date > date_sub(now(), interval 72 hour)");
$users_karma_avg_trunc = (int) $users_karma_avg;
$users_karma_avg_coef = $users_karma_avg - $users_karma_avg_trunc;

echo "Karma average for each link: $users_karma_avg, Past karma. Long term: $past_karma_long, Short term: $past_karma_short, Average: <b>$past_karma</b><br>\n";
echo "<b>Current MIN karma: $min_karma</b>, absolute min karma: $min_past_karma, analizing from $limit_karma<br>\n";
echo "</p>\n";




$where = "link_date > $from_time AND link_status = 'queued' AND link_votes>=$min_votes  AND (link_karma > $limit_karma or (link_date > date_sub(now(), interval 2 hour) and link_karma > $bonus_karma)) and user_id = link_author and category_id = link_category";
$sort = "ORDER BY link_karma DESC, link_votes DESC";

$links = $db->get_results("SELECT SQL_NO_CACHE link_id, link_karma as karma, category_parent as parent from links, users, categories where $where $sort LIMIT 30");
$rows = $db->num_rows;
if (!$rows) {
	echo "There are no articles<br>\n";
	echo "--------------------------<br>\n";
	die;
}
	
$max_karma_found = 0;
$best_link = 0;
$best_karma = 0;
echo "<table>\n";	
if ($links) {
	print "<tr class='thead'><th>votes</th><th>anon</th><th>neg.</th><th>bonus</th><th>karma</th><th>meta</th><th>title</th><th>changes</th></tr>\n";
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

		// Count number of votes
		//$votes_pos = intval($db->get_var("select SQL_NO_CACHE count(*) from votes, users where vote_type='links' AND vote_link_id=$link->id and vote_user_id > 0 and vote_value > 0 and vote_user_id = user_id and user_level != 'disabled'"));
		//$votes_neg = intval($db->get_var("select SQL_NO_CACHE count(*) from votes, users where vote_type='links' AND vote_link_id=$link->id and vote_value < 0 and vote_user_id = user_id and user_level != 'disabled'"));
		$votes_pos = intval($db->get_var("select SQL_NO_CACHE count(*) from votes where vote_type='links' AND vote_link_id=$link->id and vote_user_id > 0 and vote_value>0"));
		$votes_neg = intval($db->get_var("select SQL_NO_CACHE count(*) from votes where vote_type='links' AND vote_link_id=$link->id and vote_value < 0"));
		$votes_pos_anon = intval($db->get_var("select SQL_NO_CACHE count(*) from votes where vote_type='links' AND vote_link_id=$link->id and vote_user_id = 0 and vote_value > 0"));

		if ($link->votes != $votes_pos || $link->anonymous != $votes_pos_anon || $link->negatives != $votes_neg) {
			$link->votes = $votes_pos;
			$link->anonymous = $votes_pos_anon;
			$link->negatives = $votes_neg;
			$link->store_basic();
		}


		// Calculate the real karma for the link
		// high =~ users with higher karma greater than average
		// low =~ users with higher karma less-equal than average
		$karma_pos_user_high = intval($db->get_var("select SQL_NO_CACHE sum(vote_value) from votes, users where vote_type='links' AND vote_link_id=$link->id and vote_user_id > 0 and vote_value > 0 and vote_user_id = user_id and user_level !='disabled' and vote_value > $users_karma_avg_trunc"));
		$karma_pos_user_equal = intval($db->get_var("select SQL_NO_CACHE sum(vote_value) from votes, users where vote_type='links' AND vote_link_id=$link->id and vote_user_id > 0 and vote_value > 0 and vote_user_id = user_id and user_level !='disabled' and vote_value = $users_karma_avg_trunc"));
		$karma_pos_user_low = intval($db->get_var("select SQL_NO_CACHE sum(vote_value) from votes, users where vote_type='links' AND vote_link_id=$link->id and vote_user_id > 0 and vote_value > 0 and vote_user_id = user_id and user_level !='disabled' and vote_value < $users_karma_avg_trunc"));
		//$karma_neg_user = intval($db->get_var("select SQL_NO_CACHE sum(vote_value-user_karma/2) from votes, users where vote_type='links' AND vote_link_id=$link->id and vote_user_id > 0 and vote_value < 0 and user_id=vote_user_id and user_level !='disabled'"));
		$karma_neg_user = intval($db->get_var("select SQL_NO_CACHE sum(-user_karma) from votes, users where vote_type='links' AND vote_link_id=$link->id and vote_user_id > 0 and vote_value < 0 and user_id=vote_user_id and user_level !='disabled'"));


		// Now you distribute the "equal" among the two values
		$karma_pos_user_high += $karma_pos_user_equal * (1 - $users_karma_avg_coef);
		$karma_pos_user_low += $karma_pos_user_equal * $users_karma_avg_coef;

		// Make sure we don't deviate too much from the average (it avoids vote spams and abuses)
		// Allowed difference up to 3%
		$karma_pos_user = $karma_pos_user_high + (int) min($karma_pos_user_high * 1.07, $karma_pos_user_low);

		// If the user was disabled don't count anon. votes due to abuses
		if ($user->level != 'disabled') {
			$karma_pos_ano = intval($db->get_var("select SQL_NO_CACHE sum(vote_value) from votes where vote_type='links' AND vote_link_id=$link->id and vote_user_id = 0 and vote_value > 0"));
		} else {
			$karma_pos_ano = 0;
		}

		$karma_new = $karma_pos_user + $karma_neg_user;
		// To void votes spamming
		// Do not allow annonymous users to give more karma than registered users
		// The ratio up to 10% anonymous
		if ($karma_new > 0) 
			$karma_new += min($karma_pos_user_high*0.1, $karma_pos_ano);

		//echo "previous $dblink->parent: $karma_new -> ";
		$karma_new = (int) ($karma_new * $meta_coef[$dblink->parent]);
		//echo "$karma_new<br>";


		// Aged karma
		$diff = max(0, $now - ($link->date + 10*3600)); // 10 hours without decreasing
		$oldd = 1 - $diff/(3600*48);
		$oldd = max(0.4, $oldd);
		$oldd = min(1, $oldd);

		// BONUS
		// Give more karma to news voted very fast during the first two hours (ish)
		if ($now - $link->date < 6300 && $now - $link->date > 600) { // 6300 === 1 hs, 45 min
			$link->new_coef = 2 - ($now-$link->date)/6300;
			// if it's has bonus and therefore time-related, use the base min_karma
			if ($decay > 1) 
				$karma_threshold = $past_karma;
			else
				$karma_threshold = $min_karma;

		} else {
			// Otherwise use normal decayed min_karma
			$karma_threshold = $min_karma;
			$link->new_coef = 1;
		}

		$aged_karma =  $karma_new * $oldd * $link->new_coef;
		$dblink->karma=$aged_karma;

		$changes = 0;
		if (abs($link->karma - $dblink->karma) > 4) {
			$link->message = sprintf ("updated karma: %6d (%d, %d, %d) -> %-6d (%d, %d, %d)\n", $link->karma, $link->votes, $link->anonymous, $link->negatives, round($dblink->karma), $votes_pos, $votes_pos_anon, $votes_neg);
			if ($link->karma > $dblink->karma) 
				$changes = 1; // to show a "decrease" later	
			else $changes = 2; // increase
			$link->karma = round($dblink->karma);
			$link->store_basic();
		} else $link->message = '';
		if ($user->level == 'disabled') {
			if (preg_match('/^_+[0-9]+_+$/', $user->username)) {
				$link->message .= " $user->username disabled herself, penalized.";
				$do_publish = true;
			} else {
				$link->message .= " $user->username disabled, probably due to abuses, penalized.";
				$do_publish = true;
			}
			$link->karma = $dblink->karma = round($link->karma*0.66);
			$link->store_basic();
			$changes = 1;
		} else {
				$do_publish = true;
		}
			
		if ($do_publish && $link->votes >= $min_votes && $dblink->karma >= $karma_threshold && $published < $max_to_publish) {
			$published++;
			$link->karma = round($dblink->karma);
			publish($link);

			$changes = 3; // to show a "published" later	
		} else {
			if ($link->karma > $past_karma * $min_karma_coef && $link->karma > $last_resort_karma) {
				$last_resort_id = $link->id;
				$last_resort_karma = $link->karma;
			}
		}
		print_row($link, $changes);
		$i++;
	}
	if ($published == 0 && $decay < 0.99) {
		// Publish last resort
		$link = new Link;
		$link->id = $last_resort_id;
		if ($link->read()) {
			$link->message = "Last resort: selected with the best karma";
			print_row($link, 3);
			publish($link);
		}
	}
	print "</table>\n";
	//////////
}  
echo "</body></html>\n";

function print_row(&$link, $changes, &$log = '') {
	global $globals;
	static $row = 0;

	$mod = $row%2;

	echo "<tr><td class='tnumber$mod'>".$link->votes."</td><td class='tnumber$mod'>".$link->anonymous."</td><td class='tnumber$mod'>".$link->negatives."</td><td class='tnumber$mod'>" . sprintf("%0.2f", $link->new_coef). "</td><td class='tnumber$mod'>".intval($link->karma)."</td>";
	echo "<td class='tdata$mod'>$link->meta_name</td>\n";
	echo "<td class='tdata$mod'><a href='".$link->get_relative_permalink()."'>$link->title</a>\n";
	if (!empty($link->message)) {
		echo "<br>$link->message";
	}
	$link->message = '';
	echo "</td>\n";
	echo "<td class='tnumber$mod'>";
	switch ($changes) {
		case 1:
			echo '<img src="../img/common/sneak-problem01.png" width="21" height="17" alt="'. _('descenso') .'"/>';
			break;
		case 2:
			echo '<img src="../img/common/sneak-vote01.png" width="21" height="17" alt="'. _('ascenso') .'"/>';
			break;
		case 3:
			echo '<img src="../img/common/sneak-published01.png" width="21" height="17" alt="'. _('publicada') .'"/>';
			break;
	}
	echo "</td>";
	echo "</tr>\n";
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
	$link->published_date=time();
	$link->store_basic();
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
?>
