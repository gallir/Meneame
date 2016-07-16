<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


function tags_normalize_string($string) {

	global $current_user, $globals;

	$string = clear_whitespace($string);
	$string = html_entity_decode(trim($string), ENT_COMPAT, 'UTF-8');
	$string = preg_replace('/-+/', '-', $string); // Don't allow a sequence of more than a "-"
	$string = preg_replace('/ +,/', ',', $string); // Avoid errors like " ,"
	$string = preg_replace('/[\n\t\r]+/s', ' ', $string);
	if (!preg_match('/,/', $string)) {
	// The user didn't put any comma, we add them
		$string = preg_replace('/ +/', ', ', $string);
	}
	if (!empty($globals['sponsored_tag']) and $current_user->user_id > 0 and !$current_user->admin) {
		$string = preg_replace("/\b" . $globals['sponsored_tag'] . "\b[ ,]*/", "", $string);
	}
	$string = preg_replace('/[\.\,] *$/', "", $string);
	// Clean strange characteres, there are feed reader (including feedburner) that are just too strict and complain loudly
	$string = preg_replace('/[\\\\<>;"\'\]\[&]/', "", $string);
	return htmlspecialchars(mb_substr(mb_strtolower($string, 'UTF-8'), 0, 80), ENT_COMPAT, 'UTF-8');
}

function tags_insert_string($link, $lang, $string, $date = 0) {
	global $db;

	$string = tags_normalize_string($string);
	if ($date == 0) $date=time();
	$words = preg_split('/[,]+/', $string);
	if ($words) {
		$db->query("delete from tags where tag_link_id = $link");
		foreach ($words as $word) {
			$word=$db->escape(trim($word));
			if (mb_strlen($word) >= 2 && !$inserted[$word] && !empty($word)) {
				$db->query("insert into tags (tag_link_id, tag_lang, tag_words, tag_date) values ($link, '$lang', '$word', from_unixtime($date))");
				$inserted[$word] = true;
			}
		}
		return true;
	}
	return false;

}

function tags_get_string($link, $lang) {
	global $db;

	$counter = 0;
	$res = $db->get_col("select tag_words from tags where tag_link_id=$link and tag_lang='$lang'");
	if (!$res) return false;

	foreach ($db->get_col("select tag_words from tags where tag_link_id=$link and tag_lang='$lang'") as $word) {
		if($counter>0)  $string .= ', ';
		$string .= $word;
		$counter++;
	}
	return $string;
}


class Tag {
	var $link=0;
	var $lang=0;
	var $words='';
	var $date;
	
	function Tag() {
		return;
	}
}

