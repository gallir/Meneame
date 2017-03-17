<?php
defined('mnminclude') or die();

$sql = "select subs.* from subs, prefs where pref_user_id = $user->id and pref_key = 'sub_follow' and subs.id = pref_value order by name asc";
$subs = $db->get_results($sql);
$followers = $db->get_results("select subs.id, count(*) as c from subs, prefs where pref_key = 'sub_follow' and subs.id = pref_value group by subs.id order by c desc;");

$subscribed_subs = array();

foreach ($subs as $sub) {
    $sub->site_info = SitesMgr::get_info($sub->id);

    // Check if the sub has a logo and calculate the width
    if ($sub->site_info->media_id > 0 && $sub->site_info->media_dim1 > 0 && $sub->site_info->media_dim2 > 0) {
        $r = $sub->site_info->media_dim1 / $sub->site_info->media_dim2;

        if ($globals['mobile']) {
            $sub->site_info->logo_height = $globals['media_sublogo_height_mobile'];
        } else {
            $sub->site_info->logo_height = $globals['media_sublogo_height'];
        }

        $sub->site_info->logo_width = round($r * $sub->site_info->logo_height);
        $sub->site_info->logo_url = Upload::get_cache_relative_dir($sub->site_info->id) . '/media_thumb-sub_logo-' . $sub->site_info->id . '.' . $sub->site_info->media_extension . '?' . $sub->site_info->media_date;
    }

    $sub->followers = 0;

    foreach ($followers as $row) {
        if ($sub->id == $row->id) {
            $sub->followers = $row->c;
        }
    }

    $subscribed_subs[] = $sub;
}

$title_subscriptions = _('Suscripciones');

if ($current_user->admin && $user->id == $current_user->user_id) {
    $sql = "select subs.* from subs where subs.sub = 1 and (subs.owner = $user->id or subs.owner = 0)";
} else {
    $sql = "select subs.* from subs where subs.sub = 1 and subs.owner = $user->id";
}

$ownwed_subs = $db->get_results($sql);
$owned_subs = array();

$ids_subs = array_map(function ($row) {
    return (int) $row->id;
}, $ownwed_subs);

if ($ids_subs) {
    $followers = $db->get_results('SELECT subs.id, COUNT(*) AS c FROM subs, prefs WHERE subs.id IN (' . implode(',', $ids_subs) . ') AND pref_key = "sub_follow" AND subs.id = pref_value GROUP BY subs.id ORDER BY c DESC;');
} else {
    $followers = array();
}

foreach ($ownwed_subs as $sub) {
    $sub->site_info = SitesMgr::get_info($sub->id);

    // Check if the sub has a logo and calculate the width
    if ($sub->site_info->media_id > 0 && $sub->site_info->media_dim1 > 0 && $sub->site_info->media_dim2 > 0) {
        $r = $sub->site_info->media_dim1 / $sub->site_info->media_dim2;

        if ($globals['mobile']) {
            $sub->site_info->logo_height = $globals['media_sublogo_height_mobile'];
        } else {
            $sub->site_info->logo_height = $globals['media_sublogo_height'];
        }

        $sub->site_info->logo_width = round($r * $sub->site_info->logo_height);
        $sub->site_info->logo_url = Upload::get_cache_relative_dir($sub->site_info->id) . '/media_thumb-sub_logo-' . $sub->site_info->id . '.' . $sub->site_info->media_extension . '?' . $sub->site_info->media_date;
    }

    $sub->followers = 0;

    foreach ($followers as $row) {
        if ($sub->id == $row->id) {
            $sub->followers = $row->c;
        }
    }

    $owned_subs[] = $sub;
}

$can_edit = ($current_user->user_id > 0 && $user->id == $current_user->user_id && SitesMgr::can_edit(0));

Haanga::Load('user/subs.html', compact('subscribed_subs', 'owned_subs', 'can_edit'));
