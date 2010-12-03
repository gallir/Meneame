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

		return $globals['cache_dir'].sprintf("/%02x/%02x", ($key >> 8) & 255, $key & 255);
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
		return @mkdir(Upload::get_cache_dir($key), 0777, true);
	}
}

?>
