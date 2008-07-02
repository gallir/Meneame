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



function do_banner_right() {
	global $globals;
//
// WARNING!
//
// IMPORTANT! adapt this section to your contracted banners!!
//
	if($globals['external_ads'] && $globals['ads'] && $globals['referer'] == 'search') {
			@include('ads/right.inc');
	} 
}

function do_banner_story() { // side banner A
	global $globals, $current_user;
//
// WARNING!
//
// IMPORTANT! adapt this section to your contracted banners!!
//
	if($globals['external_ads'] && $globals['ads']) {
		@include('ads/story.inc');
	}
}

function do_legal($legal_name, $target = '', $show_abuse = true) {
	global $globals;
	// IMPORTANT: legal note only for our servers, CHANGE IT!!
	if (preg_match('/meneame.net$/', get_server_name())) {
		echo '<a href="'.$globals['base_url'].'libs/ads/legal-meneame.php" '.$target.'>'.$legal_name.'</a>';
		if ($show_abuse) {
			echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
			echo '<a href="http://meneame.net/libs/ads/legal-meneame.php#contact" title="'._("encontrarás la dirección en la página de información legal").'">'._('reportar abusos').'</a>';
		}
	} else {
		echo _('legal conditions link here');
		echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
		echo _('abuse report email address here');
	}
	// IMPORTANT: read above
}

function do_credits() {
	global $dblang, $globals;

	echo "</div><!--#container closed-->\n";

// 	echo '<br style="clear: both;" />' . "\n";
	echo '<div class="credits-wrapper">' . "\n";
	echo '<div class="credits-strip">' . "\n";
	echo '<span class="credits-strip-text">' . "\n";

	echo '<strong>';
	do_legal (_('condiciones legales'));
	echo '</strong>';

	// IMPORTANT: links change in every installation, CHANGE IT!!
	// contact info
	if (preg_match('/meneame.net$/', get_server_name())) {
		echo '<br/><a href="'.$globals['base_url'].'faq-'.$dblang.'.php#we">'._('quiénes somos, contacto').'</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://socialmediasl.com" title="agencia de publicidad en menéame">contratar publicidad</a>&nbsp;&nbsp;|&nbsp;&nbsp;alojamiento en <a href="http://www.ferca.com">Ferca Network</a><br />'; // delete this link, is a meneame.net sponsor!<br />';
	} else {
		echo _('<br/>why are you and contact link here').'<br />';
	}

	// code link and licenses
	if (preg_match('/meneame.net$/', get_server_name())) {
		echo _('código: ').'<a href="'.$globals['base_url'].'COPYING">'._('licencia').'</a>, <a href="http://svn.meneame.net/index.cgi/branches/version2/">'._('descargar').'</a>';
		echo '&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://creativecommons.org/licenses/by-sa/2.5/">'._('licencia de los gráficos').'</a>' . "\n";
		echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
		echo '<a href="http://creativecommons.org/licenses/by/2.5/es/">'._('licencia del contenido').'</a><br />';
	} else {
			echo _('link to code and licenses here (please respect the menéame Affero license and publish your own code!)').'<br />';
	}
	// IMPORTANT: read above

	echo '</span>' . "\n";

	echo '<span class="credits-strip-buttons">' . "\n";
	echo '<a href="http://validator.w3.org/check?uri=referer"><img style="border:0;width:80px;height:15px" src="'.$globals['base_url'].'img/common/valid-xhtml10.gif" alt="Valid XHTML 1.0 Transitional" /></a>' . "\n";
	echo '<br />' . "\n";

	echo '<a href="http://jigsaw.w3.org/css-validator/check/referer"><img style="border:0;width:80px;height:15px" src="'.$globals['base_url'].'img/common/valid-css.gif" alt="Valid CSS" /></a>' . "\n";
	echo '<br />' . "\n";

	echo '<a href="http://feedvalidator.org/check.cgi?url=http://meneame.net/rss2.php"><img style="border:0;width:80px;height:15px" src="'.$globals['base_url'].'img/common/valid-rss.gif" alt="Valid RSS" title="Validate my RSS feed" /></a>' . "\n";

	echo '</span>' . "\n";

	echo '</div>' . "\n";
	echo '</div>' . "\n";
	echo "<!--ben-tmp-functions:do_credits-->\n";
}

function do_banner_right_low() {
	global $globals, $current_user;
//
// WARNING!
//
// IMPORTANT! adapt this section to your contracted banners!!
//
	/*
	if($globals['external_ads'] && $globals['ads'] && $current_user->user_id == 0) {
		@include('ads/codigobarras.inc');
	}
	*/
}

function do_banner_left_down() {
	global $globals, $current_user;
//
// WARNING!
//
// IMPORTANT! adapt this section to your contracted banners!!
//

	if($globals['external_ads'] && $globals['ads'] && ! $globals['link']) {
		//@include('ads/sexodinos.inc');
	}

}

function do_banner_top_lower() { // side banner A
	global $globals, $current_user;
//
// WARNING!
//
// IMPORTANT! adapt this section to your contracted banners!!
//
	if($globals['external_ads'] && $globals['ads'] && $current_user->user_id == 0) {
		echo '<div style="margin: 10px 0 0 80px; height: 95px">';
		@include('ads/adsense-top-lower.inc');
		echo '</div>' . "\n";
	}
}

function do_pager_ads() { // side banner A
	global $globals, $current_user;
//
// WARNING!
//
// IMPORTANT! adapt this section to your contracted banners!!
//
	/* It's a good ads
	if($globals['external_ads'] && $globals['ads'] && $current_user->user_id == 0) {
		echo '<div style="margin: 0 0 10px 0">' . "\n";
		@include('ads/adsense-block-5.inc');
		echo '</div>' . "\n";
	}
	*/
}

?>
