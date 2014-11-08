<?php
// The Meneame source code is Free Software, Copyright (C) 2005-2010 by
// Ricardo Galli <gallir at gmail dot com> and Menéame Comunicacions S.L.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
// 
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.

// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

$base = dirname(dirname($_SERVER["SCRIPT_FILENAME"])); // Get parent dir that works with symbolic links
include("$base/config.php");

$service = clean_input_string($_GET['service']);
$op = clean_input_string($_GET['op']);

switch ($service) {
    case 'gplus':
		require_once('gplus.php');
		$req = new GplusOAuth();
		if ($op == 'init') {
			$req->authRequest();
		} else {
			$req->authorize();
		}
        break;
	case 'twitter':
	default:
		require_once('twitter.php');
		$req = new TwitterOAuth();
		if ($op == 'init') {
			$req->authRequest();
		} else {
			$req->authorize();
		}
	}

?>
