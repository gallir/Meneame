<?php
// Developed by @Hass, 2009
include('config.php');
include(mnminclude.'html1.php');

/*
if (!$current_user->admin) {
	do_error(_('acceso prohibido'), 403);
	die();
}
*/

function print_time($secs) {
	if ( $secs < 60 ) return $secs . ' ' . _("segundos");
	elseif ( $secs == 60 ) return "1" . ' ' . _("minuto");
	elseif ( $secs % 60 == 0 && $secs < 3600) return ($secs / 60) . ' ' ._("minutos");
	elseif ( $secs == 3600) return "1" . _(" hora");
	elseif ( $secs % 3600 == 0 && $secs < 86400) return ($secs / 3600) . ' ' ._("horas");
	elseif ( $secs == 86400) return "1" . ' ' . _("día");
	elseif ( $secs % 86400 == 0 ) return ($secs / 86400) . ' ' ._("días");
	else return $secs . ' ' . _("segundos");
}

do_header(_('Información sobre valores de karma y límites') . ' | men&eacute;ame');
echo '<div id="singlewrap">'."\n";


echo '
<div align="center">
	<br />
	<h2>'._("Información sobre valores de karma y límites").'</h2>
	<br />
</div>
';



echo '<fieldset><legend>'._('karma').'</legend>';
echo _("El karma de un usuario puede ir de").' '.$globals['min_karma'].' '._("a").' '.$globals['max_karma'].'.<br />
				<br />
				'._("El karma base va de").' '.$globals['karma_base'] .' '._("a").' '.$globals['karma_base_max'] .'. '. _("Aumentando a una velocidad de 1 por año, a contar desde la fecha de la primera noticia publicada en portada de ese usuario") .'.<br />
				<br />
				'._("Un usuario normal obtiene el estado 'special' cuando su karma").' > '.$globals['special_karma_gain'].' '._("y lo pierde cuando su karma"). ' &lt; '.$globals['special_karma_loss'] . '.<br />
				<br />
				';

if($globals['min_karma_for_links']) {
				echo _("Karma mínimo para enviar historias") . ': ' . $globals['min_karma_for_links'] . '.<br />
				<br />
				';
}

if($globals['min_karma_for_comments']) {
				echo _("Karma mínimo para enviar comentarios") . ': '. $globals['min_karma_for_comments'] . '.<br />
				<br />
				';
}

if($globals['min_karma_for_comment_votes']) {
				echo _("Karma mínimo para votar comentarios") . ': ' . $globals['min_karma_for_comment_votes'] . '.<br />
				<br />
				';
}

if($globals['min_karma_for_posts']) {
				echo _("Karma mínimo para enviar nótames") . ': ' . $globals['min_karma_for_posts'] . '.<br />
				<br />
				';
}

if($globals['min_karma_for_sneaker']) {
				echo _("Karma mínimo para hablar en la fisgona") . ': ' . $globals['min_karma_for_sneaker'] . '.<br />
				<br />
				';
}

echo				_("Karma instantáneo ganado por un usuario en el momento que se publica uno de sus envíos") . ': ' . $globals['instant_karma_per_published'] . '.<br />
				<br />
				'._("Karma instantáneo perdido por un usuario en el momento que se de-publica uno de sus envíos") . ': ' . $globals['instant_karma_per_depublished'] . '.<br />
				<br />
				'._("Karma instantaneo perdido por un usuario en el momento que se descarta uno de sus envíos") . ': ' . $globals['instant_karma_per_discard'] . '.<br />
				<br />
				'._("Karma que se añade al cálculo diario de karma por cada envío publicado") . ': ' . $globals['karma_points_per_published'] . ' (' . _("hasta un máximo de") . ' ' . $globals['karma_points_per_published_max'] . ').<br />
		</fieldset>';



echo '
		<fieldset>
			<legend>'._('comentarios').'</legend>
				'._("Tiempo para editar un comentario") . ': ' . print_time( $globals['comment_edit_time'] ) . '.<br />
				<br />
				'._("Karma a partir del cual se destacan los comentarios") . ': ' . $globals['comment_highlight_karma'] . '.<br />
				<br />
				'._("Karma a partir del cual se ocultan los comentarios") . ': ' . -$globals['comment_highlight_karma'] . '.<br />
				<br />
				'._("Límite de comentarios por meneo") . $globals['max_comments'] . ': ' . '.<br />
				<br />
				'._("Tiempo que permanecen abiertos los comentarios en meneos en portada") . ': ' . print_time($globals['time_enabled_comments']) . '.<br />
				<br />
				'._("Tiempo que permanecen abiertos los comentarios en meneos pendientes") . ': ' . print_time($globals['time_enabled_comments_status']['queued']) . '.<br />
				<br />
				'._("Tiempo que permanecen abiertos los comentarios en meneos descartados") . ': ' . print_time($globals['time_enabled_comments_status']['discard']) . '.<br />
				<br />
				'._("Tiempo que permanecen abiertos los comentarios en meneos autodescartados") . ': ' . print_time($globals['time_enabled_comments_status']['autodiscard']) . '.<br />
				<br />
				'._("Tiempo que permanecen abiertos los comentarios en meneos descartados por abuso") . ': ' . print_time($globals['time_enabled_comments_status']['abuse']) . '.<br />
				<br />
				'._("Tiempo que debe pasar desde el registro para que un nuevo usuario pueda comentar") . ': ' . print_time($globals['min_time_for_comments']) . '.<br />
		</fieldset>';






echo '
		<fieldset>
			<legend>'._('votos abiertos').'</legend>
				'._("Tiempo que permanecen abiertos los votos") . ': ' . print_time($globals['time_enabled_votes']) . '.<br />
				<br />
				'._("Tiempo abiertos los negativos si se ha extendido el tiempo de voto negativo") . ': ' . print_time($globals['time_enabled_votes']) . '.<br />
				<br />
				'._("Tiempo abiertos los negativos si no se ha extendido el tiempo de voto negativo: ") . ': ' . print_time($globals['time_enabled_negative_votes']) . '.<br />
		</fieldset>';






echo '
		<fieldset>
			<legend>'._('envíos').'</legend>
				'._("Límite de envíos global para usuarios con karma") . ' &lt;= ' . $globals['limit_3_minutes_karma'] . ' ('. _('Se han enviado demasiadas noticias en los últimos 3 minutos') ."): " . $globals['limit_3_minutes'] . ' ' . _("meneos cada 3 minutos") . '.<br />
				<br />
				'._("Límite de envíos global para usuarios con karma") . ' > ' . $globals['limit_3_minutes_karma'] .' ('. _('Se han enviado demasiadas noticias en los últimos 3 minutos') ."): " . ($globals['limit_3_minutes'] * 1.5) . ' ' . _("meneos cada 3 minutos") . '.<br />
				<br />
				'._("Límite de envíos por usuario en las últimas 24 horas") . ' (' . _('Debes esperar, tienes demasiadas noticias en cola de las últimas 24 horas') . "): " .$globals['limit_user_24_hours'] . _(" envíos") .'.<br />
				<br />
				'._("Tiempo que tarda un borrador en eliminarse automáticamente") . ': ' . print_time($globals['draft_time']) . '.<br />
				<br />
				'._("Máximo de borradores por usuario") . ' (' . _('Has hecho demasiados intentos, debes esperar o continuar con ellos desde la') . " " . _('cola de descartadas') . '): ' . $globals['draft_limit'] . '.<br />
				<br />
				'._("Porcentaje de karma de historia que sufre un «depublish» y vuelve a pendientes"). ': ' . intval(100 / $globals['depublish_karma_divisor']) . '.<br />
		</fieldset>';





echo '
		<fieldset>
			<legend>'._('registros').'</legend>
				'._("Los nombres de usuario deben ser de 3 o más caracteres y comenzar por una letra") . '.<br />
				<br />
				'._("Las contraseñas deben ser de 6 o más caracteres e incluir mayúsculas, minúsculas y números") .'.<br />
				<br />
				'._("Registros desde la misma IP") . ': ' . _("Para registrar otro usuario desde la misma dirección debes esperar 24 horas") . '.<br />
				<br />
				'._("Registros desde la misma subred") . ' (xxx.yyy.zzz.*): ' . _("Para registrar otro usuario desde la misma red debes esperar 6 horas") . '.<br />
				<br />
				'._("Registros desde la misma subred") . ' (xxx.yyy.*.*): ' . _("Para registrar otro usuario desde la misma red debes esperar unos minutos") . ' ('. _("una hora") . ').<br />
		</fieldset>';




echo '
		<fieldset>
			<legend>'._('nótames').'</legend>
				'._("Karma a partir del cual se destacan las notas") . ': ' . $globals['post_highlight_karma'] . '.<br />
				<br />
				'._("Karma a partir del cual se ocultan las notas") . ': ' . $globals['post_hide_karma'] . '.<br />
				<br />
				'._("Tiempo de espera entre notas") . ': ' . print_time($globals['posts_period']) . '.<br />
				<br />
				'._("Tiempo de edición de notas") . ': ' . print_time($globals['posts_edit_time']) . '<br />
				<br />
				'._("Tiempo de edición de notas siendo admin") . ': ' .print_time($globals['posts_edit_time_admin']) . '<br />
				<br />
				'._("El karma que gana o pierde un usuario por los votos a sus notas es") . ' ' . ($globals['comment_votes_multiplier'] / $globals['post_votes_multiplier']) . ' ' . _("veces menor al karma que hubiese conseguido si esos mismos votos fuesen a sus comentarios") . '.<br />
		</fieldset>';




echo '
		<fieldset>
			<legend>'._('fórmulas').'</legend>
				'._("El «depublish» ocurre cuando la suma del karma de los votantes de negativos > suma del karma de los votantes de positivos o que la suma de los votantes negativos > (karma de la historia / 2)") . '.<br />
				<br />
				'._("Para el «depublish» también debe cumplirse que la suma del karma de los votantes de negativos > (karma del envío / 6) o que el núm. de votos negativos > (núm. de votos positivos / 6)") . '.<br />
				<br />
				'._("Para que sean contados en las sumas de karma del cálculo de un «depublish», los usuarios que voten positivo deben tener karma > ") . $globals['depublish_positive_karma'] . _(" y los que voten negativo karma > ") . $globals['depublish_negative_karma'] . '.<br />
				<br />
				'._("Se considera «nuevo usuario» a los usuarios que no hayan enviado ningún meneo o se hayan registrado hace menos de "). print_time($globals['new_user_time']) . '.<br />
				<br />';
if ($globals['min_user_votes']) {

$total_links = (int) $db->get_var("select count(*) from links where link_date > date_sub(now(), interval 24 hour) and link_status = 'queued'");


echo '
				'._("Un «nuevo usuario» con karma &lt; "). $globals['new_user_karma'] ._(" y sin envíos deberá votar ") . $globals['min_user_votes'] . _(" meneos antes de poder enviar").'.<br />
				<br />
				'._("Un «nuevo usuario» con karma &lt; "). $globals['new_user_karma'] . _(" y con envíos deberá votar (cifra dinámica) ") . min(4, intval($total_links/20)) . _(" * (1 + nº de envíos del usuario en las últimas 24 h. que no estén en estado «discard») meneos para poder enviar") . '.<br />
				<br />';
}

echo '				
				'._("Un «nuevo usuario» solo podrá enviar ") . $globals['new_user_links_limit'] . _(" historias cada ") . print_time($globals['new_user_links_interval']) . ' ('._('debes esperar, ya se enviaron varias con el mismo usuario o dirección IP'). ').<br />
				<br />
				'._("Un «nuevo usuario» con envíos recientes") . ' ( &lt; ' . print_time($globals['new_user_links_interval']) . ') ' . _("y karma") . ' &lt; ' . $globals['karma_propaganda'] ._(" no podrá enviar si esos envíos han tenido más de 10 negativos y los negativos") . ' > (positivos * 1,5).<br />
		</fieldset>';




echo '
</div>';

do_footer_menu();
do_footer();

?>
