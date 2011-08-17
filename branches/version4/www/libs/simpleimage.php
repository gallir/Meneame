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

	function load($filename) {
		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];
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
		default:
			$this->image = false;
		}
		if ($this->image) return true;
		else return false;
	}

	function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75) {
		if (!$this->image) return false;
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
		if (! @imagecopyresampled($new_image, $this->image, 0, 0, $src_x, $src_y, $width, $height, $src_w, $src_h)) return false;
		$this->image = $new_image;
		return true;
	}
}
