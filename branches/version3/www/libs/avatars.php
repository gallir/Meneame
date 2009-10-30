<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


function get_avatars_dir() {
	global $globals;
	return mnmpath.'/'.$globals['cache_dir'].'/avatars';
}

function is_avatars_enabled() {
	global $globals;
	return !empty($globals['cache_dir']) && is_writable(get_avatars_dir());
}

function avatars_manage_upload($user, $name) {
	global $globals;
	$subdir = get_avatars_dir() . '/'. intval($user%$globals['avatars_files_per_dir']);
	$time = $globals['now'];
	$file_base = $subdir . "/$user-$time";
	@mkdir(get_avatars_dir());
	@mkdir($subdir);
	if (!is_writable($subdir)) return false;
	avatars_remove_user_files($user);
	move_uploaded_file($_FILES[$name]['tmp_name'], $file_base . '-orig.img');
	$size = @getimagesize("$file_base-orig.img");
	avatar_resize("$file_base-orig.img", "$file_base-80.jpg", 80);
	$size = @getimagesize("$file_base-80.jpg");
	if (!($size[0] == 80 && $size[1] == 80 && ($mtime = avatars_db_store($user, "$file_base-80.jpg", $time)))) {
		// Mark FALSE in DB
		avatars_db_remove($user);
		avatars_remove_user_files($user);
		return false;
	}
	/*
	// Upload to DB and mark TRUE
	avatar_resize("$file_base-orig.img", "$file_base-20.jpg", 20);
	avatar_resize("$file_base-orig.img", "$file_base-25.jpg", 25);
	avatar_resize("$file_base-orig.img", "$file_base-40.jpg", 40);
	*/
	unlink("$file_base-orig.img");
	return $mtime;
}

function avatars_remove_user_files($user) {
	global $globals;
	$subdir = @get_avatars_dir() . '/'. intval($user%$globals['avatars_files_per_dir']);
	if ( $subdir && ($handle = @opendir( $subdir )) ) {
		while ( false !== ($file = readdir($handle))) {
			if ( preg_match("/^$user-/", $file) ) {
				@unlink($subdir . '/' . $file);
			}
		}
		closedir($handle);
	}
}

function avatars_check_upload_size($name) {
	global $globals;
	return $_FILES[$name]['size'] < $globals['avatars_max_size'];
}

function avatars_db_store($user, $file, $now) {
	global $db;
	$bytes = file_get_contents($file);
	if (strlen($bytes)>0 && strlen($bytes) < 30000) {
		$bytes = addslashes($bytes);
		$db->query("replace into avatars set avatar_id = $user, avatar_image='$bytes'");
		$db->query("update users set user_avatar = $now  where user_id=$user");
		return $now;
	}
	return false;
}

function avatars_db_remove($user) {
	global $db;
	$db->query("delete from avatars where avatar_id=$user");
	$db->query("update users set user_avatar = 0  where user_id=$user");
}

function avatar_get_from_file($user, $size) {
	global $globals, $db;

	$time = $db->get_var("select user_avatar from users where user_id=$user");
	if(! $time > 0) return false;
	$file = get_avatars_dir() . '/'. intval($user%$globals['avatars_files_per_dir']) . "/$user-$time-$size.jpg";
	if (is_readable($file)) {
		return  file_get_contents($file);
	} else {
		return false;
	}

}

function avatar_get_from_db($user, $size=0) {
	global $db, $globals;
	$img = $db->get_var("select avatar_image from avatars where avatar_id=$user");
	if (!strlen($img) > 0) {
		return false;
	}
	$time = $db->get_var("select user_avatar from users where user_id=$user");
	$subdir = get_avatars_dir() . '/'. intval($user%$globals['avatars_files_per_dir']);
	$file_base = $subdir . "/$user-$time";
	@mkdir(get_avatars_dir());
	@mkdir($subdir);
	if (!is_writable($subdir)) return false;
	file_put_contents ($file_base . '-80.jpg', $img);
	if ($size > 0 && $size != 80 && in_array($size, $globals['avatars_allowed_sizes'])) {
		avatar_resize("$file_base-80.jpg", "$file_base-$size.jpg", $size);
		return file_get_contents("$file_base-$size.jpg");
	}
	return $img;
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
	imagejpeg($dst_img,$outfile,80);
}
