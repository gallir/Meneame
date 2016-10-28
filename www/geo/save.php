<?php
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include(__DIR__.'/../config.php');
include(mnminclude.'geo.php');

header('Content-Type: text/plain; charset=UTF-8');
stats_increment('ajax');

if(!($id=intval($_REQUEST['id']))) {
	error(_('falta el id'). " $link");
}

$type = $_REQUEST['type'];

if ($type == 'user') {
	if ($id != $current_user->user_id) {
		error(_('usuario incorrecto'));
	}
} elseif ($type == 'link') {
	$link = new Link;
	$link->id = $id;
	if ( ! $link->read() ) {
		error(_('Artículo inexistente'));
	}
	if (! $link->is_map_editable() ) {
		error(_("noticia no modificable"));
	}
} else {
	error(_('tipo incorrecto'));
}

$lat = (float) $_REQUEST['lat'];
$lng = (float) $_REQUEST['lng'];
$text = clean_text($_REQUEST['text'], 0, true, 75);

if(geo_insert($type, $id, $lat, $lng, $text)) {
	echo "OK";
	if ($type == 'link') {
		Log::conditional_insert('link_geo_edit', $link->id, $current_user->user_id, 3600);
	}
} else {
	error(_('no se insertó en la base de datos'));
}


function error($mess) {
	echo "ERROR: $mess\n";
	die;
}

?>
