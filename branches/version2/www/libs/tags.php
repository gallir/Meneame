<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


function tags_normalize_string($string) {
	if (!preg_match('/,/', $string)) {
	// The user didn't put any comma, we add them
		$string = preg_replace('/ +/', ',', $string);
	}
	$string = preg_replace('/[\.\,] *$/', "", $string);
	return mb_substr(mb_strtolower($string, 'UTF-8'), 0, 80);
}

function tags_insert_string($link, $lang, $string, $date = 0) {
	global $db;

	$string = tags_normalize_string($string);
	if ($date == 0) $date=time();
	$words = preg_split('/[,;]+/', $string);
	if ($words) {
		$db->query("delete from tags where tag_link_id = $link");
		foreach ($words as $word) {
			$word=trim($word);
			if (!$inserted[$word] && !empty($word)) {
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

