<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005-2010 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


// Functions for recaptcha 2
function ts_is_human() {
	global $globals;

	if ($_POST["g-recaptcha-response"]) {
		$resp = recaptcha2_check_answer ($globals['recaptcha_private_key'],
								$_POST["g-recaptcha-response"]);
		
		if (is_object($resp) && $resp->success) {
			return true;
		} else {
			# set the error code so that we can display it
			$globals['error'] = _('error en el captcha');;
		}
	}
	return false;
}

function ts_print_form() {
	global $globals, $dblang;

	$globals['extra_js'][] = '//www.google.com/recaptcha/api.js';
	echo '<div class="g-recaptcha" data-sitekey="'.$globals['recaptcha_public_key'].'"></div>';
}


function recaptcha2_check_answer($secret, $response) {
	global $globals;

	$data = array('secret' => $secret, 'response' => $response);

	$con = curl_init();
	curl_setopt($con, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
	curl_setopt($con, CURLOPT_SSL_VERIFYPEER, false); // Sorry, but curl and its certificates are not reliable in Ubuntu
	curl_setopt($con, CURLOPT_POST, 1);
	curl_setopt($con, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($con, CURLOPT_RETURNTRANSFER, true);

	$output = curl_exec($con);
	// syslog(LOG_INFO, "recaptcha2 output: $output error: " . curl_error($con) . " data: " .  http_build_query($data));
	curl_close($con);

	if ($output) {
		$o = json_decode($output);
		return $o;
	}

	return null;
}
