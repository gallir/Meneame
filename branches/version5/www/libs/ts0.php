<?php
/*
Based on Trencaspammers (http://coffelius.arabandalucia.com)
Keep the original license
*/
if (!isset($site_key)) 
	$site_key=82397834;

function ts_gfx($ts_random) {
	global $site_key;
	$datekey = date("F j");
	$rcode = hexdec(md5($_SERVER[HTTP_USER_AGENT] . $site_key . $ts_random . $datekey));
	$code = substr($rcode, 2, 6);
	
	$circles=5;
	$lines=0;
	$width=100;
	$height=40;
	$font=5;
	
	$fontwidth = ImageFontWidth($font) * strlen($string);
	$fontheight = ImageFontHeight($font);
	
	$im = @imagecreate ($width,$height);
	$background_color = imagecolorallocate ($im, 255, 138, 0);
	$text_color = imagecolorallocate ($im, rand(200,255),rand(200,255),rand(200,255)); // Random Text
#rgb(1%, 51%, 87%)
	$r=0.87;$g=0.51;$b=0.0;
	for ($i=1;$i<=$circles;$i++) {
		$value=rand(200, 255);
		$randomcolor = imagecolorallocate ($im , $value*$r, $value*$g,$value*$b);
		imagefilledellipse($im,rand(0,$width-20),rand(0,$height-6),rand(15,70),rand(15,70),$randomcolor);
	}

	
	imagerectangle($im,0,0,$width-1,$height-1,$text_color);
	imagestring ($im, $font, 22, 12,$code,$text_color);
	for ($i=0;$i<$lines;$i++) {
		$y1=rand(14, 23);
		$y2=rand(15, 24);
		$randomcolor=imagecolorallocate($im, rand(100, 255), 0,rand(100, 255));
		imageline($im, 0, $y1, $width, $y2, $randomcolor);
	}

	header ("Content-type: image/jpeg");
	imagejpeg ($im,'',85);
	ImageDestroy($im);
	die();
}

function ts_is_human() {
	global $site_key;
//	global $ts_random;
	$ts_code=trim($_POST['ts_code']);
	$ts_random=$_POST['ts_random'];
	$datekey = date("F j");
	$rcode = hexdec(md5($_SERVER[HTTP_USER_AGENT] . $site_key . $ts_random . $datekey));
	$code = substr($rcode, 2, 6);

	return $ts_code==$code;

}

function ts_print_form() {
	$ts_random=rand();
	echo _("introduce el código de la imagen").":<br/>\n";
//	echo '<table><tr><td>';
	echo '<input type="hidden" name="ts_random" value="'.$ts_random.'" />';
	echo '<div class="tc"><img src="ts_image.php?ts_random='.$ts_random.'" alt="code number"/></div>';
//	echo '<tr><td><input type="text" size="20" name="ts_code" /></td></tr></table><br/>'."\n";
	echo '<input type="text" size="20" name="ts_code" /><br/>'."\n";
}

