<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'tags.php');
include(mnminclude.'ban.php');

$globals['ads'] = false;


if(isset($_POST["phase"])) {
	force_authentication();
	switch ($_POST["phase"]) {
		case 1:
			do_header(_("enviar noticia 2/3"), "post");
			echo '<div id="singlewrap">' . "\n";
			do_submit1();
			break;
		case 2:
			do_header(_("enviar noticia 3/3"), "post");
			echo '<div id="singlewrap">' . "\n";
			do_submit2();
			break;
		case 3:
			do_submit3();
			break;
	}
} else {
	check_already_sent();
	force_authentication();
	do_header(_("enviar noticia 1/3"), "post");
	echo '<div id="singlewrap">' . "\n";
	do_submit0();
}
echo "</div>\n"; // singlewrap
do_footer();
exit;

function preload_indicators() {
	global $globals;

	echo '<SCRIPT type="text/javascript">'."\n";
	echo '<!--'."\n";
	echo 'var img_src1=\''.$globals['base_static'].'img/common/indicator_orange.gif\''."\n";;
	echo 'var img1= new Image(); '."\n";
	echo 'img1.src = img_src1;'."\n";
	echo '//-->'."\n";
	echo '</SCRIPT>'."\n";
}

function check_already_sent() {
	global $db;
	// Check if the url has been sent already
	if (!empty($_GET['url'])) {
		$linkres = new Link;
		if (($found = $linkres->duplicates($_GET['url']))) {
			$linkres->id = $found;
			if($linkres->read()) {
				header('Location: ' . $linkres->get_permalink());
				die;
			}
		}
	}
}

function print_empty_submit_form() {
	global $globals, $current_user, $site_key;

	preload_indicators();
	if (!empty($_GET['url'])) {
		$url = clean_input_url($_GET['url']);
	} else {
		$url = 'http://';
	}
	echo '<div class="genericform">';
	echo '<fieldset><legend><span class="sign">'._('dirección de la noticia').'</span></legend>';
	echo '<form action="submit.php" method="post" id="thisform" onSubmit="$(\'#working\').html(\''._('verificando').'...&nbsp;<img src=\\\'\'+img_src1+\'\\\'/>\'); return true;">';
	echo '<p><label for="url">'._('url').':</label><br />';
	echo '<input type="text" name="url" id="url" value="'.htmlspecialchars($url).'" class="form-full" onblur="if(this.value==\'\') this.value=\'http://\';" onclick="if(this.value==\''._('http://').'\') this.value=\'\';"/></p>';
	echo '<input type="hidden" name="phase" value="1" />';
	$randkey = rand(10000,10000000);
	echo '<input type="hidden" name="key" value="'.md5($randkey.$current_user->user_id.$current_user->user_email.$site_key.get_server_name()).'" />'."\n";
	echo '<input type="hidden" name="randkey" value="'.$randkey.'" />';
	echo '<input type="hidden" name="id" value="c_1" />';
	echo '<p><input class="button" type="submit" value="'._('continuar &#187;').'" ';
	echo '/>&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span></p>';
	echo '</form>';
	echo '</fieldset>';
	echo '</div>';
}

function do_submit0() {
	echo '<h2>'._('envío de una nueva noticia: paso 1 de 3').'</h2>';
	echo '<div class="faq">';
	echo '<h3>'._('por favor, respeta estas instrucciones para mejorar la calidad:').'</h3>';
	echo '<ul class="instruction-list">';
	echo '<li><strong>'._('contenido externo').':</strong> '._('Menéame no es un sitio para generar noticias, ni un sistema de <em>microblogging</em>').'</li>';
	echo '<li><strong>'._('contenido interesante').':</strong> '._('¿interesará a una cantidad razonable de lectores?').'</li>';
	echo '<li><strong>'._('enlaza la fuente original').':</strong> '._('no enlaces a sitios intermedios que no añaden nada al original').'</li>';
	echo '<li><strong>'._('busca antes').':</strong> '._('evita duplicar noticias').'</li>';
	echo '<li><strong>'._('sé descriptivo').':</strong> '._('explica el enlace de forma fidedigna, no distorsiones').'</li>';
	echo '<li><strong>'._('respeta el voto de los demás').'</strong>. '._('si los votos te pueden afectar personalmente, es mejor que no envíes la noticia').'</li>';
	echo '<li><strong>¿'._('has leído las').' <a href="libs/ads/legal-meneame.php#tos" target="_blank">'._('condiciones de uso').'</a></strong>?</li>';
	echo '</ul></div>'."\n";
	print_empty_submit_form();
}

function do_submit1() {
	global $db, $dblang, $current_user, $globals;

	$url = clean_input_url($_POST['url']);
	$url = preg_replace('/^http:\/\/http:\/\//', 'http://', $url); // Some users forget to delete the foo http://
	$url = preg_replace('/#[^\/]*$/', '', $url); // Remove the "#", people just abuse

	echo '<div>'."\n";

	$new_user = false;
	if (!check_link_key()) {
		echo '<p class="error"><strong>'._('clave incorrecta').'</strong></p> ';
		echo '</div>'. "\n";
		return;
	}
	if ($globals['min_karma_for_links'] > 0 && $current_user->user_karma < $globals['min_karma_for_links'] ) {
		echo '<p class="error"><strong>'._('no tienes el mínimo de karma para enviar una nueva historia').'</strong></p> ';
		echo '</div>'. "\n";
		return;
	}

	$queued_24_hours = (int) $db->get_var("select count(*) from links where link_status!='published' and link_date > date_sub(now(), interval 24 hour) and link_author=$current_user->user_id");

	if ($globals['limit_user_24_hours'] && $queued_24_hours > $globals['limit_user_24_hours']) {
		echo '<p class="error">'._('Debes esperar, tienes demasiadas noticias en cola de las últimas 24 horas'). " ($queued_24_hours), "._('disculpa las molestias'). ' </p>';
		syslog(LOG_NOTICE, "Meneame, too many queued in 24 hours ($current_user->user_login): $_POST[url]");
		echo '<br style="clear: both;" />' . "\n";
		echo '</div>'. "\n";
		return;
	}

	// check the URL is OK and that it resolves
	$url_components = @parse_url($url);
	if (!$url_components || ! $url_components['host'] || gethostbyname($url_components['host']) == $url_components['host']) {
		echo '<p class="error"><strong>'._('URL o nombre de servidor erróneo').'</strong></p> ';
		echo '<p>'._('el nombre del servidor es incorrecto o éste tiene problemas para resolver el nombre'). ' </p>';
		syslog(LOG_NOTICE, "Meneame, hostname error ($current_user->user_login): $url");
		print_empty_submit_form();
		echo '</div>'. "\n";
		return;
	}

	$enqueued_last_minutes = (int) $db->get_var("select count(*) from links where link_status='queued' and link_date > date_sub(now(), interval 3 minute)");
	if ($current_user->user_karma > $globals['limit_3_minutes_karma']) $enqueued_limit = $globals['limit_3_minutes'] * 1.5;
	else $enqueued_limit = $globals['limit_3_minutes'];

	if ($enqueued_last_minutes > $enqueued_limit) {
		echo '<p class="error"><strong>'._('Exceso de envíos').':</strong></p>';
		echo '<p>'._('Se han enviado demasiadas noticias en los últimos 3 minutos'). " ($enqueued_last_minutes > $enqueued_limit), "._('disculpa las molestias'). ' </p>';
		syslog(LOG_NOTICE, "Meneame, too many queued ($current_user->user_login): $_POST[url]");
		echo '</div>'. "\n";
		return;
	}

	// Check the user does not have too many drafts
	$minutes = intval($globals['draft_time'] / 60) + 10;
	$drafts = (int) $db->get_var("select count(*) from links where link_author=$current_user->user_id  and link_date > date_sub(now(), interval $minutes minute) and link_status='discard' and link_votes = 0");
	if ($drafts > $globals['draft_limit']) {
		echo '<p class="error"><strong>'._('Demasiados borradores').':</strong></p>';
		echo '<p>'._('Has hecho demasiados intentos, debes esperar o continuar con ellos desde la'). ' <a href="shakeit.php?meta=_discarded">'. _('cola de descartadas').'</a></p>';
		syslog(LOG_NOTICE, "Meneame, too many drafts ($current_user->user_login): $_POST[url]");
		echo '</div>'. "\n";
		return;
	}
	// Delete dangling drafts
	if ($drafts > 0) {
		$db->query("delete from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 30 minute) and link_date < date_sub(now(), interval 10 minute) and link_status='discard' and link_votes = 0");
	}


	// Check for banned IPs
	if(($ban = check_ban($globals['user_ip'], 'ip', true)) || ($ban = check_ban_proxy())) {
		echo '<p class="error"><strong>'._('Dirección IP no permitida para enviar').':</strong> '.$globals['user_ip'].'</p>';
		echo '<p><strong>'._('Razón').'</strong>: '.$ban['comment'].'</p>';
		if ($ban['expire'] > 0) {
			echo '<p class="note"><strong>'._('caduca').'</strong>: '.get_date_time($ban['expire']).'</p>';
		}
		syslog(LOG_NOTICE, "Meneame, banned IP $globals[user_ip] ($current_user->user_login): $url");
		print_empty_submit_form();
		echo '</div>'. "\n";
		return;
	}

	// Number of links sent by the user
	$total_sents = (int) $db->get_var("select count(*) from links where link_author=$current_user->user_id") - $drafts;
	if ($total_sents > 0) {
		$sents = (int) $db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 60 day)") - $drafts;
	} else {
		$new_user = true;
		$sents = 0;
	}

	$register_date = $current_user->Date();
	if ($globals['now'] - $register_date < $globals['new_user_time'] ) {
		$new_user = true;
	}

	// check that a new user also votes, not only sends links
	// it requires $globals['min_user_votes'] votes
	if ($new_user && $globals['min_user_votes'] > 0 && $current_user->user_karma < $globals['new_user_karma']) {
		$user_votes_total = (int) $db->get_var("select count(*) from votes where vote_type='links' and vote_user_id=$current_user->user_id");
		$user_votes = (int) $db->get_var("select count(*) from votes where vote_type='links' and vote_date > date_sub(now(), interval 72 hour) and vote_user_id=$current_user->user_id");
		$user_links = 1 + $db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 24 hour) and link_status != 'discard'");
		$total_links = (int) $db->get_var("select count(*) from links where link_date > date_sub(now(), interval 24 hour) and link_status = 'queued'");
		echo "<!-- $user_votes_total, $user_links, $total_links -->\n";
		if ($sents == 0) {
			// If is a new user, requires more votes, to avoid spam
			$min_votes = $globals['min_user_votes'];
		} else {
			$min_votes = min(4, intval($total_links/20)) * $user_links;
		}
		if (!$current_user->admin && $user_votes < $min_votes) {
			$needed = $min_votes - $user_votes;
			echo '<p class="error">';
			if ($new_user) {
				echo '<strong>'._('¿es la primera vez que envías una noticia?').'</strong></p> ';
				echo '<p class="error-text">'._('necesitas como mínimo'). " <strong>$needed " . _('votos') . '</strong><br/>';
			} else {
				echo '<strong>'._('no tienes el mínimo de votos necesarios para enviar una nueva historia').'</strong></p> ';
				echo '<p class="error-text">'._('necesitas votar como mínimo a'). " <strong>$needed " . _('noticias') . '</strong><br/>';
			}
			echo '<strong>'._('no votes de forma apresurada, penaliza el karma').'</strong><br/>';
			echo '<a href="'.$globals['base_url'].'shakeit.php" target="_blank">'._('haz clic aquí para ir a votar').'</a></p>';
			echo '<br style="clear: both;" />' . "\n";
			echo '</div>'. "\n";
			return;
		}
	}

	// avoid spams, an extra security check
	// it counts the numbers of links in the last hours
	if ($new_user) {
		$user_links_limit = $globals['new_user_links_limit'];
		$user_links_interval = intval($globals['new_user_links_interval'] / 3600);
	} else {
		$user_links_limit = $globals['user_links_limit'];
		$user_links_interval = intval($globals['user_links_interval'] / 3600);
	}
	$same_user = (int) $db->get_var("select count(*) from links where link_date > date_sub(now(), interval $user_links_interval hour) and link_author=$current_user->user_id") - $drafts;
	$same_ip = (int) $db->get_var("select count(*) from links where link_date > date_sub(now(), interval $user_links_interval hour) and link_ip = '".$globals['user_ip']."'") - $drafts;
	if ($same_user >  $user_links_limit  || $same_ip >  $user_links_limit  ) {
		echo '<p class="error"><strong>'._('debes esperar, ya se enviaron varias con el mismo usuario o dirección IP').  '</strong></p>';
		echo '<br style="clear: both;" />' . "\n";
		echo '</div>'. "\n";
		return;
	}

	// avoid users sending continuous "rubbish" or "propaganda", specially new users
	// it takes in account the number of positive votes in the last six hours
	if ($same_user > 1 && $current_user->user_karma < $globals['karma_propaganda']) {
		$positives_received = $db->get_var("select sum(link_votes) from links where link_date > date_sub(now(), interval $user_links_interval hour) and link_author = $current_user->user_id");
		$negatives_received = $db->get_var("select sum(link_negatives) from links where link_date > date_sub(now(), interval $user_links_interval hour) and link_author = $current_user->user_id");
		if ($negatives_received > 10 && $negatives_received > $positives_received * 1.5) {
			echo '<p class="error"><strong>'._('debes esperar, has tenido demasiados votos negativos en tus últimos envíos').  '</strong></p>';
			echo '<br style="clear: both;" />' . "\n";
			echo '</div>'. "\n";
			return;
		}
	}
	
	$linkres=new Link;
	$linkres->url = $url;

	$edit = false;

	if(report_dupe($url)) return;


	if(!$linkres->check_url($url, true, true) || !$linkres->get($url)) {
		echo '<p class="error"><strong>'._('URL erróneo o no permitido').'</strong>: ';
		if ($linkres->ban && $linkres->ban['match']) {
			echo $linkres->ban['match'];
		} else {
			echo $linkres->url;
		}
		echo '</p>';
		echo '<p><strong>'._('Razón').':</strong> '. $linkres->ban['comment'].'</p>';
		if ($linkres->ban['expire'] > 0) {
			echo '<p class="note"><strong>'._('caduca').'</strong>: '.get_date_time($linkres->ban['expire']).'</p>';
		}
		print_empty_submit_form();
		echo '</div>'. "\n";
		return;
	}

	// If the URL has changed, check again is not dupe
	if($linkres->url != $url && report_dupe($linkres->url)) return;

	$linkres->randkey = intval($_POST['randkey']);
	if(!$linkres->valid) {
		echo '<p class="error"><strong>'._('error leyendo el url').':</strong> '.htmlspecialchars($url).'</p>';
		// Dont allow new users with low karma to post wrong URLs
		if ($current_user->user_karma < 8 && $current_user->user_level == 'normal') {
			echo '<p>'._('URL inválido, incompleto o no permitido. Está fuera de línea, o tiene mecanismos antibots.').'</p>';
			print_empty_submit_form();
			return;
		}
		echo '<p>'._('No es válido, está fuera de línea, o tiene mecanismos antibots. <strong>Continúa</strong>, pero asegúrate que sea correcto').'</p>';
	}

	$linkres->status='discard';
	$linkres->author=$current_user->user_id;

	if (!$linkres->pingback()) {
		$linkres->trackback();
	}
	$trackback=htmlspecialchars($linkres->trackback);
	$linkres->create_blog_entry();
	$blog = new Blog;
	$blog->id = $linkres->blog;
	$blog->read();

	$blog_url_components = @parse_url($blog->url);
	$blog_url = $blog_url_components['host'].$blog_url_components['path'];
	// Now we check again against the blog table
	// it's done because there could be banned blogs like http://lacotelera.com/something
	if(($ban = check_ban($blog->url, 'hostname', false, true))) {
		echo '<p class="error"><strong>'._('URL inválido').':</strong> '.htmlspecialchars($url).'</p>';
		echo '<p>'._('El sitio').' '.$ban['match'].' '. _('está deshabilitado'). ' ('. $ban['comment'].') </p>';
		if ($ban['expire'] > 0) {
			echo '<p class="note"><strong>'._('caduca').'</strong>: '.get_date_time($ban['expire']).'</p>';
		}
		syslog(LOG_NOTICE, "Meneame, banned site ($current_user->user_login): $blog->url <- $_POST[url]");
		print_empty_submit_form();
		echo '</div>'. "\n";
		/*
		// If the domain is banned, decrease user's karma
		if ($linkres->banned && $current_user->user_level == 'normal') {
			$db->query("update users set user_karma = user_karma - 0.05 where user_id = $current_user->user_id");
		}
		*/
		return;
	}


	// check for users spamming several sites and networks
	// it does not allow a low "entropy"
	if ($sents > 30) {
		$ratio = (float) $db->get_var("select count(distinct link_blog)/count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 60 day)");
		$threshold = 1/log($sents, 2);
		if ($ratio <  $threshold ) {
			if ($db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 60 day) and link_blog = $blog->id") > 2) {
				syslog(LOG_NOTICE, "Meneame, forbidden due to low entropy: $ratio <  $threshold  ($current_user->user_login): $linkres->url");
				echo '<p class="error"><strong>'._('ya has enviado demasiados enlaces a los mismos sitios').'</strong></p> ';
				echo '<p class="error-text">'._('varía las fuentes, podría ser considerado spam').'</p>';
				echo '<br style="clear: both;" />' . "\n";
				echo '</div>'. "\n";
				return;
			}
		}
	}

	// Check the user does not send too many images or vídeos
	// they think this is a fotolog
	if ($sents > 5 && ($linkres->content_type == 'image' || $linkres->content_type == 'video')) {
		$image_links = intval($db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 60  day) and link_content_type in ('image', 'video')"));
		if ($image_links > $sents * 0.7) {
			syslog(LOG_NOTICE, "Meneame, forbidden due to too many images or video sent by user ($current_user->user_login): $linkres->url");
			echo '<p class="error"><strong>'._('ya has enviado demasiadas imágenes o vídeos').'</strong></p> ';
			//echo '<p class="error-text">'._('disculpa, no es un fotolog').'</p>';
			echo '<br style="clear: both;" />' . "\n";
			echo '</div>'. "\n";
			return;
		}
	}

	// Avoid users sending too many links to the same site in last hours
	$hours = 24;
	$same_blog = $db->get_var("select count(*) from links where link_date > date_sub(now(), interval $hours hour) and link_author=$current_user->user_id and link_blog=$linkres->blog and link_votes > 0");
	if ($same_blog > 2) {
		syslog(LOG_NOTICE, "Meneame, forbidden due to too many links to the same site in last $hours hours ($current_user->user_login): $linkres->url");
		echo '<p class="error"><strong>'._('demasiados enlaces al mismo sitio en las últimas horas').'</strong></p> ';
		echo '<br style="clear: both;" />' . "\n";
		echo '</div>'. "\n";
		return;
	}

	// avoid auto-promotion (autobombo)
	$minutes = 30;
	$same_blog = $db->get_var("select count(*) from links where link_date > date_sub(now(), interval $minutes minute) and link_author=$current_user->user_id and link_blog=$linkres->blog and link_votes > 0");
	if ($same_blog > 0 && $current_user->user_karma < 12) {
		syslog(LOG_NOTICE, "Meneame, forbidden due to short period between links to same site ($current_user->user_login): $linkres->url");
		echo '<p class="error"><strong>'._('ya has enviado un enlace al mismo sitio hace poco tiempo').'</strong></p> ';
		echo '<p class="error-text">'._('debes esperar'). " $minutes " . _(' minutos entre cada envío al mismo sitio.') . ', ';
		echo '<a href="'.$globals['base_url'].'faq-'.$dblang.'.php">'._('lee el FAQ').'</a></p>';
		echo '<br style="clear: both;" />' . "\n";
		echo '</div>'. "\n";
		return;
	}

	// Avoid spam (autobombo), count links in last two months
	$same_blog = $db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 60 day) and link_blog=$linkres->blog");

	$check_history =  $sents > 3 && $same_blog > 0 && ($ratio = $same_blog/$sents) > 0.5;
	if ($check_history) {
		echo '<p class="error"><strong>'._('has enviado demasiados enlaces a')." $blog->url".'</strong></p> ';
		if ($sents > 5 && $ratio > 0.75) {
			echo '<p class="error-text">'._('has superado los límites de envíos de este sitio').'</p>';
			// don't allow to continue
			syslog(LOG_NOTICE, "Meneame, warn, high ratio, process interrumped ($current_user->user_login): $linkres->url");
			return;
		} else {
			echo '<p class="error-text">'._('continúa, pero ten en cuenta podría recibir votos negativos').', ';
			echo '<a href="'.$globals['base_url'].'legal.php">'._('normas de uso del menáme').'</a>, ';
			echo '<a href="'.$globals['base_url'].'faq-'.$dblang.'.php">'._('el FAQ').'</a></p>';
			syslog(LOG_NOTICE, "Meneame, warn, high ratio, continue ($current_user->user_login): $linkres->url");
		}
	}



	$links_12hs = $db->get_var("select count(*) from links where link_date > date_sub(now(), interval 12 hour)");

	// check there is no an "overflow" from the same site
	$site_links = intval($db->get_var("select count(*) from links where link_date > date_sub(now(), interval 12 hour) and link_blog=$linkres->blog and link_status in ('queued', 'published')"));
	if ($site_links > 5 && $site_links > $links_12hs * 0.04) { // Only 4% from the same site
		syslog(LOG_NOTICE, "Meneame, forbidden due to overflow to the same site ($current_user->user_login): $linkres->url");
		echo '<p class="error"><strong>'._('ya se han enviado demasiadas noticias del mismo sitio, espera unos minutos por favor').'</strong></p> ';
		echo '<p class="error-text">'._('total en 12 horas').": $site_links , ". _('el máximo actual es'). ': ' . intval($links_12hs * 0.04). '</p>';
		echo '<br style="clear: both;" />' . "\n";
		echo '</div>'. "\n";
		return;
	}

	// check there is no an "overflow" of images
	if ($linkres->content_type == 'image' || $linkres->content_type == 'video') {
		$image_links = intval($db->get_var("select count(*) from links where link_date > date_sub(now(), interval 12 hour) and link_content_type in ('image', 'video')"));
		if ($image_links > 5 && $image_links > $links_12hs * 0.08) { // Only 8% images and videos
			syslog(LOG_NOTICE, "Meneame, forbidden due to overflow images ($current_user->user_login): $linkres->url");
			echo '<p class="error"><strong>'._('ya se han enviado demasiadas imágenes o vídeos, espera unos minutos por favor').'</strong></p> ';
			echo '<p class="error-text">'._('total en 12 horas').": $image_links , ". _('el máximo actual es'). ': ' . intval($links_12hs * 0.05). '</p>';
			echo '<br style="clear: both;" />' . "\n";
			echo '</div>'. "\n";
			return;
		}
	}

	if(($ban = check_ban($linkres->url, 'punished_hostname', false, true))) {
		echo '<p class="error"><strong>'._('Aviso').' '.$ban['match']. ':</strong> <em>'.$ban['comment'].'</em></p>';
		echo '<p>'._('mejor enviar el enlace a la fuente original, sino será penalizado').'</p>';
	}

	
	// Now stores new draft
	$linkres->ip = $globals['user_ip'];
	$linkres->sent_date = $linkres->date=time();
	$linkres->store();
	
	echo '<h2>'._('envío de una nueva noticia: paso 2 de 3').'</h2>'."\n";


	echo '<div class="genericform">'."\n";
	echo '<form action="submit.php" method="post" id="thisform" name="thisform">'."\n";

	echo '<input type="hidden" name="url" id="url" value="'.htmlspecialchars($linkres->url).'" />'."\n";
	echo '<input type="hidden" name="phase" value="2" />'."\n";
	echo '<input type="hidden" name="randkey" value="'.intval($_POST['randkey']).'" />'."\n";
	echo '<input type="hidden" name="key" value="'.$_POST['key'].'" />'."\n";
	echo '<input type="hidden" name="id" value="'.$linkres->id.'" />'."\n";

	echo '<fieldset><legend><span class="sign">'._('información del enlace').'</span></legend>'."\n";
	echo '<p class="genericformtxt"><strong>';
	echo mb_substr($linkres->url_title, 0, 200);
	echo '</strong><br/>';
	echo htmlspecialchars($linkres->url);
	echo '</p> '."\n";
	echo '</fieldset>'."\n";

	echo '<fieldset><legend><span class="sign">'._('detalles de la noticia').'</span></legend>'."\n";

	echo '<label for="title" accesskey="1">'._('título de la noticia').':</label>'."\n";
	echo '<p><span class="note">'._('título de la noticia. máximo: 120 caracteres').'</span>'."\n";
	// Is it an image or video?
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	$linkres->print_content_type_buttons();

	echo '<br/><input type="text" id="title" name="title" value="'.$link_title.'" size="80" maxlength="120" />';
	echo '</p>'."\n";

	echo '<label for="tags" accesskey="2">'._('etiquetas').':</label>'."\n";
	echo '<p><span class="note"><strong>'._('pocas palabras, genéricas, cortas y separadas por "," (coma)').'</strong> Ejemplo: <em>web, programación, software libre</em></span>'."\n";
	echo '<br/><input type="text" id="tags" name="tags" value="'.$link_tags.'" size="70" maxlength="70" /></p>'."\n";

	echo '<div style="float: right;">';
	print_simpleformat_buttons('bodytext');
	echo '<input readonly type="text" name="bodycounter" size="3" maxlength="3" value="550" /> <span class="note">' . _('caracteres libres') . '</span>&nbsp;&nbsp;';
	echo '</div>';

	echo '<label for="bodytext" accesskey="3">'._('descripción de la noticia').':</label>'."\n";
	echo '<p><span class="note"><strong>'._('describe la noticia en castellano. entre dos y cinco frases es suficiente. no deformes el contenido.').'</strong></span>'."\n";
	echo '<br /><textarea name="bodytext"  rows="10" cols="60" id="bodytext" onKeyDown="textCounter(document.thisform.bodytext,document.thisform.bodycounter,550)" onKeyUp="textCounter(document.thisform.bodytext,document.thisform.bodycounter,550)">';
	if (mb_strlen($linkres->url_description) > 40) {
		echo $linkres->url_description;
	}
	echo '</textarea>'."\n";
	echo '</p>'."\n";

	print_categories_form();

	echo '<p><label for="trackback">'._('trackback').':</label><br />'."\n";
	if (empty($trackback)) {
		echo '<span class="note">'._('puedes agregar o cambiar el trackback si ha sido detectado automáticamente').'</span>'."\n";
		echo '<input type="text" name="trackback" id="trackback" value="'.$trackback.'" class="form-full" /></p>'."\n";
	} else {
		echo '<span class="note">'.$trackback.'</span>'."\n";
		echo '<input type="hidden" name="trackback" id="trackback" value="'.$trackback.'"/></p>'."\n";
	}
	echo '<input class="button" type="button" onclick="window.history.go(-1)" value="'._('&#171; retroceder').'" />&nbsp;&nbsp;'."\n";
	echo '<input class="button" type="submit" value="'._('continuar &#187;').'" />'."\n";
	echo '</fieldset>'."\n";
	echo '</form>'."\n";
	echo '</div>'."\n";
	echo '</div>'."\n";
}


function do_submit2() {
	global $db, $dblang, $globals;

	$linkres=new Link;
	$linkres->id=$link_id = intval($_POST['id']);
	$linkres->read();

	$linkres->read_content_type_buttons($_POST['type']);

	// Check if the title contains [IMG], [IMGs], (IMG)... and mark it as image

	if (preg_match('/[\(\[](IMG|PICT*)s*[\)\]]/i', $_POST['title'])) {
		$_POST['title'] = preg_replace('/[\(\[](IMG|PICT*)s*[\)\]]/i', ' ', $_POST['title']);
		$linkres->content_type = 'image';
	} elseif (preg_match('/[\(\[](VID|VIDEO|Vídeo*)s*[\)\]]/i', $_POST['title'])) {
		$_POST['title'] = preg_replace('/[\(\[](VID|VIDEO|Vídeo*)s*[\)\]]/i', ' ', $_POST['title']);
		$linkres->content_type = 'video';
	}

	$linkres->category=intval($_POST['category']);
	$linkres->title = clean_text(preg_replace('/(\w) *[;.,] *$/', "$1", $_POST['title']), 40);  // It also deletes punctuaction signs at the end
	$linkres->tags = tags_normalize_string($_POST['tags']);
	$linkres->content = clean_text($_POST['bodytext']);
	if (link_errors($linkres)) {
		echo '<form class="genericform">'."\n";
		echo '<p><input class="button" type=button onclick="window.history.go(-1)" value="'._('&#171; retroceder').'"/></p>'."\n";
		echo '</form>'."\n";
		echo '</div>'."\n"; // opened in print_form_submit_error
		return;
	}

	$linkres->store();
	tags_insert_string($linkres->id, $dblang, $linkres->tags);
	$linkres->read();
	$edit = true;
	$link_title = $linkres->title;
	$link_content = $linkres->content;
	preload_indicators();
	echo '<div class="genericform">'."\n";
	
	echo '<h2>'._('envío de una nueva noticia: paso 3 de 3').'</h2>'."\n";

	echo '<form action="submit.php" method="post" class="genericform" onSubmit="$(\'#working\').html(\''._('enviando trackbacks').'...&nbsp;<img src=\\\'\'+img_src1+\'\\\'/>\'); return true;">'."\n";
	echo '<fieldset><legend><span class="sign">'._('detalles de la noticia').'</span></legend>'."\n";

	echo '<div class="genericformtxt"><label>'._('ATENCIÓN: esto es sólo una muestra!').'</label>&nbsp;&nbsp;<br/>'._('Ahora puedes 1) ').'<label>'._('retroceder').'</label>'._(' o 2)  ').'<label>'._('enviar a la cola y finalizar').'</label>'._('. Cualquier otro clic convertirá tu noticia en comida para <del>gatos</del> elefantes (o no).').'</div>';	

	echo '<div class="formnotice">'."\n";
	$linkres->print_summary('preview');
	echo '</div>'."\n";

	echo '<input type="hidden" name="phase" value="3" />'."\n";
	echo '<input type="hidden" name="randkey" value="'.intval($_POST['randkey']).'" />'."\n";
	echo '<input type="hidden" name="key" value="'.$_POST['key'].'" />'."\n";
	echo '<input type="hidden" name="id" value="'.$linkres->id.'" />'."\n";
	echo '<input type="hidden" name="trackback" value="'.htmlspecialchars(trim($_POST['trackback'])).'" />'."\n";

	echo '<br style="clear: both;" /><br style="clear: both;" />'."\n";
	echo '<input class="button" type="button" onclick="window.history.go(-1)" value="'._('&#171; retroceder').'"/>&nbsp;&nbsp;'."\n";
	echo '<input class="button" type="submit" value="'._('enviar a la cola y finalizar &#187;').'" ';
	echo '/>&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span>';
	echo '</fieldset>'."\n";
	echo '</form>'."\n";
	echo '</div>'."\n";
}

function do_submit3() {
	global $db, $current_user;

	$linkres=new Link;

	$linkres->id=$link_id = intval($_POST['id']);
	if(!check_link_key() || !$linkres->read()) die;
	// Check it is not in the queue already
	if($linkres->votes == 0 && $linkres->status != 'queued') {
		$db->transaction();
		$linkres->status='queued';
		$linkres->sent_date = $linkres->date=time();
		$linkres->get_uri();
		$linkres->store();
		$linkres->insert_vote($current_user->user_karma);
		$db->commit();

		// Add the new link log/event
		require_once(mnminclude.'log.php');
		log_conditional_insert('link_new', $linkres->id, $linkres->author);

		$db->query("delete from links where link_author = $linkres->author and link_date > date_sub(now(), interval 30 minute) and link_status='discard' and link_votes=0");
		if(!empty($_POST['trackback'])) {
			$trackres = new Trackback;
			$trackres->url=clean_input_url($_POST['trackback']);
			$trackres->link_id=$linkres->id;
			$trackres->link=$linkres->url;
			$trackres->author=$linkres->author;
			$trackres->status = 'pendent';
			$trackres->store();
		}
		fork("backend/send_pingbacks.php?id=$linkres->id");
	}

	header('Location: '. $linkres->get_permalink());
	die;
	
}

function check_link_key() {
	global $site_key, $current_user;
	return $_POST['key'] == md5($_POST['randkey'].$current_user->user_id.$current_user->user_email.$site_key.get_server_name());
}

function link_errors($linkres) {
	$error = false;
	// Errors
	if(! check_link_key() || intval($_POST['randkey']) != $linkres->randkey) {
		print_form_submit_error(_("clave incorrecta"));
		$error = true;
	}
	if($linkres->status != 'discard') {
		//echo '<br style="clear: both;" />';
		print_form_submit_error(_("La historia ya está en cola").": $linkres->status");
		$error = true;
	}
	if(strlen($linkres->title) < 10  || strlen($linkres->content) < 30 ) {
		print_form_submit_error(_("Título o texto incompletos"));
		$error = true;
	}
	if(get_uppercase_ratio($linkres->title) > 0.25  || get_uppercase_ratio($linkres->content) > 0.25 ) {
		print_form_submit_error(_("Demasiadas mayúsculas en el título o texto"));
		$error = true;
	}
	if(mb_strlen(html_entity_decode($linkres->title, ENT_COMPAT, 'UTF-8'), 'UTF-8') > 120  || mb_strlen(html_entity_decode($linkres->content, ENT_COMPAT, 'UTF-8'), 'UTF-8') > 550 ) {
		print_form_submit_error(_("Título o texto demasiado largos"));
		$error = true;
	}
	if(strlen($linkres->tags) < 3 ) {
		print_form_submit_error(_("No has puesto etiquetas"));
		$error = true;
	}

	if(preg_match('/.*http:\//', $linkres->title)) {
		//echo '<br style="clear: both;" />';
		print_form_submit_error(_("Por favor, no pongas URLs en el título, no ofrece información"));
		$error = true;
	}
	if(!$linkres->category > 0) {
		//echo '<br style="clear: both;" />';
		print_form_submit_error(_("Categoría no seleccionada"));
		$error = true;
	}
	return $error;
}

function print_form_submit_error($mess) {
	static $previous_error=false;
	
	if (!$previous_error) {
		// ex container-wide
		echo '<div class="genericform">'."\n"; // this div MUST be closed after function call!
		echo '<h2>'._('ooops!').'</h2>'."\n";
		$previous_error = true;
	}
	echo '<div class="form-error-submit">&nbsp;&nbsp;'._($mess).'</div>'."\n";
}

function report_dupe($url) {
	global $globals;

	$link = new Link;
	if(($found = $link->duplicates($url))) {
		$dupe = new Link;
		$dupe->id = $found;
		$dupe->read();
		echo '<p class="error"><strong>'._('noticia repetida!').'</strong></p> ';
		echo '<p class="error-text">'._('lo sentimos').'</p>';
		$dupe->print_summary();
		echo '<br style="clear: both;" /><br/>' . "\n";
		echo '<form class="genericform" action="">';
		echo '<input class="button" type="button" onclick="window.history.go(-1)" value="'._('&#171; retroceder').'" />';
		echo '</form>'. "\n";
		echo '</div>'. "\n";
		return true;
	}
	return false;
}

?>
