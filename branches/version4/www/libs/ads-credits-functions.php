<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
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
		Haanga::Safe_Load('private/top.html');
	}

/*****
	if($globals['external_ads'] && $globals['ads']) {
		Haanga::Safe_Load('private/ad-top.html');
		//@include('ads/top.inc');
	} else {
		echo '<div class="banner-top">' . "\n";
		Haanga::Safe_Load('private/ad-meneame.html');
		//@include('ads/meneame-01.inc');
		echo '</div>' . "\n";
	}
*****/
}

function do_banner_top_mobile () { 
	global $globals, $dblang;
//
// WARNING!
//
// IMPORTANT! adapt this section to your contracted banners!!
//
	if($globals['ads']) {
		@include('ads/mobile-01.inc');
	}
}


function do_banner_right() { // side banner A
	global $globals, $current_user;

	if ($globals['mobile']) return;
//
// WARNING!
//
// IMPORTANT! adapt this section to your contracted banners!!
//
	if($globals['external_ads'] && $globals['ads']) {
		if ($globals['kalooga_categories'] && isset($_REQUEST['category']) && in_array($_REQUEST['category'], $globals['kalooga_categories'])) {
			$globals['kalooga_right'] = true;
		}
		Haanga::Safe_Load('private/ad-right.html');
	}
}

function do_banner_promotions() { 
	global $globals;

	if ($globals['mobile']) return;

/*
	global $globals;
	if(! $globals['mobile'] && $globals['external_ads'] && $globals['ads']) {
		@include('ads/promotions.inc');
	}
*/
	Haanga::Safe_Load('private/promotions.html');
}

function do_banner_top_news() {
	global $globals;
	@include('ads/top-news.inc');
}

function do_banner_story() {
	global $globals, $current_user;
	if ($globals['link'] && $globals['kalooga_categories'] && in_array($globals['link']->category, $globals['kalooga_categories']) ) {
		$globals['kalooga_story'] = true;
	}
	if ($globals['external_ads'] && $globals['ads'] && $globals['link']) {
		Haanga::Safe_Load('private/ad-middle.html');
	}
}

function do_legal($legal_name, $target = '', $show_abuse = true) {
	global $globals;
	// IMPORTANT: legal note only for our servers, CHANGE IT!!
	if ($globals['is_meneame']) {
		echo '<a href="'.$globals['legal'].'" '.$target.'>'.$legal_name.'</a>';
	} else {
		echo 'legal conditions link here';
	}
	// IMPORTANT: read above
}

function do_credits_mobile() {
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

?>
