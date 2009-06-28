<?
// The source code packaged with this file is Free Software, Copyright (C) 2005-2009 by
// Benjamí Villoslada <benjami at bitassa dot cat>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'html1.php');

$errn = $_SERVER{"REDIRECT_STATUS"};

do_header(_('Error'));

echo '<STYLE TYPE="text/css" MEDIA=screen>'."\n";
echo '<!--'."\n";
  echo '.errt { text-align:center; padding-top:50px; font-size:300%; color:#FF6400;}'."\n";
  echo '.errl { text-align:center; margin-top:50px; margin-bottom:100px; }'."\n";
echo '-->'."\n";
echo '</STYLE>'."\n";

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
}

echo '<p class="errt">'.$errp.'<br />'."\n";
echo _('(error').' '.$errn.')</p>'."\n";
echo '<div class="errl"><img src="lame_excuses/img/lame_excuse_01.png" width="362" height="100" alt="ooops logo" /></div>'."\n";

do_footer_menu();
do_footer();
?>
