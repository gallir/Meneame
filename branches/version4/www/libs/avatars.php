<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


function is_avatars_enabled() {
	global $globals;
	return $globals['Amazon_S3_media_url'] || ($globals['cache_dir'] && is_writable(Upload::get_cache_dir()));
}

function avatars_get_from_url($user, $url) {
	$res = get_url($url, $url, 1000000);
	if ($res && strlen($res['content']) < 1000000) { // Image is smaller than our limit
		$tmpfname = tempnam('/tmp', 'avatar');
		if ($tmpfname) {
			$bytes = file_put_contents($tmpfname, $res['content']);
			if($bytes) {
				avatars_manage_upload($user, false, $tmpfname);
			}
			@unlink($tmpfname);
			return true;
		}
	}
	return false;
}

function avatars_manage_upload($user, $name, $filename = false) {
	global $globals;

	$time = $globals['now'];

	if (!$globals['Amazon_S3_local_cache'] && $globals['Amazon_S3_media_bucket'] && is_writable('/tmp')) {
		$subdir = '/tmp';
	} else {
		if (! Upload::create_cache_dir($user)) return false;
		$subdir = Upload::get_cache_dir($user);
	}
	if (!is_writable($subdir)) return false;
	$file_base = $subdir . "/$user-$time";

	// First check the image is valid before moving and deleting
	// If $name is provided, the file was uploaded from a form
	if ($name) {
		$filename = $_FILES[$name]['tmp_name'];
	}
	$original_size = @getimagesize($filename);
	if ($original_size == false) {
		@unlink($filename);
		return false;
	}

	avatars_remove_user_files($user);

	// If $name is provided, the file was uploaded from a form
	if($name) {
		move_uploaded_file($filename, $file_base . '-orig.img');
	} else {
		rename($filename, $file_base . '-orig.img');
	}

	avatar_resize("$file_base-orig.img", "$file_base-80.jpg", 80);
	$size = @getimagesize("$file_base-80.jpg");


	if (!($size[0] == 80 && $size[1] == 80 && ($mtime = avatars_db_store($user, "$file_base-80.jpg", $time)))) {
		// Mark FALSE in DB
		avatars_db_remove($user);
		avatars_remove_user_files($user);
		return false;
	}
	avatar_resize("$file_base-orig.img", "$file_base-20.jpg", 20);
	avatar_resize("$file_base-orig.img", "$file_base-25.jpg", 25);
	avatar_resize("$file_base-orig.img", "$file_base-40.jpg", 40);

	// Store in S3 the other images and a higher quality one
	if ($globals['Amazon_S3_media_bucket']) {
		$max_size = min($original_size[0], $original_size[1], 200);
		avatar_resize("$file_base-orig.img", "$file_base.jpg", $max_size);
		if ( Media::put("$file_base-20.jpg", 'avatars')
				&& Media::put("$file_base-25.jpg", 'avatars')
				&& Media::put("$file_base-40.jpg", 'avatars')
				&& Media::put("$file_base.jpg", 'avatars') ) {
			@unlink("$file_base.jpg");
			if (! $globals['Amazon_S3_local_cache']) {
				@unlink("$file_base-20.jpg");
				@unlink("$file_base-25.jpg");
				@unlink("$file_base-40.jpg");
				@unlink("$file_base-80.jpg");
			}
		}
	}
	@unlink("$file_base-orig.img");
	return $mtime;
}

function avatars_check_upload_size($name) {
	global $globals;
	return $_FILES[$name]['size'] < $globals['avatars_max_size'];
}

function avatars_db_store($user, $file, $now) {
	global $db, $globals;

	// Store in S3
	if ($globals['Amazon_S3_media_bucket']) {
		if (Media::put($file, 'avatars')) {
			$db->query("update users set user_avatar = $now  where user_id=$user");
			return $now;
		} else {
			return false;
		}
	}

	// Store locally
	$bytes = file_get_contents($file);
	if (strlen($bytes)>0 && strlen($bytes) < 30000) {
		$bytes = addslashes($bytes);
		$db->query("replace into avatars set avatar_id = $user, avatar_image='$bytes'");
		$db->query("update users set user_avatar = $now  where user_id=$user");
		return $now;
	}
	return false;
}

function avatar_get_from_db($user, $size=0) {
	global $db, $globals;

	if (! in_array($size, $globals['avatars_allowed_sizes'])) return false;

	$time = $db->get_var("select user_avatar from users where user_id=$user");

	if (!$globals['Amazon_S3_local_cache'] && $globals['Amazon_S3_media_bucket'] && is_writable('/tmp')) {
		$subdir = '/tmp';
	} else {
		if (! Upload::create_cache_dir($user)) return false;
		$subdir = Upload::get_cache_dir($user);
	}
	if (!is_writable($subdir)) return false;
	$file_base = $subdir . "/$user-$time";

	$delete = false;
	$original = false;
	if ($globals['Amazon_S3_media_bucket']) {
		// Get avatar from S3
		// Try up to 3 times to download from Amazon
		$try = 0;
		while ($original == false && $try < 3) {
			if (Media::get("$user-$time-$size.jpg", 'avatars', "$file_base-$size.jpg")) {
				return file_get_contents("$file_base-$size.jpg");
			}
			if (Media::get("$user-$time.jpg", 'avatars', "$file_base-orig.jpg")) {
				$delete_it = true;
				$original = "$file_base-orig.jpg";
			} elseif ((is_readable($file_base . '-80.jpg') && filesize($file_base . '-80.jpg') > 0)
						|| Media::get("$user-$time-80.jpg", 'avatars', "$file_base-80.jpg") ) {
				$original = $file_base . '-80.jpg';
			} else {
				$try++;
				usleep(rand(1,20)); // Wait a little to minimize race-conditions
			}
		}
		if (! $original) { // The images were not found in S3
			if (($buckets = Media::buckets(false)) && in_array($globals['Amazon_S3_media_bucket'], $buckets)
					&& is_writable(mnmpath.'/'.$globals['cache_dir'])) { // Double check
				avatars_remove($user);
			}
			return false;
		}

	} else {
		//Get from DB
		if (!is_readable($file_base . '-80.jpg')) {
			$img = $db->get_var("select avatar_image from avatars where avatar_id=$user");
			if (!strlen($img) > 0) {
				if (is_writable(mnmpath.'/'.$globals['cache_dir'])) { // Double check
					avatars_remove($user);
				}
				return false;
			}
			file_put_contents ($file_base . '-80.jpg', $img);
			$original = $file_base . '-80.jpg';
		}
	}

	if ($size > 0 && $size != 80 ) {
		avatar_resize($original, "$file_base-$size.jpg", $size);
		if ($delete_it) @unlink($original);
	}

	return file_get_contents("$file_base-$size.jpg");
}

function avatar_get_from_file($user, $size) {
	global $globals, $db;

	$time = $db->get_var("select user_avatar from users where user_id=$user");
	if(! $time > 0) return false;
	$file = Upload::get_cache_dir($user) . "/$user-$time-$size.jpg";
	if (is_readable($file)) {
		return	file_get_contents($file);
	} else {
		return false;
	}

}

function avatar_resize($infile,$outfile,$size) {
	$image_info = getImageSize($infile);
	switch ($image_info['mime']) {
		case 'image/gif':
		if (imagetypes() & IMG_GIF)  {
			$src_img = imageCreateFromGIF($infile) ;
		} else {
			$ermsg = 'GIF images are not supported<br />';
		}
		break;
		case 'image/jpeg':
		if (imagetypes() & IMG_JPG)  {
			$src_img = imageCreateFromJPEG($infile) ;
		} else {
			$ermsg = 'JPEG images are not supported<br />';
		}
		break;
		case 'image/png':
		if (imagetypes() & IMG_PNG)  {
			$src_img = imageCreateFromPNG($infile) ;
		} else {
			$ermsg = 'PNG images are not supported<br />';
		}
		break;
		case 'image/wbmp':
		if (imagetypes() & IMG_WBMP)  {
			$src_img = imageCreateFromWBMP($infile) ;
		} else {
			$ermsg = 'WBMP images are not supported<br />';
		}
		break;
		default:
		$ermsg = $image_info['mime'].' images are not supported<br />';
		break;
	}
	if (isset($ermsg)) {
		echo "Error: $ermsg";
		die;
	}
	$dst_img = ImageCreateTrueColor($size,$size);
	imagecopyresampled($dst_img,$src_img,0,0,0,0,$size,$size,imagesx($src_img),imagesy($src_img));
	imagejpeg($dst_img,$outfile,85);
}

function avatars_remove($user) {
	avatars_remove_user_files($user);
	avatars_db_remove($user);
}

function avatars_db_remove($user) {
	global $db;
	$db->query("delete from avatars where avatar_id=$user");
	$db->query("update users set user_avatar = 0 where user_id=$user");
}

function avatars_remove_user_files($user) {
	global $globals;
	if ($globals['Amazon_S3_media_bucket']) {
		Media::rm("avatars/$user-*");
	}

	if ($globals['Amazon_S3_local_cache'] || ! $globals['Amazon_S3_media_bucket'] ) {
		$subdir = Upload::get_cache_dir($user);
		if ( $subdir && ($handle = @opendir( $subdir )) ) {
			while ( false !== ($file = readdir($handle))) {
				if ( preg_match("/^$user-/", $file) ) {
					@unlink($subdir . '/' . $file);
				}
			}
			closedir($handle);
		}
	}
}
?>
