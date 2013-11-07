<?php
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

// Mofified from:
/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
*
*/

class SimpleImage {
	var $image;
	var $image_type;

	function mime() {
		if ($this->image_type) return image_type_to_mime_type($this->image_type);
		else return 'image/';
	}

	function load($filename) {
		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];
		$this->filename = $filename;
		switch($this->image_type) {
		case IMAGETYPE_JPEG:
			$this->image = @imagecreatefromjpeg($filename);
			return true;
		case IMAGETYPE_GIF:
			$this->image = @imagecreatefromgif($filename);
			return true;
		case IMAGETYPE_PNG:
			$this->image = @imagecreatefrompng($filename);
			return true;
		case IMAGETYPE_WBMP:
			$this->image = @imagecreatefromwbmp($filename);
			return true;
		default:
			$this->image = false;
		}
		if ($this->image) return true;
		else return false;
	}

	function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75) {
		if (!$this->image) return false;

		if ($image_type == -1) {
			// Save in the same format
			$image_type = $this->image_type;
		}

		switch($image_type) {
		case IMAGETYPE_JPEG:
			$res = imagejpeg($this->image, $filename, $compression);
			break;
		case IMAGETYPE_GIF:
			$res = imagegif($this->image, $filename);
			break;
		case IMAGETYPE_PNG:
			$res = imagepng($this->image, $filename);
			break;
		default:
			syslog(LOG_INFO, "IMAGE not type found: $image_type");
			return false;
		}
		if ($res) return true;
		else return false;
	}

	function output($image_type=IMAGETYPE_JPEG) {
		switch($image_type) {
		case IMAGETYPE_JPEG:
			imagejpeg($this->image);
		case IMAGETYPE_GIF:
			imagegif($this->image);
		case IMAGETYPE_PNG:
			imagepng($this->image);
		default:
			return false;
		}
		return true;
	}

	function getWidth() {
		return imagesx($this->image);
	}

	function getHeight() {
		return imagesy($this->image);
	}

	function resizeToHeight($height) {
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize($width,$height);
	}

	function resizeToWidth($width) {
		$ratio = $width / $this->getWidth();
		$height = $this->getheight() * $ratio;
		$this->resize($width,$height);
	}

	function scale($scale) {
		$width = $this->getWidth() * $scale/100;
		$height = $this->getheight() * $scale/100;
		$this->resize($width,$height);
	}

	function resize($width, $height, $crop = false) {
		if (! $this->image) return false;
		if (! $crop) {
			$src_x = 0;
			$src_y = 0;
			$src_w = $this->getWidth();
			$src_h = $this->getHeight();
		} else {
			$rel_w = $width / $this->getWidth();
			$rel_h = $height / $this->getHeight();
			$rel_max = max($rel_w, $rel_h);
			$view_w = round($width / $rel_max, 0);
			$view_h = round($height / $rel_max, 0);
			$src_x = round(($this->getWidth()-$view_w) / 2, 0);
			$src_y = round(($this->getHeight()-$view_h) / 2, 0);
			$src_w = $view_w;
			$src_h = $view_h;
		}
		$new_image = imagecreatetruecolor($width, $height);
		imagefill($new_image, 0, 0, imagecolorallocate($new_image, 255, 255, 255));
		if (! @imagecopyresampled($new_image, $this->image, 0, 0, $src_x, $src_y, $width, $height, $src_w, $src_h)) return false;
		$this->image = $new_image;
		return true;
	}


	/* Extracted from http://www.neilyoungcv.com/blog/code-share/image-resizing-with-php-exif-orientation-fix/ */
	function flip() {

		$width = $this->getWidth();
		$height = $this->getHeight();

		// Truecolor provides better results, if possible.
		if (function_exists('imageistruecolor') && imageistruecolor($image)) {
			$tmp = imagecreatetruecolor(1, $height);
		} else {
			$tmp = imagecreate(1, $height);
		}

		$x2 = $x + $width - 1;

		for ($i = (int)floor(($width - 1) / 2); $i >= 0; $i--) {
			// Backup right stripe.
			imagecopy($tmp, $this->image, 0, 0, $x2 - $i, $y, 1, $height);

			// Copy left stripe to the right.
			imagecopy($this->image, $this->image, $x2 - $i, $y, $x + $i, $y, 1, $height);

			// Copy backuped right stripe to the left.
			imagecopy($this->image, $tmp, $x + $i,	$y, 0, 0, 1, $height);
		}

		imagedestroy($tmp);
		return true;
	}

	function rotate_exif($filename = false) {

		if (! $filename && $this->filename) {
			$filename = $this->filename;
		}

		$exif = @exif_read_data($filename);
		if (!$exif) return false;

		$ort = $exif['Orientation'];
		if (empty($ort) || $ort < 2 || $ort > 8) return false;

		if (! $this->image) {
			$this->load($filename);
		}

		// exif only supports jpg in our supported file types
		if (! $this->image || $this->image_type != IMAGETYPE_JPEG) return false;

		//determine what oreientation the image was taken at
		switch($ort) {

		case 2: // horizontal flip
			$this->flip();
			break;

		case 3: // 180 rotate left
			$this->image = imagerotate($this->image, 180, -1);
			break;

		case 4: // vertical flip
			$this->flip();
			break;

		case 5: // vertical flip + 90 rotate right
			$this->flip();
			$this->image = imagerotate($this->image, -90, -1);
			break;

		case 6: // 90 rotate right
			$this->image = imagerotate($this->image, -90, -1);
			break;

		case 7: // horizontal flip + 90 rotate right
			$this->flip();
			$this->image = imagerotate($this->image, -90, -1);
			break;

		case 8: // 90 rotate left
			$this->image = imagerotate($this->image, 90, -1);
			break;

		default:
			return false;
		}

		return true;
	}

}
