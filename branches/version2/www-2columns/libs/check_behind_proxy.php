<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".



function isIPIn($ip,$net,$mask) {
        $lnet=ip2long($net);
        $lip=ip2long($ip);
        $binnet=str_pad( decbin($lnet),32,"0","STR_PAD_LEFT" );
        $firstpart=substr($binnet,0,$mask);
        $binip=str_pad( decbin($lip),32,"0","STR_PAD_LEFT" );
        $firstip=substr($binip,0,$mask);
        return(strcmp($firstpart,$firstip)==0);
}


function isPrivateIP($ip) {
        $privates = array ("127.0.0.0/24", "10.0.0.0/8", "172.16.0.0/12", "192.168.0.0/16");
        foreach ( $privates as $k ) {
                list($net,$mask)=split("/",$k);
                if (isIPIn($ip,$net,$mask)) {
                        return true;
                }
        }
        return false;
}

function check_ip_behind_proxy() {
	static $last_seen = '';

	// Temporalely disabled, not useful
	// return $_SERVER["REMOTE_ADDR"];
	
	if(!empty($last_seen) ) return $last_seen;

	if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		$user_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} else if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
		$user_ip = $_SERVER["HTTP_CLIENT_IP"];
	} else {
		$last_seen = $_SERVER["REMOTE_ADDR"];
		return $last_seen;
	}

	$ips = preg_split('/[, ]/', $user_ip);
	foreach ($ips as $last_seen) {
		if (preg_match('/^[1-9]\d{0,2}\.(\d{1,3}\.){2}[1-9]\d{0,2}$/s', $last_seen)
			&& !isPrivateIP($last_seen) ) {
			return $last_seen;
		}
	}

	$last_seen = $_SERVER["REMOTE_ADDR"];
	return $last_seen;
}
?>
