<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


class Upload {
	static function get_url($type, $id, $version = 0, $ts = 0, $mime='image/jpg') {
		global $globals;
		return $globals['scheme'].'//'.get_server_name().$globals['base_url_general']."backend/media?type=$type&amp;id=$id&amp;version=$version&amp;ts=$ts&amp;".str_replace('/', '.', $mime);
	}

	static function is_thumb_public($type) {
		$types = array('post', 'comment', 'sub_logo', 'link');
		return in_array($type, $types);
	}

	static function thumb_sizes($type, $key = false) {
		global $globals;

		$size = $globals['media_thumb_size'];
		switch ($type) {
			case 'sub_logo':
				$all = array('media_thumb' => array(false, $globals['media_sublogo_height'] * 2, false));
				break;
			case 'link':
				if (! empty($globals['thumb_size'])) {
					$size = $globals['thumb_size'];
				}
			default:
				$all = array('media_thumb' => array($size, $size, true),
					'media_thumb_2x' => array($size * 2, $size * 2, true));
		}

		if ($key) {
			return $all[$key];
		} else {
			arsort($all); // Ordered by size, descending
			return $all;
		}
	}

	static function get_cache_relative_dir($key = false) {
		global $globals;

		if (!$key) return $globals['cache_dir'];
		else return $globals['cache_dir'].sprintf("/%02x/%02x", ($key >> 16) & 255, ($key >> 8) & 255);
	}

	static function get_cache_dir($key = false) {
		global $globals;

		// Very fast cache dir generator for two levels
		// mask == 2^8 - 1 or 1 << 8 -1
		if (! $key) {
			return mnmpath.'/'.$globals['cache_dir'];
		} else {
			return mnmpath. '/' . Upload::get_cache_relative_dir($key);
		}
	}

	static function create_cache_dir($key = false) {
		global $globals;

		if (file_exists(Upload::get_cache_dir($key))) return true;
		$dir = Upload::get_cache_dir($key);
		$old_mask = umask(0);
		$res = @mkdir($dir, 0777, true);
		umask($old_mask);
		return $res;
	}

	static function current_user_limit_exceded($size) {
		global $current_user, $globals;

		// Check current_user file upload limits
		if ($size > $globals['media_max_size']) return _('tamaño excedido');
		if ($current_user->user_karma < $globals['media_min_karma']) return _('karma bajo');
		if (Upload::user_uploads($current_user->user_id, 24) > $globals['media_max_upload_per_day']) return _('máximas subidas diarias excedidas');
		if (Upload::user_bytes_uploaded($current_user->user_id, 24) > $globals['media_max_bytes_per_day'] * 1.2) return _('máximos bytes por día excedidos');
		return false;
	}


	static function user_bytes_uploaded($user, $hours = false) {
		global $db;

		if (! $user > 0) return 0;
		if ($hours) $date_limit = "and date > date_sub(now(), interval $hours hour)";
		else $date_limit = '';

		return intval($db->get_var("select sum(size) from media where user = $user $date_limit"));
	}

	static function user_uploads($user, $hours = false, $type = false) {
		global $db;

		if (! $user > 0) return 0;

		if ($hours) $date_limit = "and date > date_sub(now(), interval $hours hour)";
		else $date_limit = '';

		if ($type) $media_type = "and type = '$type'";
		else $media_type = '';

		return intval($db->get_var("select count(*) from media where user = $user $date_limit $media_type"));
	}

	function __construct($type, $id, $version = 0, $time = false) {
		global $globals;

		$this->type = $type;
		$this->id = $id;
		$this->to = 0;
		$this->access = 'restricted';
		$this->version = $version;
		if (! $time ) {
			$this->date = $globals['now'];
		} else {
			$this->date = $time;
		}
		$this->dim1 = $this->dim2 = 0;
	}

	function store() {
		global $db, $current_user, $globals;

		if (! $this->user) $this->user = $current_user->user_id;

		$mime = $db->escape($this->mime);
		$extension = $db->escape($this->extension);
		$access = $db->escape($this->access);
		$db->query("REPLACE INTO media (type, id, version, user, `to`, access, mime, extension, size, date, dim1, dim2) VALUES ('$this->type', $this->id, $this->version, $this->user, $this->to, '$access', '$mime', '$extension', $this->size, FROM_UNIXTIME($this->date), $this->dim1, $this->dim2)");

		$this->backup();
		return true;
	}

	function read() {
		global $db, $current_user;

		/* Check the original exists */
		$extra_tables = $extra_where = "";

		switch ($this->type) {
			case 'private':
				$extra_tables = ', privates';
				$extra_where = 'AND `media.to` = privates.id';
				break;
			case 'post':
				$extra_tables = ', posts';
				$extra_where = 'AND `media.to` = posts.post_id';
				break;
			case 'comment':
				$extra_tables = ', comments';
				$extra_where = 'AND `media.to` = comments.comment_id';
				break;
		}

		if(($result = $db->get_row("SELECT type, id, version, user, `to`, access, mime, extension, size, UNIX_TIMESTAMP(date) as date, dim1, dim2 FROM media WHERE type = '$this->type' and id = $this->id and version = $this->version"))) {
			foreach(get_object_vars($result) as $var => $value) $this->$var = $value;
			$this->read = true;
			return true;
		}
		$this->read = false;
		return false;
	}

	function delete() {
		global $db, $globals;

		// Check is read to read all data
		if (! $this->read) $this->read();
		if (! $this->read) return false;

		$this->clean();
		$this->delete_backup();
		$db->query("delete from media where type = '$this->type' and id = $this->id and version = $this->version");
	}

	function clean() {
		$this->delete_thumbs();
		@unlink($this->pathname());
	}

	function delete_thumbs() {
		foreach (Upload::thumb_sizes($this->type) as $k => $s) {
			$pattern = $this->thumb_pathname($k) . '.*';
			array_map('unlink', glob($pattern));
		}
	}

	function create_thumbs($key = false) {
		if ($this->type == 'private' || $this->access == 'private') return false;
		$pathname = $this->pathname();

		if (! file_exists($pathname)) {
			if (! $this->restore()) return false;
		}

		require_once(mnminclude."simpleimage.php");
		$thumb = new SimpleImage();
		$thumb->load($pathname);
		if ( ! $thumb->load($pathname)) {
			$alternate_image = mnmpath . "/img/common/picture02.png";
			syslog(LOG_INFO, "Meneame, trying alternate thumb ($alternate_image) for $pathname");
			if (!$thumb->load($alternate_image)) return false;
		}

		$res = 0;
		foreach (Upload::thumb_sizes($this->type) as $k => $s) {
			if ($key && $key != $k) continue; // Generate just what was requested
			$thumb->resize($s[0], $s[1], $s[2]);
			if (($name = $thumb->save($this->thumb_pathname($k))) ) { // SimpleImage->save return the final name
				$res++;
				@chmod($name, 0777);
				$this->thumb = $thumb;
			}
		}
		return $res;
	}

	// Store the image from a file uploaded from the form
	function from_temporal($file, $type = false) {
		global $current_user, $globals;

		// Check __again__ the limits
		Upload::current_user_limit_exceded($file['size']);

		if ($type && ! preg_match("/^$type\/[^ ]+$/", $file['type'])) return false;
		$this->mime = $file['type'];
		$this->user = $current_user->user_id;
		Upload::create_cache_dir($this->id);
		if (move_uploaded_file($file['tmp_name'], $this->pathname())) {
			@chmod($this->pathname(), 0777);
			$this->check_size_and_rotation($this->pathname());
			$this->delete_thumbs();
			$this->create_thumbs();
			return $this->store();
		} else {
			syslog(LOG_INFO, "Meneame, error moving to " . $this->pathname());
		}
		return false;
	}

	// Store the image from a file uploaded with AJAX
	function from_tmp_upload($filename, $type) {
		global $current_user, $globals;

		$pathname = Upload::get_cache_dir() . '/tmp/' . $filename;
		if (! file_exists($pathname)) return false;

		// Check __again__ the limits
		Upload::current_user_limit_exceded(filesize($pathname));

		$this->mime = $type;
		$this->user = $current_user->user_id;
		Upload::create_cache_dir($this->id);
		if (rename($pathname, $this->pathname())) {
			$this->check_size_and_rotation($this->pathname());
			$this->delete_thumbs();

			// Check if it exists a thumb and save it
			$thumbname = Upload::get_cache_dir() . "/tmp/tmp_thumb-$filename";
			if (file_exists($thumbname)) {
				@unlink($thumbname);
			}
			$this->create_thumbs();
			return $this->store();
		} else {
			syslog(LOG_INFO, "Meneame, error moving to " . $this->pathname());
		}
		return false;
	}

	function filename() {
		return sprintf("%s-%d-%d-%d.media", $this->type, $this->user, $this->id, $this->version);
	}

	function pathname() {
		return Upload::get_cache_dir($this->id).'/'.$this->filename();
	}

	function path() {
		return Upload::get_cache_dir($this->id);
	}

	function url() {
		global $globals;

		return $globals['base_url'].Upload::get_cache_relative_dir($this->id).'/'.$this->filename();
	}

	function file_exists() {
		return file_exists($this->pathname());
	}

	function readfile() {
		if (! file_exists($this->pathname())) {
			$this->restore();
		}
		return readfile($this->pathname());
	}

	function filesize() {
		return filesize($this->pathname());
	}


	// Call S3 functions
	function backup() {
		global $globals;

		if ($globals['Amazon_S3_media_bucket'] && $globals['Amazon_S3_upload']) {
			return Media::put($this->pathname(), $this->type);
		}
		return true;
	}

	function restore() {
		global $globals;

		if ($globals['Amazon_S3_media_bucket']) {
			Upload::create_cache_dir($this->id);
			$res = Media::get($this->filename(), $this->type, $this->pathname());
			@chmod($this->pathname(), 0777);
			return $res;
		}
	}

	function delete_backup() {
		global $globals;

		if ($globals['Amazon_S3_media_bucket']) {
			return Media::rm($this->type.'/'.$this->filename());
		}
	}

	function thumb_pathname($key = 'media_thumb') {
		return $this->path() . "/$key-$this->type-$this->id";
	}

	function check_size_and_rotation($pathname) {
		require_once(mnminclude."simpleimage.php");

		$original = $pathname;
		$tmp = "$pathname.tmp";

		$max_size = 2048;

		$image = new SimpleImage();
		if ($image->rotate_exif($pathname)) {
			if ($image->save($tmp)) {
				$pathname = $tmp;
				clearstatcache();
			}
		}

		if ($image->load($pathname)) {
			$dim1 = $image->getWidth();
			$dim2 = $image->getHeight();
			$this->extension = $image->extension;
		}

		if (filesize($pathname) > 1024*1024) { // Bigger than 1 MB
			if ($image->image &&  ($dim1 > $max_size || $dim2 > $max_size)) {
				if ($dim1 > $dim2) {
					$image->resizeToWidth($max_size);
				} else {
					$image->resizeToHeight($max_size);
				}
				$dim1 = $image->getWidth();
				$dim2 = $image->getHeight();
				if ($image->save($tmp)) {
					$pathname = $tmp;
					clearstatcache();
				}
			}
		}

		if ($pathname != $original && file_exists($pathname)) {
			 if (! @rename($pathname, $original)) {
				syslog(LOG_INFO, "Error renaming file $pathname -> $original");
				@unlink($pathname);
			}
		}

		$this->size = filesize($original);
		@chmod($original, 0777);

		$dim1 = $image->getWidth();
		$dim2 = $image->getHeight();

		if ($dim1 > 0) {
			$this->dim1 = $dim1;
		}
		if ($dim2 > 0) {
			$this->dim2 = $dim2;
		}

		return true;
	}
}

