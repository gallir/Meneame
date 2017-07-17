<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function tags_normalize_string($string)
{
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

    if (!empty($globals['sponsored_tag']) and $current_user->user_id > 0) {
        $string = preg_replace("/\b" . $globals['sponsored_tag'] . "\b[ ,]*/i", "", $string);
    }

    $string = preg_replace('/[\.\,] *$/', "", $string);
    // Clean strange characteres, there are feed reader (including feedburner) that are just too strict and complain loudly
    $string = preg_replace('/[\\\\<>;"\'\]\[&]/', "", $string);

    return htmlspecialchars(mb_substr(mb_strtolower($string, 'UTF-8'), 0, 80), ENT_COMPAT, 'UTF-8');
}

function tags_string_to_array($string)
{
    $words = array_unique(array_map('trim', preg_split('/[,]+/', $string)));

    return array_filter($words, function($value) {
        return $word && (mb_strlen($word) > 1);
    });
}

function tags_insert_string($link, $lang, $string, $time = 0)
{
    global $db;

    $words = tags_string_to_array(tags_normalize_string($string));

    if (empty($words)) {
        return;
    }

    $link = (int)$link;
    $time = (int)$time ?: time();

    $db->query('
        DELETE FROM tags
        WHERE tag_link_id = "'.$link.'";
    ');

    $insert = array();

    foreach ($words as $word) {
        $insert[] = '("'.$link.'", "'.$lang.'", "'.$db->escape($word).'", FROM_UNIXTIME('.$time.'))';
    }

    return $db->query('
        INSERT INTO tags
        (tag_link_id, tag_lang, tag_words, tag_date)
        VALUES
        '.implode(', ', $insert).';
    ');
}

class Tag
{
    public $link = 0;
    public $lang = 0;
    public $words = '';
    public $date;
}
