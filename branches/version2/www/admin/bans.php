<?
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// David Martín :: Suki_ :: <david at sukiweb dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');

$globals['ads'] = false;

do_header(_('Administración de bans'));
do_banner_top();

$page_size = 20;
$offset=(get_current_page()-1)*$page_size;
$ban_text_length=64; // Cambiar también en checkfield.php
$ban_comment_length=64;

if ($current_user->user_level=="god") {
	if (!$_REQUEST["admin"]) { $_REQUEST["admin"]="hostname"; }
	admin_tabs($_REQUEST["admin"]);
	admin_bans($_REQUEST["admin"]);
} else {
	echo '<div id="container-wide">' . "\n";
	echo '<div class="topheading"><h2>'._('Esta página es sólo para Bofhs chungos').'</h2>';
}
echo "</div>";
do_footer();


function admin_tabs($tab_selected = false) {
	global $globals;

	$active = ' class="tabsub-this"';
	
	echo '<ul class="tabsub-shakeit">' . "\n";

	// url with parameters?
	if (!empty($_SERVER['QUERY_STRING']))
		$query = "?".htmlentities($_SERVER['QUERY_STRING']);

	// START STANDARD TABS
	// First the standard and always present tabs
	$tabs=array("hostname", "email", "ip", "words");
		foreach($tabs as $tab) {
		if ($tab_selected == $tab) {
			echo '<li><a '.$active.' href="'.$globals['base_url'].'admin/bans.php?admin='.$tab.'" title="'.$reload_text.'">'._($tab).'&nbsp;&nbsp;&nbsp;'.$reload_icon.'</a></li>' . "\n";
		} else {
			echo '<li><a  href="'.$globals['base_url'].'admin/bans.php?admin='.$tab.'">'._($tab).'</a></li>' . "\n";
		}
	}
	echo '</ul>' . "\n";
}


function admin_bans($ban_type) {
	global $db, $globals, $offset, $page_size, $ban_text_length, $ban_comment_length;
	if ($_REQUEST["new_ban"]) {
		require_once(mnminclude.'ban.php');
		insert_ban($ban_type, $_REQUEST["ban_text"], $_REQUEST["ban_comment"], $_REQUEST["ban_expire"]);
		//$db->debug();
	}

	if ($_REQUEST["new_bans"]) {
		require_once(mnminclude.'ban.php');
		$array = preg_split ("/\s+/", $_REQUEST["ban_text"]);
		$size = count($array);
		for($i=0; $i < $size; $i++) {
			insert_ban($ban_type, $array[$i], $_REQUEST["ban_comment"], $_REQUEST["ban_expire"]);
		}
		//$db->debug();
	}
	if ($_REQUEST["edit_ban"]) {
		require_once(mnminclude.'ban.php');
		insert_ban($ban_type, $_REQUEST["ban_text"], $_REQUEST["ban_comment"], $_REQUEST["ban_expire"], $_REQUEST["ban_id"]);
		//$db->debug();
	}
	if ($_REQUEST["del_ban"]) {
		require_once(mnminclude.'ban.php');
		del_ban($_REQUEST["ban_id"]);
		//$db->debug();
	}
	
	echo '<div id="container-wide">' . "\n";
	echo '<div id="genericform">';

	echo '<div style="float:right;">'."\n";
	echo '<form method="get" action="'.$globals['base_url'].'admin/bans.php">';
	echo '<input type="hidden" name="admin" value="'.$ban_type.'" />';
	echo '<input type="text" name="s" ';
	if ($_REQUEST["s"]) { echo ' value="'.$_REQUEST["s"].'" '; } else { echo ' value="'._('buscar...').'" '; }
	echo 'onblur="if(this.value==\'\') this.value=\''._('buscar...').'\';" onfocus="if(this.value==\''._('buscar...').'\') this.value=\'\';" />';
	
	echo '&nbsp;<input style="padding:2px;" type="image" align="top" value="buscar" alt="buscar" src="'.$globals['base_url'].'img/common/search-01.gif" />';
	echo '</form>';
	echo '</div>'; 

	echo '&nbsp; [ <a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;new=1">'._('Nuevo ban').'</a> ]';
	echo '&nbsp; [ <a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;news=1">'._('Múltiples bans').'</a> ]';
	echo '<table>';
	echo '<tr><th><a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;'; 
	if ($_REQUEST["s"]) { echo 's='.$_REQUEST["s"].'&amp;'; }
	echo 'orderby=ban_text">'.$ban_type.'</a></th>';
	echo '<th><a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;';
	if ($_REQUEST["s"]) { echo 's='.$_REQUEST["s"].'&amp;'; }
	echo 'orderby=ban_comment">'._('Comentario').'</a></th>';
	echo '<th><a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;';
	if ($_REQUEST["s"]) { echo 's='.$_REQUEST["s"].'&amp;'; }
	echo 'orderby=ban_date">'._('Fecha creación').'</a></th>';
	echo '<th><a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;';
	if ($_REQUEST["s"]) { echo 's='.$_REQUEST["s"].'&amp;'; }
	echo 'orderby=ban_expire">'._('Fecha expiración').'</a></th>';
	echo '<th>'._('Editar / Borrar').'</th></tr>';
	
	if ($_REQUEST["new"]) {
		//echo '<fieldset>';
		//echo '<legend>'._('Creando ban').'</legend>';
		echo '<form method="post" name="newban" action="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'">';
		echo '<tr><td>';
		//echo '<p class="l-top"><label for="ban_text">' . _($ban_type) . ':</label><br />' . "\n";
		echo '<input type="text" id="ban_text" name="ban_text" size="'.($ban_text_length+1).'" maxlength="'.$ban_text_length.'" onkeyup="enablebutton(this.form.checkbutton1, this.form.submit, this)" value="" />';
		echo '&nbsp;<span id="checkit"><input type="button" id="checkbutton1" value="'._('verificar').'" disabled="disabled" onclick="checkfield(\'ban_'.$ban_type.'\', this.form, this.form.ban_text)"/></span>' . "\n";
		echo '<br \><span id="ban_'.$ban_type.'checkitvalue"></span></p>' . "\n";
		echo '</td><td>';
		//echo '<p class="l-top"><label for="ban_comment">'._('Comentario').':</label><br />' . "\n";
		echo '<input class="form-full" type="text" name="ban_comment" id="ban_comment" />';
		echo '</td><td>';
		echo '</td><td>';
		//echo '<p class="l-top"><label for="ban_expire">'._('Fecha expiración').':</label><br />' . "\n";
		echo '<select name="ban_expire" id="ban_expire">';
		echo '<option value="UNDEFINED">'._('Sin expiración').'</option>';
		echo '<option value="'.(time()+86400*7).'">'._('Ahora + Una semana').'</option>';
		echo '<option value="'.(time()+86400*30).'">'._('Ahora + Un mes').'</option>';
		echo '<option value="'.(time()+86400*180).'">'._('Ahora + Seis meses').'</option>';
		echo '<option value="'.(time()+86400*365).'">'._('Ahora + Un año').'</option>';
		echo '</select>';
		echo '</td><td>';
		echo '<input type="hidden" name="new_ban" value="1" />';
		echo '<input type="submit" disabled="disabled" name="submit" value="'._('Crear ban').'" />';
		echo '</td></tr>';
		echo '</form>';
		
		//echo '</fieldset>';
	} else if ($_REQUEST["news"]) {
		//echo '<fieldset>';
		//echo '<legend>'._('Creando ban').'</legend>';
		echo '<form method="post" name="newban" action="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'">';
		echo '<tr><td>';
		//echo '<p class="l-top"><label for="ban_text">' . _($ban_type) . ':</label><br />' . "\n";
		echo '<textarea id="ban_text" name="ban_text" /></textarea>';
		echo '</td><td>';
		//echo '<p class="l-top"><label for="ban_comment">'._('Comentario').':</label><br />' . "\n";
		echo '<input class="form-full" type="text" name="ban_comment" id="ban_comment" />';
		echo '</td><td>';
		echo '</td><td>';
		//echo '<p class="l-top"><label for="ban_expire">'._('Fecha expiración').':</label><br />' . "\n";
		echo '<select name="ban_expire" id="ban_expire">';
		echo '<option value="UNDEFINED">'._('Sin expiración').'</option>';
		echo '<option value="'.(time()+86400*7).'">'._('Ahora + Una semana').'</option>';
		echo '<option value="'.(time()+86400*30).'">'._('Ahora + Un mes').'</option>';
		echo '<option value="'.(time()+86400*180).'">'._('Ahora + Seis meses').'</option>';
		echo '<option value="'.(time()+86400*365).'">'._('Ahora + Un año').'</option>';
		echo '</select>';
		echo '</td><td>';
		echo '<input type="hidden" name="new_bans" value="1" />';
		echo '<input type="submit" name="submit" value="'._('Crear bans').'" />';
		echo '</td></tr>';
		echo '</form>';
	
	} elseif ($_REQUEST["edit"]) {
		$ban=$db->get_row("SELECT * FROM `bans` WHERE ban_id='".$_REQUEST["edit"]."'");
		//echo '<fieldset>';
		//echo '<legend>'._('Editando ban').'</legend>';
		echo '<form method="post" name="newban" action="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'">';
		//echo '<p class="l-top"><label for="ban_text">'._($ban_type).':</label><br />' . "\n";
		echo '<tr><td>';
		echo '<input type="text" name="ban_text" id="ban_text" size="'.($ban_text_length+1).'" maxlength="'.$ban_text_length.'" value="'.$ban->ban_text.'" />';
		echo '</td><td>';
		//echo '<p class="l-top"><label for="ban_comment">'._('Comentario').':</label><br />' . "\n";
		echo '<input type="text" class="form-full" name="ban_comment" id="ban_comment" />';
		//echo '<p class="l-top"><label for="ban_expire">'._('Fecha expiración').':</label><br />' . "\n";
		echo '</td><td>';
		echo $ban->ban_date;
		echo '</td><td>';
		echo '<select name="ban_expire" id="ban_expire">';
		echo '<option value="NOCHANGE">'.$ban->ban_expire.'</option>';
		echo '<option value="'.(time()+86400*7).'">'._('Ahora + Una semana').'</option>';
		echo '<option value="'.(time()+86400*30).'">'._('Ahora + Un mes').'</option>';
		echo '<option value="'.(time()+86400*180).'">'._('Ahora + Seis meses').'</option>';
		echo '<option value="'.(time()+86400*365).'">'._('Ahora + Un año').'</option>';
		echo '<option value="UNDEFINED">'._('Sin expiración').'</option>';
		echo '</select>';
		echo '</td><td>';
		echo '<input type="hidden" name="ban_id" value="'.$ban->ban_id.'" />';
		echo '<input type="submit" name="edit_ban" value="'._('Editar ban').'" />';
		echo '</td></tr>';
		echo '</form>';
		//echo '</fieldset>';
	
	} 
	//listado de bans
	if (!$_REQUEST["orderby"]) { $_REQUEST["orderby"]="ban_text"; }
	$where= "WHERE ban_type='".$ban_type."'";
	if ($_REQUEST["s"]) { $where .=" AND ban_text LIKE '%".$_REQUEST["s"]."%' "; }
	$bans = $db->get_results("SELECT * FROM `bans` ".$where." ORDER BY `".$_REQUEST["orderby"]."` LIMIT $offset,$page_size");
	$rows = $db->get_var("SELECT count(*) FROM `bans` ".$where);
	if ($bans) {
		foreach($bans as $dbbans) {
			echo '<tr>';
			echo '<td><em onmouseover="return tooltip.ajax_delayed(event, \'get_ban_info.php\', '.$dbbans->ban_id.');" onmouseout="tooltip.clear(event);" >'.$dbbans->ban_text.'</em></td>';
			echo '<td>'.txt_shorter($dbbans->ban_comment, 30).'</td>';
			echo '<td>'.$dbbans->ban_date.'</td>';
			echo '<td>'.$dbbans->ban_expire.'</td>';
			echo '<td>';
			echo '<a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;edit='.$dbbans->ban_id.'" title="'._('Editar').'"><img src="'.$globals['base_url'].'img/common/sneak-edit-notice01.gif" alt="'.('Editar').'" /></a>';
			echo '<a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;del_ban='.$dbbans->ban_id.'" title="'._('Eliminar').'"><img src="'.$globals['base_url'].'img/common/sneak-reject01.png" alt="'.('Eliminar').'" /></a>';
			echo '</td>';

			echo '</tr>';
		}
	}
	echo '</table>';
	do_pages($rows, $page_size, false);
	//echo '</div>'; // end div genericform
	//echo '</div>'; // end div container-wide
	
}

function recover_error($message) {
	echo '<div class="form-error">';
	echo "<p>$message</p>";
	echo "</div>\n";
}
?>
