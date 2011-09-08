<?
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


class Upload {
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
		syslog(LOG_INFO, "Meneame, creating cache dir " . $dir);
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

	static function user_uploads($user, $hours = false) {
		global $db;

		if (! $user > 0) return 0;
		if ($hours) $date_limit = "and date > date_sub(now(), interval $hours hour)";
		else $date_limit = '';

		return intval($db->get_var("select count(*) from media where user = $user $date_limit"));
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
		$access = $db->escape($this->access);
		$db->query("REPLACE INTO media (type, id, version, user, `to`, access, mime, size, date, dim1, dim2) VALUES ('$this->type', $this->id, $this->version, $this->user, $this->to, '$access', '$mime', $this->size, FROM_UNIXTIME($this->date), $this->dim1, $this->dim2)");
		$this->backup();
		return true;
	}

	function read() {
		global $db, $current_user;

		if(($result = $db->get_row("SELECT type, id, version, user, `to`, access, mime, size, UNIX_TIMESTAMP(date) as date, dim1, dim2 FROM media WHERE type = '$this->type' and id = $this->id and version = $this->version"))) {
			foreach(get_object_vars($result) as $var => $value) $this->$var = $value;
			$this->read = true;
			return true;
		}
		$this->read = false;
		return false;
	}

	function delete() {
		global $db, $globals;

		$this->clean();
		$this->delete_backup();
		$db->query("delete from media where type = '$this->type' and id = $this->id and version = $this->version");
	}

	function clean() {
		@unlink($this->pathname());
		@unlink($this->thumb_pathname());
	}

	function from_temporal($file, $type = false) {
		global $current_user, $globals;

		// Check __again__ the limits
		Upload::current_user_limit_exceded($file['size']);

		if ($type && ! preg_match("/^$type\/[^ ]+$/", $file['type'])) return false;
		$this->mime = $file['type'];
		$this->size = $file['size'];
		$this->user = $current_user->user_id;
		Upload::create_cache_dir($this->id);
		if (move_uploaded_file($file['tmp_name'], $this->pathname())) {
			@unlink($this->thumb_pathname());
			$this->create_thumb($globals['media_thumb_size']);
			return $this->store();
		} else {
			syslog(LOG_INFO, "Meneame, error moving to " . $this->pathname());
		}
		return false;
	}

	function from_tmp_upload($filename, $type) {
		global $current_user, $globals;

		$pathname = Upload::get_cache_dir() . '/tmp/' . $filename;
		if (! file_exists($pathname)) return false;

		// Check __again__ the limits
		Upload::current_user_limit_exceded(filesize($pathname));

		$this->mime = $type;
		$this->size = filesize($pathname);
		$this->user = $current_user->user_id;
		Upload::create_cache_dir($this->id);
		if (rename($pathname, $this->pathname())) {
			@unlink($this->thumb_pathname());

			// Check if it exists a thumb adn save it in jpg
			$thumbname = Upload::get_cache_dir() . "/tmp/tmp_thumb-$filename";
			if (file_exists($thumbname)) {
				if (preg_match('/\.(jpeg|jpg)$/i', $filename)) {
					// If it's already jpg, just rename it
					rename($thumbname, $this->thumb_pathname());
				} else {
					// else convert it
					require_once(mnminclude."simpleimage.php");
					$thumb = new SimpleImage();
					$thumb->load($thumbname);
					$thumb->save($this->thumb_pathname());
				}
			} else {
				$this->create_thumb($globals['media_thumb_size']);
			}

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
			return Media::get($this->filename(), $this->type, $this->pathname());
		}
	}

	function delete_backup() {
		global $globals;

		if ($globals['Amazon_S3_media_bucket']) {
			return Media::rm($this->type.'/'.$this->filename());
		}
	}

	function thumb_pathname() {
		return $this->path() . "/media_thumb-$this->type-$this->id.jpg";
	}

	function create_thumb($x = 40, $y = false) {
		$pathname = $this->pathname();

		if (! file_exists($pathname)) {
			if (! $this->restore()) return false;
		}

		require_once(mnminclude."simpleimage.php");
		if (! $y) $y = $x;
		$thumb = new SimpleImage();
		$thumb->load($pathname);
		if ( ! $thumb->load($pathname)) {
			$alternate_image = mnmpath . "/img/common/picture01-40x40.png";
			syslog(LOG_INFO, "Meneame, trying alternate thumb ($alternate_image) for $pathname");
			if (!$thumb->load($alternate_image)) return false;
		}
		$thumb->resize($x, $y, true);
		if ($thumb->save($this->thumb_pathname())) {
			$this->thumb = $thumb;
			return true;
		}
		return false;
	}
}

?>
