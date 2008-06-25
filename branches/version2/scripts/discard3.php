<?
include('../config.php');
include(mnminclude.'user.php');
include(mnminclude.'log.php');
include(mnminclude.'annotation.php');

header("Content-Type: text/plain");

$now = time();
$max_date = "date_sub(now(), interval 15 minute)";
$min_date = "date_sub(now(), interval 24 hour)"; 

// Delete not validated users
$db->query("delete from users where user_date < date_sub(now(), interval 12 hour) and user_date > date_sub(now(), interval 24 hour) and user_validated_date is null");

// Delete old bad links
$db->query("delete from links where link_status='discard' and link_date > date_sub(now(), interval 24 hour) and link_date < date_sub(now(), interval 20 minute) and link_votes = 0");

// send back to queue links with too many negatives
$links = $db->get_results("select link_id, link_author, link_date, link_karma from links where link_status = 'published' and link_date > date_sub(now(), interval 3 hour) and link_negatives > link_votes / 5");

if ($links) {
	foreach ($links as $link) {
		// count those votes with $globals['negative_votes_values'] = -8 =>  _('errónea'),  -9 => _('copia/plagio')
		// Count only those votes with karma > 6.2 to avoid abuses with new accounts with new accounts
		$negatives = (int) $db->get_var("select sum(user_karma) from votes, users where vote_type='links' and vote_link_id=$link->link_id and vote_date > '$link->link_date' and vote_value < 0 and vote_user_id > 0 and user_id = vote_user_id and user_karma > 6.2");
		$positives = (int) $db->get_var("select sum(user_karma) from votes, users where vote_type='links' and vote_link_id=$link->link_id and vote_date > '$link->link_date' and vote_value > 0 and vote_user_id > 0 and user_id = vote_user_id and user_karma > 6.2");
		echo "Candidate $link->link_id ($link->link_karma) $negatives $positives\n";
		if ($negatives > $link->link_karma/5 and $negatives > $positives) {
			echo "Queued again: $link->link_id negative karma: $negatives positive karma: $positives\n";
			$db->query("update links set link_status='queued', link_date = link_sent_date, link_karma=link_karma/2 where link_id = $link->link_id");
			log_insert('link_depublished', $link->link_id, $link->link_author);
			// Add the discard to log/event
			$user = new User();
			$user->id = $link->link_author;
			if ($user->read()) {
				$user->karma -= -2;
				echo "$user->username: $user->karma\n";
				$user->store();
				$annotation = new Annotation("karma-$user->id");
				$annotation->read();
				$annotation->text .= _('Noticia retirada de portada').": -2, karma: $user->karma\n";
				echo $annotation->text;
				$annotation->store();
			}
		}
	}
}


// Discard links
$negatives = $db->get_results("select SQL_NO_CACHE link_id, link_karma, link_votes, link_negatives, link_author from links where link_date < $max_date and link_date > $min_date and link_status = 'queued' and link_karma < link_votes*2 and ( (link_negatives > 20 and link_karma < 0 ) or (link_negatives > 3 and link_negatives > link_votes) )");

//$db->debug();
if( !$negatives) { 
	echo "no negatives to analyze\n";
	die;
}

foreach ($negatives as $negative) {
	$linkid = $negative->link_id;
	$user = new User();
	$user->id = $negative->link_author;
	if ($user->read()) {
		$user->karma -= 0.20;
		echo "$user->username: $user->karma\n";
		$user->store();
		$annotation = new Annotation("karma-$user->id");
		$annotation->read();
		$annotation->text .= _('Noticia descartada').": -0.20, karma: $user->karma\n";
		echo $annotation->text;
		$annotation->store();
	}
	$db->query("update links set link_status='discard' where link_id = $linkid");
	// Add the discard to log/event
	log_insert('link_discard', $linkid, $negative->link_author);
	echo  "$linkid: $negative->link_karma ($negative->link_votes, $negative->link_negatives)\n";

}

?>
