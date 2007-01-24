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
		if ($globals['external_user_ads'] && $globals['do_user_ad'] > 0 && ($r=rand(1, 100)) <= $globals['do_user_ad']) {
		// do_user_ad: 0 = dont show user banner, 1 = show standard, 2 = show all user ads
			echo '<div class="banner-01">' . "\n";
			@include('ads/adsense-top-user01.inc');
		} elseif ($globals['referer'] == 'search') {
			echo '<div class="banner-01">' . "\n";
			@include('ads/adsense-top-from-search.inc');
		/* 
		// If the user is authenticated show a small block
		} elseif ($current_user->user_id > 0) {
			echo '<div class="banner-block">' . "\n";
			@include('ads/adsense-top-bloque.inc');
		*/
		} else {
			echo '<div class="banner-01">' . "\n";
			@include('ads/adsense-top.inc');
		}
		echo '<!--Adsense Info: do_user_ad: '.$globals['do_user_ad']." - Random: $r -->\n";
	} else {
		echo '<div class="banner-01">' . "\n";
		@include('ads/meneame-01.inc');
	}
	echo '</div>' . "\n";
}



function do_banner_right() {
	global $globals;
//
// WARNING!
//
// IMPORTANT! adapt this section to your contracted banners!!
//
	if($globals['external_ads'] && $globals['ads'] && $globals['referer'] == 'search') {
			echo '<div class="banner-right">' . "\n";
			@include('ads/adsense-block-right.inc');
			echo '</div>' . "\n";
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
		if ($globals['referer'] == 'search') {
			echo '<div class="banner-story-2">' . "\n";
			@include('ads/adsense-story-from-search.inc');
			echo '</div>' . "\n";
		} elseif($current_user->user_id == 0) {
			echo '<div class="banner-story">' . "\n";
			@include('ads/adsense-story-01.inc');
			echo '</div>' . "\n";
		}
	}
}

function do_legal($legal_name, $target = '') {
	global $globals;
	// IMPORTANT: legal note only for our servers, CHANGE IT!!
	if (preg_match('/meneame.net$/', get_server_name())) {
		echo '<a href="'.$globals['base_url'].'libs/ads/legal-meneame.php" '.$target.'>'.$legal_name.'</a>';
	} else {
		echo _('condiciones legales');
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
	echo '</strong>&nbsp;&nbsp;|&nbsp;&nbsp;';


	echo '<a href="'.$globals['base_url'].'faq-'.$dblang.'.php#we">'._('quiénes somos').'</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.ferca.com">Alojamiento web en Ferca Network</a><br />'; // delete this link, is a meneame.net sponsor!<br />';
	echo _('código: ').'<a href="'.$globals['base_url'].'COPYING">'._('licencia').'</a>, <a href="http://svn.meneame.net/index.cgi/branches/version2/">'._('descargar').'</a>';
	echo '&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://creativecommons.org/licenses/by-sa/2.5/">'._('licencia de los gráficos').'</a>' . "\n";
	echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
	echo '<a href="http://creativecommons.org/licenses/by/2.5/es/">'._('licencia del contenido').'</a><br />';
	echo '</span>' . "\n";
	echo '<span class="credits-strip-buttons">' . "\n";
	echo '<a href="http://validator.w3.org/check?uri=referer"><img src="'.$globals['base_url'].'img/common/valid-xhtml10.png" alt="Valid XHTML 1.0 Transitional" height="31" width="88" /></a>' . "\n";
	echo '&nbsp;&nbsp;' . "\n";
	echo '<a href="http://jigsaw.w3.org/css-validator/check/referer"><img style="border:0;width:88px;height:31px" src="'.$globals['base_url'].'img/common/vcss.png" alt="Valid CSS!" /></a>&nbsp;&nbsp;' . "\n";
	echo '<a href="http://feedvalidator.org/check.cgi?url=http://meneame.net/rss2.php"><img src="'.$globals['base_url'].'img/common/valid-rss.png" alt="[Valid RSS]" title="Validate my RSS feed" /></a>' . "\n";
	echo '</span>' . "\n";
	echo '</div>' . "\n";
	echo '</div>' . "\n";
	echo "<!--ben-tmp-functions:do_credits-->\n";
}
?>
