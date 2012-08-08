<?php
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
/* vim:set noet ci pi sts=0 sw=4 ts=4: */

include(dirname(__FILE__) . '/../../config.php');
include(mnminclude.'html1.php');

$globals['extra_css'][] = 'admin.css';
$globals['ads'] = false;

if (!$current_user->admin || $current_user->user_level != 'god') {
	do_header(_('Administración de Ligas'));
	echo '<div id="singlewrap">' . "\n";
	echo '<div class="topheading"><h2>'._('Esta página es sólo para administradores').'</h2>';
	echo '</div></div>';
	do_footer();
	exit;
}

function do_league_tabs()
{
    global $globals;
    $globals['league_base_url'] = $globals['base_url'] . 'admin/league/';
    $tabs  = array(_('Ligas') => 'index.php', _('Equipos') => 'teams.php', _('Partidos') => 'matches.php');
    $links = array();
    $self  = basename($_SERVER["SCRIPT_NAME"]);
    foreach ($tabs as $tab => $page) {
        $links[] = array(
            'url'    => $globals['league_base_url'] . $page,
            'name'   => $tab,
            'active' => $page == $self,
        );
    }
    Haanga::load('league/list.tpl', compact('links'));
}

function create_form($view, $title, $data = array())
{
    global $site_key, $current_user;
    $time = microtime(true) * 1000;
    $form = array(
        'title' => $title,
        'hash'  => sha1($site_key . $time . $current_user->user_id),
        'time'  => $time,
    );

    Haanga::Load("league/form.$view.tpl", compact('form', 'data'));
}
