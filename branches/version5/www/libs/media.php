<?
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (!class_exists('S3')) require_once 'S3.php';

class Media {
//	$globals['Amazon_access_key']
//	$globals['Amazon_secret_key']
//	$globals['Amazon_S3_media_bucket']
//	$globals['Amazon_S3_media_url']

//	private static $__access_key = $globals['Amazon_access_key'];
//	private static $__secret_key = $globals['Amazon_secret_key'];
	static $lastHTTPCode = 0;


	public static function put($file, $type, $name = false) {
		global $globals;
		if (!$name) $name = baseName($file);
		if (empty($type)) $type = 'notype';
		$uri = "$type/$name";
		S3::setAuth($globals['Amazon_access_key'], $globals['Amazon_secret_key']);
		$response = S3::putObjectFile($file, $globals['Amazon_S3_media_bucket'], $uri, S3::ACL_PUBLIC_READ, array(), array(
			"Cache-Control" => "max-age=864000",
			"Expires" => gmdate("D, d M Y H:i:s T", time() + 864000) ));

		if ($response) {
			// syslog(LOG_NOTICE, "Meneame, uploaded $uri to S3");
			$lastHTTPCode = 200;
			return true;
		}
		syslog(LOG_NOTICE, "Meneame, failed to upload $uri to S3");
		$lastHTTPCode = 0;
		return false;
	}

	public static function get($file, $type, $output = false) {
		global $globals;
		$uri = "$type/$file";
		S3::setAuth($globals['Amazon_access_key'], $globals['Amazon_secret_key']);
		$object = @S3::getObject($globals['Amazon_S3_media_bucket'], $uri, $output);
		$lastHTTPCode =  $object->code;
		
		if ($object) {
			return $object;
		}
		// syslog(LOG_NOTICE, "Meneame, failed to get $uri from S3 to $output code: " . S3::$lastHTTPCode);
		@unlink($output);
		return false;
	}

	public static function ls($pattern = null) {
		global $globals;
		S3::setAuth($globals['Amazon_access_key'], $globals['Amazon_secret_key']);
		$list = S3::getBucket($globals['Amazon_S3_media_bucket'], $pattern);
		return $list;
	}

	public static function rm($pattern) {
		global $globals;
		S3::setAuth($globals['Amazon_access_key'], $globals['Amazon_secret_key']);
		if (preg_match('/\*$/', $pattern)) {
			$pattern = preg_replace('/\*$/', '', $pattern);
			$files = self::ls($pattern);
			foreach ($files as $file => $values) {
				S3::deleteObject($globals['Amazon_S3_media_bucket'], $file);
			}
		}
		S3::deleteObject($globals['Amazon_S3_media_bucket'], $pattern);
	}

	public static function buckets($detailed = false) {
		global $globals;
		S3::setAuth($globals['Amazon_access_key'], $globals['Amazon_secret_key']);
		return S3::listBuckets($detailed);
	}
}

