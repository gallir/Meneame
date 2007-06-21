<?php

// Modified and adapted by Ricardo Galli from:
/*
* File: CaptchaSecurityImages.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 03/08/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-captcha.php
* 
* This program is free software; you can redistribute it and/or 
* modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation; either version 2 
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
* GNU General Public License for more details: 
* http://www.gnu.org/licenses/gpl.html
*
*/

if (empty($globals['recaptcha_public_key']) || empty($globals['recaptcha_private_key'])) {
	session_cache_expire(15);
	session_name('mnm_captcha');
	session_start();
} else {
	require_once(mnminclude.'recaptchalib.php');
}

class CaptchaSecurityImages {

	var $font = 'adler.ttf';

	function generateCode($characters) {
		/* list all possible characters, similar looking characters and vowels have been removed */
		$possible = '23456789bcdfghjkmnpqrstvwxyz'; 
		$code = '';
		$i = 0;
		while ($i < $characters) { 
			$code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
			$i++;
		}
		return $code;
	}

	function CaptchaSecurityImages($width='120',$height='40',$characters='6') {
		$code = $this->generateCode($characters);
		/* font size will be 75% of the image height */
		// changed to .55 for Adler
		$font_size = $height * 0.55;
		$image = @imagecreate($width, $height) or die('Cannot Initialize new GD image stream');
		/* set the colours */
		$background_color = imagecolorallocate($image, 255, 255, 255);
		//$text_color = imagecolorallocate($image, 20, 40, 100);
		$text_color = imagecolorallocate($image, mt_rand(140,148),mt_rand(72,80),mt_rand(0,5));
		//$noise_color = imagecolorallocate($image, 100, 120, 180);
		$noise_color = imagecolorallocate($image, mt_rand(250,255), mt_rand(95,105), mt_rand(0,5));
		/* generate random dots in background */
		for( $i=0; $i<($width*$height)/3; $i++ ) {
			$x1 = mt_rand(0,$width); 
			$y1 =  mt_rand(0,$height);
			imagefilledrectangle($image, $x1, $y1, $x1, $y1, $noise_color);
		}
		/* generate random lines in background */
		for( $i=0; $i<($width*$height)/150; $i++ ) {
			imageline($image, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $noise_color);
		}
		/* create textbox and add text */
		$textbox = imagettfbbox($font_size, 0, $this->font, $code);
		$x = ($width - $textbox[4])/2;
		// Changed to 2.3 for adler
		$y = ($height - $textbox[5])/2.3;
		imagettftext($image, $font_size, 0, $x, $y, $text_color, $this->font , $code);
		
		/* output captcha image to browser */
		imagejpeg($image);
		imagedestroy($image);
		$_SESSION['security_code'] = $code;
	}

}

function ts_gfx() {
	// Hack to avoid problems with monofont.ttf
	putenv('GDFONTPATH=' . mnminclude);
	
	header('Content-Type: image/jpeg');
	$captcha = new CaptchaSecurityImages(155,45,5);
}


function ts_is_human() {
	global $globals;

	if (empty($globals['recaptcha_public_key']) || empty($globals['recaptcha_private_key'])) {
		$result = !empty($_SESSION['security_code']) && $_SESSION['security_code'] == $_POST['security_code'];
		if ($result)  {
			$_SESSION['security_code'] = '';
			return true;
		}
		return false;
	} else {
		if ($_POST["recaptcha_response_field"]) {
			$resp = recaptcha_check_answer ($globals['recaptcha_private_key'],
									$globals['user_ip'],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);
		
			if ($resp->is_valid) {
				return true;
			} else {
				# set the error code so that we can display it
				$globals['error'] = $resp->error;
			}
		}
		return false;
	}
}

function ts_print_form() {
	global $globals;

	if (empty($globals['recaptcha_public_key']) || empty($globals['recaptcha_private_key'])) {
		echo _("introduce el texto de la imagen:")."<br/>\n";
		echo '<div class="tc"><img src="ts_image.php" alt="code number"/></div>';
		echo '<input type="text" size="20" name="security_code" /><br/>'."\n";
	} else {
	// reCaptcha
		echo _("escribe las dos palabras")."<br/>\n";
		echo recaptcha_get_html($globals['recaptcha_public_key'],null);
	}
}
?>
