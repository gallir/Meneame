<?
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'geo.php');

header('Content-Type: text/html; charset=UTF-8');

if(!($id=intval($_REQUEST['id']))) {
	error(_('falta el id'). " $link");
}

$type = clean_input_string($_REQUEST['type']);
$icontype = clean_input_string($_REQUEST['icon']);

if ($type == 'link') {
	require_once(mnminclude.'link.php');
	$link = new Link;
	$link->id = $id;
	if ( ! $link->read() ) {
		error(_('Artículo inexistente'));
	}
	if (! $link->is_map_editable() ) {
		error(_("noticia no modificable"));
	}
	$latlng = $link->get_latlng();
} else {
	error(_('tipo incorrecto'));
}

geo_coder_print_form($type, $id, $latlng, _('edición localización'), $icontype);

function error($mess) {
	echo "ERROR: $mess\n";
	die;
}

?>
