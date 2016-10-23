<?php
// The Meneame source code is Free Software, Copyright (C) 2005-2009 by
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

include(__DIR__.'/config.php');
include mnminclude.'html1.php';
include_once(mnminclude.'recaptcha2.php');


do_header("test de captcha");

echo '<br/><form action="" method="post">';

if ($_POST["g-recaptcha-response"]) {
	if (ts_is_human()) {
		echo "Captcha OK<br/>";
	} else {
		echo "Failed!<br/>";
	}
}

ts_print_form();
echo '<br/> <input type="submit" value="submit" /> </form> </body>';

do_footer();

