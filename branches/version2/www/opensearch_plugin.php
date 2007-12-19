<?
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
header('Content-Type: text/xml; charset=utf-8');

echo '<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">'."\n";
echo '<ShortName>Menéame Search</ShortName>'."\n";
echo '<Description>'._('noticias elegidas por los usuarios').'</Description>'."\n";
echo '<InputEncoding>UTF-8</InputEncoding>'."\n";
echo '<Image height="16" width="16">http://'.get_server_name().$globals['base_url'].'img/favicons/favicon4.ico</Image>'."\n";
echo '<Url type="text/html" method="GET" template="http://'.get_server_name().$globals['base_url'].'search.php">'."\n";
echo '<Param name="q" value="{searchTerms}"/>'."\n";
echo '</Url>'."\n";
echo '<SearchForm>http://'.get_server_name().$globals['base_url'].'search.php</SearchForm>'."\n";
echo '</OpenSearchDescription>'."\n";
?>
