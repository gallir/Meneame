<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

/*****
// banners and credits funcions: FUNCTIONS TO ADAPT TO YOUR CONTRACTED ADS AND CREDITS
*****/

function banner_enabled($mobile = false)
{
    global $globals;

    return ($globals['ads'] && (!$globals['mobile'] || $mobile));
}

function do_banner($template, $mobile = false)
{
    if (banner_enabled($mobile)) {
        Haanga::Safe_Load($template.'.html');
    }
}

function do_banner_private($template, $mobile = false)
{
    do_banner('private/'.$template);
}

function do_banner_inc($template, $mobile = false)
{
    $file = __DIR__.'/ads/'.$template.'.inc';

    if (banner_enabled($mobile) && is_file($file)) {
        include $file;
    }
}

function do_banner_top()
{
    global $globals;

    if ($globals['external_ads'] && $globals['ads']) {
        Haanga::Safe_Load('private/top.html');
    }
}

function do_banner_top_mobile()
{
    do_banner_inc('mobile-01', true);
}

function do_banner_right()
{
    do_banner_private('ad-right');
}

function do_banner_promotions()
{
    do_banner_private('promotions');
}

function do_banner_top_news()
{
    do_banner_private('top-news', true);
}

function do_banner_story()
{
    do_banner_private('ad-middle', true);
}

function do_legal($legal_name, $target = '', $show_abuse = true)
{
    global $globals;

    // IMPORTANT: legal note only for our servers, CHANGE IT!!
    if ($globals['is_meneame']) {
        echo '<a href="'.$globals['legal'].'" '.$target.'>'.$legal_name.'</a>';
    } else {
        echo 'legal conditions link here';
    }
    // IMPORTANT: read above
}

function do_credits_mobile()
{
    global $dblang, $globals;

    echo '<div id="footthingy">';
    echo '<a href="http://meneame.net" title="meneame.net"><img src="'.$globals['base_static'].'img/mnm/meneito.png" alt="Menéame"/></a>';
    /*
    echo '<ul id="stdcompliance">';
    echo '<li><a href="http://validator.w3.org/check?uri=referer"><img style="border:0;width:80px;height:15px" src="'.$globals['base_url'].'img/common/valid-xhtml10.gif" alt="Valid XHTML 1.0 Transitional" /></a></li>';
    echo '<li><a href="http://jigsaw.w3.org/css-validator/check/referer?profile=css3"><img style="border:0;width:80px;height:15px" src="'.$globals['base_url'].'img/common/valid-css.gif" alt="Valid CSS" /></a></li>';
    echo '</ul>';
    */
    echo '</div>'."\n";
}
