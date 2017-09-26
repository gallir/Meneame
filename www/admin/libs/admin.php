<?php
defined('config_done') or die();

$globals['extra_js'][] = 'autocomplete/jquery.autocomplete.min.js';
$globals['extra_js'][] = 'jquery.user_autocomplete.js';
$globals['extra_css'][] = 'jquery.autocomplete.css';
$globals['extra_css'][] = 'bootstrap.min.css';
$globals['extra_css'][] = 'bootstrap-theme.min.css';
$globals['extra_css'][] = 'admin.css';
$globals['extra_js'][] = '../admin/js/admin.js';
$globals['ads'] = false;

if (!$current_user->admin) {
    die(Haanga::Load('admin/no_access.html'));
}

function do_admin_tabs($tab_selected = false)
{
    $tabs = [
        'admin_logs' => 'logs.php',
        'comment_reports' => 'reports.php',
        'strikes' => 'strikes.php',
        'hostname' => 'bans.php?tab=hostname',
        'punished_hostname' => 'bans.php?tab=punished_hostname',
        'email' => 'bans.php?tab=email',
        'ip' => 'bans.php?tab=ip',
        'words' => 'bans.php?tab=words',
        'noaccess' => 'bans.php?tab=noaccess',
        'preguntame' => 'preguntame.php',
        'patrocinios' => 'sponsors.php',
    ];

    Haanga::Load('admin/tabs.html', compact('tabs', 'tab_selected'));
}

function URLQuery()
{
    parse_str($_SERVER['QUERY_STRING'], $data);

    $args = func_get_args();
    $count = count($args);
    $changes = array();

    for ($i = 0; $i < $count; $i += 2) {
        $changes[$args[$i]] = $args[$i + 1];
    }

    return http_build_query(array_filter(array_replace($data, $changes)));
}
