<?
// The source code packaged with this file is Free Software, Copyright (C) 2005-2009 by
// Benjamí Villoslada <benjami at bitassa dot cat>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

$errn = $_SERVER{"REDIRECT_STATUS"};

switch($errn) {
  case 400:
    $errp = _('petición desconocida');
    break;
  case 401:
    $errp = _('no autorizado');
    break;
  case 403:
    $errp = _('acceso prohibido');
    break;
  case 404:
    $errp = _('la página no existe');
    break;
  case 500:
  case 501:
  case 503:
    $errp = _('error de servidor');
    break;
  default:
	$errn = false;
	$errp = false;
}

do_error($errp, $errn);

?>
