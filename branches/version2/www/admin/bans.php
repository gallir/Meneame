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

$page_size = 40;
$offset=(get_current_page()-1)*$page_size;
$ban_text_length=64; // Cambiar también en checkfield.php
$ban_comment_length=120;

if ($current_user->user_level=="god" || $current_user->user_level=="admin") {
	if (!$_REQUEST["admin"]) {
		$_REQUEST["admin"] = 'hostname';
	} else {
		$_REQUEST["admin"] = clean_input_string($_REQUEST["admin"]);;
	}
	// Delete expired bans
	$db->query("delete from bans where ban_expire is not null and ban_expire < date_sub(now(), interval 60 day)");
	admin_tabs($_REQUEST["admin"]);
	admin_bans($_REQUEST["admin"]);
} else {
	echo '<div id="container-wide">' . "\n";
	echo '<div class="topheading"><h2>'._('Esta página es sólo para administradores').'</h2>';
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
	$tabs=array("hostname", "email", "ip", "words", "proxy");
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
	global $db, $globals, $offset, $page_size, $ban_text_length, $ban_comment_length, $current_user;
	require_once(mnminclude.'ban.php');

	if ($current_user->user_level=="god") {
		if (!empty($_REQUEST["new_ban"])) {
			insert_ban($ban_type, $_POST["ban_text"], $_POST["ban_comment"], $_POST["ban_expire"]);
		} elseif (!empty($_REQUEST["edit_ban"])) {
			insert_ban($ban_type, $_POST["ban_text"], $_POST["ban_comment"], $_POST["ban_expire"], $_POST["ban_id"]);
		} elseif (!empty($_REQUEST["new_bans"])) {
			$array = preg_split ("/\s+/", $_POST["ban_text"]);
			$size = count($array);
			for($i=0; $i < $size; $i++) {
				insert_ban($ban_type, $array[$i], $_POST["ban_comment"], $_POST["ban_expire"]);
			}
		} elseif (!empty($_REQUEST["del_ban"])) {
			del_ban($_POST["ban_id"]);
		}
	}
	
	echo '<div id="container-wide">' . "\n";
	echo '<div id="genericform">';

	echo '<div style="float:right;">'."\n";
	echo '<form method="get" action="'.$globals['base_url'].'admin/bans.php">';
	echo '<input type="hidden" name="admin" value="'.$ban_type.'" />';
	echo '<input type="text" name="s" ';
	if ($_REQUEST["s"]) {
		$_REQUEST["s"] = clean_input_string($_REQUEST["s"]);
		echo ' value="'.$_REQUEST["s"].'" '; 
	} else { 
		echo ' value="'._('buscar...').'" '; 
	}
	echo 'onblur="if(this.value==\'\') this.value=\''._('buscar...').'\';" onfocus="if(this.value==\''._('buscar...').'\') this.value=\'\';" />';
	
	echo '&nbsp;<input style="padding:2px;" type="image" align="top" value="buscar" alt="buscar" src="'.$globals['base_url'].'img/common/search-01.gif" />';
	echo '</form>';
	echo '</div>'; 

	if ($current_user->user_level=="god") {
		echo '&nbsp; [ <a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;op=new">'._('Nuevo ban').'</a> ]';
		echo '&nbsp; [ <a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;op=news">'._('Múltiples bans').'</a> ]';
	}

	if (!empty($_REQUEST["op"])) {
		echo '<form method="post" name="newban" action="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'">';
	}

	echo '<table>';
	echo '<tr><th width="25%"><a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;'; 
	if ($_REQUEST["s"]) { echo 's='.$_REQUEST["s"].'&amp;'; }
	echo 'orderby=ban_text">'.$ban_type.'</a></th>';
	echo '<th width="30%"><a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;';
	if ($_REQUEST["s"]) { echo 's='.$_REQUEST["s"].'&amp;'; }
	echo 'orderby=ban_comment">'._('Comentario').'</a></th>';
	echo '<th><a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;';
	if ($_REQUEST["s"]) { echo 's='.$_REQUEST["s"].'&amp;'; }
	echo 'orderby=ban_date">'._('Fecha creación').'</a></th>';
	echo '<th><a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;';
	if ($_REQUEST["s"]) { echo 's='.$_REQUEST["s"].'&amp;'; }
	echo 'orderby=ban_expire">'._('Fecha expiración').'</a></th>';
	echo '<th>'._('Editar / Borrar').'</th></tr>';
	
	switch ($_REQUEST["op"]) {
		case 'new':
			echo '<tr><td>';
			echo '<input type="text" id="ban_text" name="ban_text" size="30" maxlength="'.$ban_text_length.'" onkeyup="enablebutton(this.form.checkbutton1, this.form.submit, this)" value="" />';
			echo '&nbsp;<span id="checkit"><input type="button" id="checkbutton1" value="'._('verificar').'" disabled="disabled" onclick="checkfield(\'ban_'.$ban_type.'\', this.form, this.form.ban_text)"/></span>' . "\n";
			echo '<br /><span id="ban_'.$ban_type.'checkitvalue"></span>' . "\n";
			echo '</td><td>';
			echo '<input class="form-full" type="text" name="ban_comment" id="ban_comment" />';
			echo '</td><td>';
			echo '</td><td>';
			echo '<select name="ban_expire" id="ban_expire">';
			print_expiration_dates();
			echo '</select>';
			echo '</td><td>';
			echo '<input type="hidden" name="new_ban" value="1" />';
			echo '<input type="submit" disabled="disabled" name="submit" value="'._('Crear ban').'" />';
			echo '</td></tr>';
			break;
		case 'news':
			echo '<tr><td>';
			echo '<textarea id="ban_text" name="ban_text" /></textarea>';
			echo '</td><td>';
			echo '<input class="form-full" type="text" name="ban_comment" id="ban_comment" />';
			echo '</td><td>';
			echo '</td><td>';
			echo '<select name="ban_expire" id="ban_expire">';
			print_expiration_dates();
			echo '</select>';
			echo '</td><td>';
			echo '<input type="hidden" name="new_bans" value="1" />';
			echo '<input type="submit" name="submit" value="'._('Crear bans').'" />';
			echo '</td></tr>';
			break;
		case 'edit':
			$ban = new Ban;
			$ban->ban_id = (int) $_REQUEST["id"];
			$ban->read();
			echo '<tr><td>';
			echo '<input type="text" name="ban_text" id="ban_text" size="30" maxlength="'.$ban_text_length.'" value="'.$ban->ban_text.'" />';
			echo '</td><td>';
			echo '<input type="text" class="form-full" name="ban_comment" id="ban_comment" value="'.$ban->ban_comment.'" />';
			echo '</td><td>';
			echo $ban->ban_date;
			echo '</td><td>';
			echo '<select name="ban_expire" id="ban_expire">';
			echo '<option value="'.$ban->ban_expire.'">'.$ban->ban_expire.'</option>';
			print_expiration_dates();
			echo '</select>';
			echo '</td><td>';
			echo '<input type="hidden" name="ban_id" value="'.$ban->ban_id.'" />';
			echo '<input type="submit" name="edit_ban" value="'._('Editar ban').'" />';
			echo '</td></tr>';
			break;
	}
	if (empty($_REQUEST["op"])) {
	//listado de bans
		if (empty($_REQUEST["orderby"])) {
			$_REQUEST["orderby"]="ban_text";
		} else {
			$_REQUEST["orderby"] = preg_replace('/[^a-z_]/i', '', $_REQUEST["orderby"]);
			if ($_REQUEST["orderby"] == 'ban_date') {
				$order = "DESC";
			}
		}
		$where= "WHERE ban_type='".$ban_type."'";
		if ($_REQUEST["s"]) { $where .=" AND ban_text LIKE '%".$_REQUEST["s"]."%' "; }
		$bans = $db->get_col("SELECT ban_id FROM bans ".$where." ORDER BY ".$_REQUEST["orderby"]." $order LIMIT $offset,$page_size");
		$rows = $db->get_var("SELECT count(*) FROM bans ".$where);
		if ($bans) {
			$ban = new Ban;
			foreach($bans as $ban_id) {
				$ban->ban_id = $ban_id;
				$ban->read();
				echo '<tr>';
				echo '<td onmouseover="return tooltip.ajax_delayed(event, \'get_ban_info.php\', '.$ban->ban_id.');" onmouseout="tooltip.clear(event);" >'.clean_text($ban->ban_text).'</td>';
				echo '<td style="overflow: hidden;white-space: nowrap;" onmouseover="return tooltip.ajax_delayed(event, \'get_ban_info.php\', '.$ban->ban_id.');" onmouseout="tooltip.clear(event);">'.clean_text(txt_shorter($ban->ban_comment, 50)).'</td>';
				echo '<td>'.$ban->ban_date.'</td>';
				echo '<td>'.$ban->ban_expire.'</td>';
				echo '<td>';
				if ($current_user->user_level=="god") {
					echo '<a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;op=edit&amp;id='.$ban->ban_id.'" title="'._('Editar').'"><img src="'.$globals['base_url'].'img/common/sneak-edit-notice01.png" alt="'.('Editar').'" /></a>';
					echo '&nbsp;/&nbsp;';
					echo '<a href="'.$globals['base_url'].'admin/bans.php?admin='.$ban_type.'&amp;del_ban='.$ban->ban_id.'" title="'._('Eliminar').'"><img src="'.$globals['base_url'].'img/common/sneak-reject01.png" alt="'.('Eliminar').'" /></a>';
				}
				echo '</td>';
				echo '</tr>';
			}
		}
	}
	echo '</table>';
	if (!empty($_REQUEST["op"])) {
		echo "</form>\n";
	}
	do_pages($rows, $page_size, false);
}

function print_expiration_dates() {
	echo '<option value="UNDEFINED">'._('Sin expiración').'</option>';
	echo '<option value="'.(time()+7200).'">'._('Ahora + dos horas').'</option>';
	echo '<option value="'.(time()+86400).'">'._('Ahora + un día').'</option>';
	echo '<option value="'.(time()+86400*7).'">'._('Ahora + una semana').'</option>';
	echo '<option value="'.(time()+86400*30).'">'._('Ahora + un mes').'</option>';
	echo '<option value="'.(time()+86400*60).'">'._('Ahora + dos meses').'</option>';
	echo '<option value="'.(time()+86400*180).'">'._('Ahora + seis meses').'</option>';
	echo '<option value="'.(time()+86400*365).'">'._('Ahora + un año').'</option>';
}
?>
