<?php
defined('mnminclude') or die();

if (empty($subs)) {
    return Haanga::Load('user/empty.html');
}

$ids_subs = array_map(function ($row) {
    return (int) $row->id;
}, $subs);

$tmp = $db->get_results('
    SELECT subs.id, COUNT(*) AS `count`
    FROM subs, prefs
    WHERE (
        subs.id IN (' . implode(',', $ids_subs) . ')
        AND pref_key = "sub_follow"
        AND subs.id = pref_value
    )
    GROUP BY subs.id;
');

$followers = array();

foreach ($tmp as $row) {
    $followers[$row->id] = $row->count;
}

foreach ($subs as $sub) {
    $sub->site_info = SitesMgr::get_info($sub->id);
    $sub->followers = isset($followers[$sub->id]) ? $followers[$sub->id] : 0;

    // Check if the sub has a logo and calculate the width
    if (!$sub->site_info->media_id || !$sub->site_info->media_dim1 || !$sub->site_info->media_dim2) {
        continue;
    }

    if ($globals['mobile']) {
        $sub->site_info->logo_height = $globals['media_sublogo_height_mobile'];
    } else {
        $sub->site_info->logo_height = $globals['media_sublogo_height'];
    }

    $sub->site_info->logo_width = round(($sub->site_info->media_dim1 / $sub->site_info->media_dim2) * $sub->site_info->logo_height);
    $sub->site_info->logo_url = Upload::get_cache_relative_dir($sub->site_info->id)
        . '/media_thumb-sub_logo-'
        . $sub->site_info->id . '.' . $sub->site_info->media_extension
        . '?' . $sub->site_info->media_date;
}

Haanga::Load('subs_list.html', compact('subs'));
