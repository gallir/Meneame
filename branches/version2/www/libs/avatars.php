<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


function get_avatars_dir() {
	global $globals;
	return mnmpath.'/'.$globals['avatars_dir'];
}

function is_avatars_enabled() {
	global $globals;
	return !empty($globals['avatars_dir']) && is_writable(get_avatars_dir());
}

function avatars_manage_upload($user, $name) {
	global $globals;
	$subdir = get_avatars_dir() . '/'. intval($user/$globals['avatars_files_per_dir']);
	$file_base = $subdir . '/' . $user;
	@mkdir($subdir);
	if (!is_writable($subdir)) return false;
	avatars_remove_user_files($user);
	move_uploaded_file($_FILES[$name]['tmp_name'], $file_base . '-orig.img');
	$size = @getimagesize("$file_base-orig.img");
	system("convert -quality 85 -resize 80x80! $file_base-orig.img $file_base-80.jpg");
	$size = @getimagesize("$file_base-80.jpg");
	if (!($size[0] == 80 && $size[1] == 80 && avatars_db_store($user, "$file_base-80.jpg"))) {
		// Mark FALSE in DB
		avatars_db_remove($user);
		avatars_remove_user_files($user);
		return false;
	}
	// Upload to DB and mark TRUE
	system("convert -quality 85 -resize 20x20 $file_base-orig.img $file_base-20.jpg");
	system("convert -quality 85 -resize 25x25 $file_base-orig.img $file_base-25.jpg");
	unlink("$file_base-orig.img");
	return true;
}

function avatars_remove_user_files($user) {
	global $globals;
	$subdir = get_avatars_dir() . '/'. intval($user/$globals['avatars_files_per_dir']);
	if ( ($handle = opendir( $subdir )) ) {
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

function avatars_db_store($user, $file) {
	global $db;
	echo "\n<!-- uploading $$usr-$file- -->\n";
	$bytes = file_get_contents($file);
	if (strlen($bytes)>0 && strlen($bytes) < 30000) {
		$bytes = addslashes($bytes);
		$db->query("replace into avatars set avatar_id = $user, avatar_image='$bytes'");
		$db->query("update users set user_avatar = 1  where user_id=$user");
		return true;
	}
	return false;
}

function avatars_db_remove($user) {
	global $db;
	$db->query("delete from avatars where avatar_id=$user");
	$db->query("update users set user_avatar = 0  where user_id=$user");
}

function avatar_get_from_file($user, $size) {
	global $globals;

	$file = get_avatars_dir() . '/'. intval($user/$globals['avatars_files_per_dir']) . '/' . $user . "-$size.jpg";
	if (is_readable($file))  return file_get_contents($file);
	else return false;

}

function avatar_get_from_db($user, $size=0) {
	global $db, $globals;
	$img = $db->get_var("select avatar_image from avatars where avatar_id=$user");
	if (!strlen($img) > 0) {
		return false;
	}
	$subdir = get_avatars_dir() . '/'. intval($user/$globals['avatars_files_per_dir']);
	$file_base = $subdir . '/' . $user;
	@mkdir($subdir);
	if (!is_writable($subdir)) return false;
	file_put_contents ($file_base . '-80.jpg', $img);
	if ($size > 0 && $size != 80 && in_array($size, $globals['avatars_allowed_sizes'])) {
		system("convert -quality 85  -resize ${size}x$size $file_base-80.jpg $file_base-$size.jpg");
		return file_get_contents("$file_base-$size.jpg");
	}
	return $img;
}
