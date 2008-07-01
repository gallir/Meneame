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
		echo '<div class="banner-01">' . "\n";
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
	if (preg_match('/meneame.net$/', get_server_name())) {
		echo '<li><a href="'.$globals['base_url'].'libs/ads/legal-meneame.php" '.$target.'>'.$legal_name.'</a></li>';
		if ($show_abuse) {
			echo '<li><a href="http://meneame.net/libs/ads/legal-meneame.php#contact" title="'._("encontrarás la dirección en la página de información legal").'">'._('reportar abusos').'</a></li>';
		}
	} else {
		echo '<li>legal conditions link here</li>';
		echo '<li>abuse report email address here</li>';
	}
	// IMPORTANT: read above
}

function do_credits() {
	global $dblang, $globals;

	echo '<div id="footthingy">';
	echo '<p>menéame</p>';
	echo '<ul id="legalese">';
	do_legal (_('condiciones legales'));

	// IMPORTANT: links change in every installation, CHANGE IT!!
	// contact info
	if (preg_match('/meneame.net$/', get_server_name())) {
		echo '<li><a href="'.$globals['base_url'].'faq-'.$dblang.'.php#we">'._('quiénes somos, contacto').'</a></li>';
		echo '<li><a href="'.$globals['base_url'].'COPYING">'._('licencia código').'</a></li>';
		echo '<li><a href="http://svn.meneame.net/index.cgi/branches/version3/">'._('descargar').'</a></li>';
		echo '<li><a href="http://creativecommons.org/licenses/by-sa/2.5/">'._('licencia de los gráficos').'</a></li>' . "\n";
		echo '<li><a href="http://creativecommons.org/licenses/by/2.5/es/">'._('licencia del contenido').'</a></li>';
		echo '<li>alojamiento en <a href="http://www.ferca.com">Ferca Network</a></li>';
	} else {
		echo '<li>link to code and licenses here (please respect the menéame Affero license and publish your own code!</li>';
		echo '<li><a href="">contacto</a></li>';
		echo '<li>código: <a href="">licencia</a>, <a href="">descargar</a></li>';
		echo '<li>you and contact link here</li>';
	}
	echo '</ul>';
	echo '<ul id="stdcompliance">';
	echo '<li><a href="http://validator.w3.org/check?uri=referer"><img style="border:0;width:80px;height:15px" src="img/common/valid-xhtml10.gif" alt="Valid XHTML 1.0 Transitional" /></a></li>';
	echo '<li><a href="http://jigsaw.w3.org/css-validator/check/referer"><img style="border:0;width:80px;height:15px" src="img/common/valid-css.gif" alt="Valid CSS" /></a></li>';
	echo '<li><a href="http://feedvalidator.org/check.cgi?url=http://meneame.net/rss2.php"><img style="border:0;width:80px;height:15px" src="img/common/valid-rss.gif" alt="Valid RSS" title="Validate my RSS feed" /></a></li>';
	echo '</ul>';
	echo '</div>';

}
?>
