<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".



/*****
// banners and credits funcions: FUNCTIONS TO ADAPT TO YOUR CONTRACTED ADS AND CREDITS
*****/


if (preg_match('/meneame.net$/', get_server_name())) {
	$globals['is_meneame']  = true;
}

function do_banner_top () { // top banner
	global $globals, $dblang, $current_user;
//
// WARNING!
//
// IMPORTANT! adapt this section to your contracted banners!!
//
	if($globals['external_ads'] && $globals['ads']) {
		@include('ads/top.inc');
	} else {
		echo '<div class="banner-top">' . "\n";
		@include('ads/meneame-01.inc');
		echo '</div>' . "\n";
	}
}



function do_banner_right() { // side banner A
	global $globals, $current_user;
//
// WARNING!
//
// IMPORTANT! adapt this section to your contracted banners!!
//
	if($globals['external_ads'] && $globals['ads']) {
		@include('ads/right.inc');
	}
}

function do_legal($legal_name, $target = '', $show_abuse = true) {
	global $globals;
	// IMPORTANT: legal note only for our servers, CHANGE IT!!
	if ($globals['is_meneame']) {
		echo '<a href="'.$globals['base_url'].'libs/ads/legal-meneame.php" '.$target.'>'.$legal_name.'</a>';
	} else {
		echo 'legal conditions link here';
	}
	// IMPORTANT: read above
}

function do_footer_help() {
	global $globals;
	if (! $globals['is_meneame']) return;
	echo '<h5>ayuda</h5>'."\n";
	echo '<ul id="helplist">'."\n";
	echo '<li><a href="'.$globals['base_url'].'faq-es.php">'._('faq').'</a></li>'."\n";
	echo '<li><a href="http://meneame.wikispaces.com/Ayuda">'._('ayuda').'</a></li>'."\n";
	echo '<li><a href="http://meneame.wikispaces.com/">'._('wiki').'</a></li>'."\n";
	echo '<li><a href="http://meneame.wikispaces.com/Bugs">'._('avisar errores').'</a></li>'."\n";
	echo '<li><a href="http://meneame.net/libs/ads/legal-meneame.php#contact">'._('avisar abusos').'</a></li>'."\n";
	echo '</ul>'."\n";
}

function do_footer_plus_meneame() {
	global $globals;
	if (! $globals['is_meneame']) return;
	echo '<h5>+menéame</h5>'."\n";
	echo '<ul id="moremenelist">'."\n";
	echo '<li><a href="http://mueveme.net/">'._('para móviles').'</a></li>'."\n";
	echo '<li><a href="/notame/">'._('nótame').'</a></li>'."\n";
	echo '<li><a href="http://blog.meneame.net/">'._('blog').'</a></li>'."\n";
	echo '<li><a href="http://meneame.jaiku.com/">'._('Jaiku').'</a></li>'."\n";
	echo '<li><a href="http://twitter.com/meneame_net">'._('Twitter').'</a></li>'."\n";
	echo '</ul>'."\n";
}

function do_footer_shop() {
	global $globals;
	if (! $globals['is_meneame']) return;
	echo '<h5>tienda</h5>'."\n";
	echo '<ul id="shoplift">'."\n";
	echo '<li><a href="http://meneame.wikispaces.com/menechandising">'._('camisetas').'</a></li>'."\n";
    echo '<li><a href="http://www.socialmediasl.com/">'._('publicidad').'</a></li>'."\n";
	echo '</ul>'."\n";

}
function do_credits() {
	global $dblang, $globals;

	echo '<div id="footthingy">';
	echo '<p>menéame</p>';
	echo '<ul id="legalese">';

	// IMPORTANT: links change in every installation, CHANGE IT!!
	// contact info
	if ($globals['is_meneame']) {
		echo '<li><a href="'.$globals['base_url'].'libs/ads/legal-meneame.php">'._('condiciones legales').'</a></li>';
		echo '<li><a href="'.$globals['base_url'].'faq-'.$dblang.'.php#we">'._('quiénes somos').'</a></li>';
		echo '<li>'._('licencias').':&nbsp;';
		echo '<a href="'.$globals['base_url'].'COPYING">'._('código').'</a>,&nbsp;';
		echo '<a href="http://creativecommons.org/licenses/by-sa/2.5/">'._('gráficos').'</a>,&nbsp;';
		echo '<a href="http://creativecommons.org/licenses/by/2.5/es/">'._('contenido').'</a></li>';
		echo '<li><a href="http://svn.meneame.net/index.cgi/branches/version3/">'._('descargar').'</a></li>';
		echo '<li>alojamiento en <a href="http://www.ferca.com">Ferca Network</a></li>';
	} else {
		echo '<li>link to code and licenses here (please respect the menéame Affero license and publish your own code!)</li>';
		echo '<li><a href="">contact here</a></li>';
		echo '<li>code: <a href="#">Affero license here</a>, <a href="#">download code here</a></li>';
		echo '<li>you and contact link here</li>';
	}
	echo '</ul>'."\n";
	echo '<ul id="stdcompliance">';
	echo '<li><a href="http://validator.w3.org/check?uri=referer"><img style="border:0;width:80px;height:15px" src="'.$globals['base_url'].'img/common/valid-xhtml10.gif" alt="Valid XHTML 1.0 Transitional" /></a></li>';
	echo '<li><a href="http://jigsaw.w3.org/css-validator/check/referer?profile=css3"><img style="border:0;width:80px;height:15px" src="'.$globals['base_url'].'img/common/valid-css.gif" alt="Valid CSS" /></a></li>';
	echo '<li><a href="http://feedvalidator.org/check.cgi?url=http://meneame.net/rss2.php"><img style="border:0;width:80px;height:15px" src="'.$globals['base_url'].'img/common/valid-rss.gif" alt="Valid RSS" title="Validate my RSS feed" /></a></li>';
	echo '</ul>';
	echo '</div>'."\n";

}
?>
