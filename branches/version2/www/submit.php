<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
include(mnminclude.'tags.php');
include(mnminclude.'ban.php');
include(mnminclude.'blog.php');

$globals['ads'] = true;

if(isset($_POST["phase"])) {
	force_authentication();
	switch ($_POST["phase"]) {
		case 1:
			do_header(_("enviar noticia"), "post");
			do_submit1();
			break;
		case 2:
			do_header(_("enviar noticia"), "post");
			do_submit2();
			break;
		case 3:
			do_submit3();
			break;
	}
} else {
	check_already_sent();
	force_authentication();
	do_header(_("enviar noticia"), "post");
	do_submit0();
}
do_footer();
exit;

function preload_indicators() {
	global $globals;

	echo '<SCRIPT type="text/javascript">'."\n";
	echo '<!--'."\n";
	echo 'var img_src1=\''.$globals['base_url'].'img/common/indicator_orange.gif\''."\n";;
	echo 'var img1= new Image(); '."\n";
	echo 'img1.src = img_src1';
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
	global $globals;

	preload_indicators();
	if (!empty($_GET['url'])) {
		$url = clean_input_url($_GET['url']);
	} else {
		$url = 'http://';
	}
	echo '<div id="genericform">';
	echo '<fieldset><legend><span class="sign">'._('dirección de la noticia').'</span></legend>';
	echo '<form action="submit.php" method="post" id="thisform" onSubmit="$(\'#working\').html(\''._('verificando').'...&nbsp;<img src=\\\'\'+img_src1+\'\\\'/>\'); return true;">';
	echo '<p class="l-top"><label for="url">'._('url').':</label><br />';
	echo '<input type="text" name="url" id="url" value="'.htmlspecialchars($url).'" class="form-full" /></p>';
	echo '<input type="hidden" name="phase" value="1" />';
	echo '<input type="hidden" name="randkey" value="'.rand(10000,10000000).'" />';
	echo '<input type="hidden" name="id" value="c_1" />';
	echo '<p class="l-bottom"><input class="genericsubmit" type="submit" value="'._('continuar &#187;').'" ';
	echo '/>&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span></p>';
	echo '</form>';
	echo '</fieldset>';
	echo '</div>';
}

function do_submit0() {
	do_banner_top();
	echo '<div id="container-wide">' . "\n";
	echo '<div id="genericform-contents">'."\n";
	echo '<h2>'._('envío de una nueva noticia: paso 1 de 3').'</h2>';
	echo '<div class="instruction">';
	echo '<h3>'._('por favor, respeta estas instrucciones para mejorar la calidad:').'</h3>';
	echo '<ul class="instruction-list">';
	echo '<li><strong>'._('contenido interesante').':</strong> '._('¿la noticia conseguirá suficientes votos por méritos propios? ¿interesará a una cantidad razonable de lectores?').'</li>';
	echo '<li><strong>'._('enlaza la fuente original').':</strong> '._('no hagas perder tiempo a los lectores.').'</li>';
	echo '<li><strong>'._('busca antes').':</strong> '._('evita duplicar noticias.').'</li>';
	echo '<li><strong>'._('sé descriptivo').':</strong> '._('explica la noticia lo mejor que puedas y porqué es interesante').'.</li>';
	//echo '<li><strong>'._('repetimos, por las dudas... ¡enlaza la fuente original!').'</strong> </li>';
	echo '<li><strong>'._('respeta el voto de los demás').'</strong>. '._('si los votos o la falta de ellos te pueden afectar personalmente, es mejor que no envíes la noticia.').'</li>';
	//echo '<li><strong>'._('NO envíes').':</strong> '._('spam, sensacionalismo, amarillismo, cotilleos, noticias del corazón, provocaciones, difamaciones e insultos.').'</li>';
	echo '<li class="underl-y"><strong>¿'._('has leído las').'</strong> <a href="libs/ads/legal-meneame.php#tos" target="_blank">'._('condiciones de uso').'</a>?</li>';
	echo '</ul></div>'."\n";
	print_empty_submit_form();
	echo '</div>';
}

function do_submit1() {
	global $db, $dblang, $current_user, $globals;

	$url = clean_input_url($_POST['url']);
	$url = preg_replace('/^http:\/\/http:\/\//', 'http://', $url); // Some users forget to delete the foo http://
	$url = preg_replace('/#.*$/', '', $url); // Remove the "#", people just abuse

	do_banner_top();
	echo '<div id="container-wide">' . "\n";
	echo '<div id="genericform-contents">'."\n";

	$new_user = false;
	if ($globals['min_karma_for_links'] > 0 && $current_user->user_karma < $globals['min_karma_for_links'] ) {
		echo '<p class="error"><strong>'._('no tienes el mínimo de karma para enviar una nueva historia').'</strong></p> ';
// 		echo '<br style="clear: both;" />' . "\n";
		echo '</div>'. "\n";
		return;
	}


	if(check_ban($globals['user_ip'], 'ip', true) || check_ban_proxy()) {
		echo '<p class="error"><strong>'._('Dirección IP no permitida para enviar').':</strong> '.$globals['user_ip'].' ('. $globals['ban_message'].')</p>';
		syslog(LOG_NOTICE, "Meneame, banned IP $globals[user_ip] ($current_user->user_login): $url");
		print_empty_submit_form();
		echo '</div>'. "\n";
		return;
	}

	// Number of links sent by the user
	$sents = $db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 90 day) and link_votes > 0");
	// check that the user also votes, not only sends links
	// if is a new user requires at least 10 votes
	if ($current_user->user_karma < 6.1) {
		$user_votes_total = (int) $db->get_var("select count(*) from votes where vote_type='links' and vote_user_id=$current_user->user_id");
		$user_votes = (int) $db->get_var("select count(*) from votes where vote_type='links' and vote_date > date_sub(now(), interval 72 hour) and vote_user_id=$current_user->user_id");
		$user_links = 1 + $db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 24 hour) and link_status != 'discard'");
		$total_links = (int) $db->get_var("select count(*) from links where link_date > date_sub(now(), interval 24 hour) and link_status = 'queued'");
		echo "<!-- $user_votes_total, $user_links, $total_links -->\n";
		if ($sents == 0) {
			// If is a new user, requires more votes, to avoid spam
			$min_votes = 10;
			$new_user = true;
		} else {
			$min_votes = min(4, intval($total_links/20)) * $user_links;
		}
		if ($current_user->user_level != 'god' && $current_user->user_level != 'admin' && $user_votes < $min_votes) {
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
	// it counts the numbers of links in the last 2 hours
	$same_user = $db->get_var("select count(*) from links where link_date > date_sub(now(), interval 2 hour) and link_author=$current_user->user_id and link_votes > 0");
	$same_ip = $db->get_var("select count(*) from links, votes where link_date > date_sub(now(), interval 2 hour) and vote_type='links' and vote_link_id = link_id and vote_user_id = link_author and vote_ip_int = ".$globals['user_ip_int']);
	if ($same_user > 6 || $same_ip > 6 ) {
		echo '<p class="error"><strong>'._('debes esperar, ya se enviaron varias con el mismo usuario o dirección IP').  '</strong></p>';
		echo '<br style="clear: both;" />' . "\n";
		echo '</div>'. "\n";
		return;
	}

	// avoid users sending continuous "rubbsih" or "propaganda", specially new users
	// it takes in account the number of positive votes in the last six hours
	if ($same_user > 1 && $current_user->user_karma < 12) {
		$positives_received = $db->get_var("select sum(link_votes) from links where link_date > date_sub(now(), interval 2 hour) and link_author = $current_user->user_id");
		$negatives_received = $db->get_var("select sum(link_negatives) from links where link_date > date_sub(now(), interval 2 hour) and link_author = $current_user->user_id");
		echo "<!-- Positives: $positives_received -->\n";
		echo "<!-- Negatives: $negatives_received -->\n";
		if ($negatives_received > 10 && $negatives_received > $positives_received * 1.5) {
			echo '<p class="error"><strong>'._('debes esperar, has tenido demasiados votos negativos en tus últimos envíos').  '</strong></p>';
			echo '<br style="clear: both;" />' . "\n";
			echo '</div>'. "\n";
			return;
		}
	}
	
	$linkres=new Link;

	$edit = false;

	if(report_dupe($url)) return;


	if(!$linkres->check_url($url, true, true) || !$linkres->get($url)) {
		echo '<p class="error"><strong>'._('URL erróneo o no permitido').'</strong></p><p> '.htmlspecialchars($url).'<br />';
		echo '<br /><strong>'._('Razón').':</strong> '. $globals['ban_message'].'</p>';
		// If the domain is banned, decrease user's karma
		if ($linkres->banned) {
			$db->query("update users set user_karma = user_karma - 0.05 where user_id = $current_user->user_id");
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
		if ($current_user->user_karma < 12 && $current_user->user_level == 'normal') {
			echo '<p>'._('URL inválido, incompleto o no permitido').'</p>';
			print_empty_submit_form();
			return;
		}
		echo '<p>'._('No es válido, está fuera de línea, o tiene mecanismos antibots. <strong>Continúa</strong>, pero asegúrate que sea correcto').'</p>';
	}

	$linkres->status='discard';
	$linkres->author=$current_user->user_id;

	if (!$linkres->trackback()) {
		$linkres->pingback();
	}
	$trackback=htmlspecialchars($linkres->trackback);
	$linkres->create_blog_entry();
	$blog = new Blog;
	$blog->id = $linkres->blog;
	$blog->read();

	$blog_url_components = @parse_url($blog->url);
	$blog_url = $blog_url_components[host].$blog_url_components[path];
	// Now we check against the blog table
	// it's done because there could be banned blogs like http://lacotelera.com/something
	if(check_ban($blog_url, 'hostname', false, true)) {
		echo '<p class="error"><strong>'._('URL inválido').':</strong> '.htmlspecialchars($url).'</p>';
		echo '<p>'._('El sitio') . " $blog->url ". _('está deshabilitado'). ' ('. $globals['ban_message'].') </p>';
		syslog(LOG_NOTICE, "Meneame, banned site ($current_user->user_login): $blog->url <- $_POST[url]");
		print_empty_submit_form();
		echo '</div>'. "\n";
		return;
	}


	// check for users spamming several sites and networks
	// it does not allow a low "entropy"
	if ($sents > 30) {
		$ratio = (float) $db->get_var("select count(distinct link_blog)/count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 90 day) and link_votes > 0");
		$threshold = 1/log($sents, 2);
		if ($ratio <  $threshold ) {
			if ($db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 90 day) and link_blog = $blog->id and link_votes > 0") > 2) {
				syslog(LOG_NOTICE, "Meneame, forbidden due to low entropy: $ratio <  $threshold  ($current_user->user_login): $linkres->url");
				echo '<p class="error"><strong>'._('ya has enviado demasiados enlaces a los mismos sitios').'</strong></p> ';
				echo '<p class="error-text">'._('varía las fuentes, podría ser considerado spam').'</p>';
				echo '<br style="clear: both;" />' . "\n";
				echo '</div>'. "\n";
				return;
			}
		}
	}

	// Check the user does not send too many images
	// they think this is a fotolog
	if ($sents > 5 && $linkres->content_type == 'image') {
		$image_links = intval($db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 90  day) and link_content_type = 'image' and link_votes > 0"));
		if ($image_links > $sents * 0.3) {
			syslog(LOG_NOTICE, "Meneame, forbidden due to too many images sent by user ($current_user->user_login): $linkres->url");
			echo '<p class="error"><strong>'._('ya has enviado demasiadas imágenes').'</strong></p> ';
			echo '<p class="error-text">'._('disculpa, no es un fotolog').'</p>';
			echo '<br style="clear: both;" />' . "\n";
			echo '</div>'. "\n";
			return;
		}
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

	// Avoid spam, count links in last three months
	$same_blog = $db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 90 day) and link_blog=$linkres->blog and link_votes > 0");

	// Check if the domain should be banned
	$check_history =  $sents > 2 && $same_blog > 0 && ($ratio = $same_blog/$sents) > 0.5;

	// check clones also for new users
	if ($sents == 0 || $check_history) {
		// Count unique users 
		// TODO: we should discard users with the same IP (clones)
		$unique_users = (int) $db->get_var("select count(distinct link_author) from links, users, votes where link_blog=$blog->id  and link_date > date_sub(now(), interval 30 day) and user_id = link_author and user_level != 'disabled' and vote_type='links' and vote_link_id = link_id and vote_user_id = link_author and vote_ip_int != ".$globals['user_ip_int']);

		// Check for user clones
		$clones = $db->get_var("select count(distinct link_author) from links, votes where link_author!=$current_user->user_id and link_date > date_sub(now(), interval 20 day) and link_blog=$linkres->blog and link_votes > 0 and vote_type='links' and vote_link_id=link_id and link_author = vote_user_id and vote_ip_int = ".$globals['user_ip_int']);

		if ($clones > 0 && $unique_users < 3) {
			// we detected that another user has sent to the same URL from the same IP
			echo '<p class="error"><strong>'._('se han detectado usuarios clones que envían al sitio')." $blog->url".'</strong></p> ';
			$ban_period_txt = _('un mes');
			$ban = insert_ban('hostname', $blog_url, _('usuarios clones'). " $current_user->user_login ($blog_url)", time() + 86400*30);
			$banned_host = $ban->ban_text;
			echo '<p class="error-text"><strong>'._('el dominio'). " '$banned_host' ". _('ha sido baneado por')." $ban_period_txt</strong>, ";
			echo '<a href="'.$globals['base_url'].'libs/ads/legal-meneame.php">'._('normas de uso del menáme').'</a></p>';
			syslog(LOG_NOTICE, "Meneame, banned '$ban_period_txt' due to user clones ($current_user->user_login): $banned_host  <- $linkres->url");
			echo '<br style="clear: both;" />' . "\n";
			echo '</div>'. "\n";
			return;
		}
		// end clones
	}

	if ($check_history) {
		// Calculate ban period according to previous karma
		$avg_karma = (int) $db->get_var("select avg(link_karma) from links where link_blog=$blog->id and link_date > date_sub(now(), interval 30 day) and link_votes > 0");
		// This is the case of unique/few users sending just their site and take care of choosing goog titles and text
		// the condition is stricter, more links and higher ratio
		if (($sents > 3 && $ratio > 0.9) || ($sents > 6 && $ratio > 0.8) || ($sents > 12 && $ratio > 0.6)) {
			if ($unique_users < 3) {
				if ($avg_karma < -10) {
					$ban_period = 86400*30;
					$ban_period_txt = _('un mes');
				} else {
					$ban_period = 86400*7;
					$ban_period_txt = _('una semana');
				}
				syslog(LOG_NOTICE, "Meneame, high ratio ($ratio) and few users ($unique_users), going to ban $blog->url ($current_user->user_login)");
			}
		// Otherwise check previous karma
		} elseif ($sents > 4 && $avg_karma < 30) {
			if ($avg_karma < -40) {
				$ban_period = 86400*30;
				$ban_period_txt = _('un mes');
			} elseif ($avg_karma < -10) {
				$ban_period = 86400*7;
				$ban_period_txt = _('una semana');
			} elseif ($avg_karma < 10) {
				$ban_period = 86400;
				$ban_period_txt = _('un día');
			} else {
				$ban_period = 7200;
				$ban_period_txt = _('dos horas');
			}
			syslog(LOG_NOTICE, "Meneame, high ratio ($ratio) and low karma ($avg_karma), going to ban $blog->url ($current_user->user_login)");
		}
		if ($ban_period > 0) {
			echo '<p class="error"><strong>'._('ya has enviado demasiados enlaces a')." $blog->url".'</strong></p> ';
			echo '<p class="error-text">'._('varía tus fuentes, es para evitar abusos y enfados por votos negativos') . ', ';
			echo '<a href="'.$globals['base_url'].'libs/ads/legal-meneame.php">'._('normas de uso del menáme').'</a>, ';
			echo '<a href="'.$globals['base_url'].'faq-'.$dblang.'.php">'._('el FAQ').'</a></p>';

			if (!empty($blog_url)) {
				$ban = insert_ban('hostname', $blog_url, _('envíos excesivos de'). " $current_user->user_login", time() + $ban_period);
				$banned_host = $ban->ban_text;
				echo '<p class="error-text"><strong>'._('el dominio'). " '$banned_host' ". _('ha sido baneado por')." $ban_period_txt</strong></p> ";
				syslog(LOG_NOTICE, "Meneame, banned '$ban_period_txt' due to high ratio ($current_user->user_login): $banned_host  <- $linkres->url");
			} else {
				syslog(LOG_NOTICE, "Meneame, error parsing during ban: $blog->id, $blog->url ($current_user->user_login)");
			}
			echo '<br style="clear: both;" />' . "\n";
			echo '</div>'. "\n";
			return;
		} elseif ($sents > 0) {  // Just in case check again sent (paranoia setting)
			echo '<p class="error"><strong>'._('ya has enviado demasiados enlaces a')." $blog->url".'</strong></p> ';
			echo '<p class="error-text">'._('el sitio podría ser baneado automáticamente si continúas enviando').', ';
			echo '<a href="'.$globals['base_url'].'libs/ads/legal-meneame.php">'._('normas de uso del menáme').'</a>, ';
			echo '<a href="'.$globals['base_url'].'faq-'.$dblang.'.php">'._('el FAQ').'</a></p>';
			if ($sents > 5 && $ratio > 0.75) {
				// don't allow to continue
				syslog(LOG_NOTICE, "Meneame, warn, high ratio, process interrumped ($current_user->user_login): $linkres->url");
				return;
			} else {
				syslog(LOG_NOTICE, "Meneame, warn, high ratio, continue ($current_user->user_login): $linkres->url");
			}
		}
	}



	$links_12hs = $db->get_var("select count(*) from links where link_date > date_sub(now(), interval 12 hour) and link_status in ('published', 'queued', 'discard')");

	// check there is no an "overflow" from the same site
	if ($current_user->user_karma < 18) {
		$site_links = intval($db->get_var("select count(*) from links where link_date > date_sub(now(), interval 12 hour) and link_status in ('published', 'queued', 'discard') and link_blog=$linkres->blog"));
		if ($site_links > 5 && $site_links > $links_12hs * 0.04) { // Only 4% from the same site
			syslog(LOG_NOTICE, "Meneame, forbidden due to overflow to the same site ($current_user->user_login): $linkres->url");
			echo '<p class="error"><strong>'._('ya se han enviado demasiadas noticias del mismo sitio, espera unos minutos por favor').'</strong></p> ';
			echo '<p class="error-text">'._('total en 12 horas').": $site_links , ". _('el máximo actual es'). ': ' . intval($links_12hs * 0.04). '</p>';
			echo '<br style="clear: both;" />' . "\n";
			echo '</div>'. "\n";
			return;
		}
	}
	// check there is no an "overflow" of images
	if ($linkres->content_type == 'image') {
		$image_links = intval($db->get_var("select count(*) from links where link_date > date_sub(now(), interval 12 hour) and link_status in ('published', 'queued', 'discard') and link_content_type = 'image'"));
		if ($image_links > 5 && $image_links > $links_12hs * 0.04) { // Only 4% images
			syslog(LOG_NOTICE, "Meneame, forbidden due to overflow images ($current_user->user_login): $linkres->url");
			echo '<p class="error"><strong>'._('ya se han enviado demasiadas imágenes, espera unos minutos por favor').'</strong></p> ';
			echo '<p class="error-text">'._('total en 12 horas').": $image_links , ". _('el máximo actual es'). ': ' . intval($links_12hs * 0.04). '</p>';
			echo '<br style="clear: both;" />' . "\n";
			echo '</div>'. "\n";
			return;
		}
	}

	
	// Now stores new draft
	$linkres->ip = $globals['user_ip'];
	$linkres->store();
	
	echo '<h2>'._('envío de una nueva noticia: paso 2 de 3').'</h2>'."\n";


	echo '<div id="genericform">'."\n";
	echo '<form action="submit.php" method="post" id="thisform" name="thisform">'."\n";

	echo '<input type="hidden" name="url" id="url" value="'.htmlspecialchars($linkres->url).'" />'."\n";
	echo '<input type="hidden" name="phase" value="2" />'."\n";
	echo '<input type="hidden" name="randkey" value="'.intval($_POST['randkey']).'" />'."\n";
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
	echo '<p><span class="genericformnote">'._('título de la noticia. máximo: 120 caracteres').'</span>'."\n";

	echo '<br/><input type="text" id="title" name="title" value="'.$link_title.'" size="80" maxlength="120" />';

	// Is it an image?
	if ($linkres->content_type != 'image') {
   		echo '&nbsp;&nbsp;<input type="checkbox" '.$imagechecked.' name="is_image" />';
   	}
   	echo '&nbsp;<img src="'.$globals['base_url'].'img/common/is-photo01.png" width="22" height="18" alt="'._('¿es una imagen?').'" title="'._('¿es una imagen?').'"/>';

	echo '</p>'."\n";

	echo '<label for="tags" accesskey="2">'._('etiquetas').':</label>'."\n";
	echo '<p><span class="genericformnote"><strong>'._('pocas palabras, genéricas, cortas y separadas por "," (coma)').'</strong> Ejemplo: <em>web, programación, software libre</em></span>'."\n";
	echo '<br/><input type="text" id="tags" name="tags" value="'.$link_tags.'" size="70" maxlength="70" /></p>'."\n";

	print_simpleformat_buttons('bodytext');

	echo '<p><label for="bodytext" accesskey="3">'._('descripción de la noticia').':</label>'."\n";
	echo '<br /><span class="genericformnote">'._('describe la noticia con tus palabras. entre dos y cinco frases es suficiente. sé cuidadoso.').'</span>'."\n";
	echo '<br /><textarea name="bodytext"  rows="10" cols="60" id="bodytext" onKeyDown="textCounter(document.thisform.bodytext,document.thisform.bodycounter,550)" onKeyUp="textCounter(document.thisform.bodytext,document.thisform.bodycounter,550)"></textarea>'."\n";
	echo '<br /><input readonly type="text" name="bodycounter" size="3" maxlength="3" value="550" /> <span class="genericformnote">' . _('caracteres libres') . '</span>';
	echo '</p>'."\n";

	print_categories_form();

	echo '<p><label for="trackback">'._('trackback').':</label><br />'."\n";
	if (empty($trackback)) {
		echo '<span class="genericformnote">'._('puedes agregar o cambiar el trackback si ha sido detectado automáticamente').'</span>'."\n";
		echo '<input type="text" name="trackback" id="trackback" value="'.$trackback.'" class="form-full" /></p>'."\n";
	} else {
		echo '<span class="genericformnote">'.$trackback.'</span>'."\n";
		echo '<input type="hidden" name="trackback" id="trackback" value="'.$trackback.'"/></p>'."\n";
	}
	echo '<input class="genericsubmit" type="button" onclick="window.history.go(-1)" value="'._('&#171; retroceder').'" />&nbsp;&nbsp;'."\n";
	echo '<input class="genericsubmit" type="submit" value="'._('continuar &#187;').'" />'."\n";
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

	if ($_POST['is_image']) {
		$linkres->content_type = 'image';
	}

	$linkres->category=intval($_POST['category']);
	$linkres->title = clean_text(preg_replace('/(\w) *[;.,] *$/', "$1", $_POST['title']), 40);  // It also deletes punctuaction signs at the end
	$linkres->tags = tags_normalize_string($_POST['tags']);
	$linkres->content = clean_text($_POST['bodytext']);
	if (link_errors($linkres)) {
		echo '<form id="genericform">'."\n";
		echo '<p><input class="genericsubmit" type=button onclick="window.history.go(-1)" value="'._('&#171; retroceder').'"/></p>'."\n";
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
	do_banner_top();
	preload_indicators();
	echo '<div id="container-wide">' . "\n";
	echo '<div id="genericform-contents">'."\n";
	
	echo '<h2>'._('envío de una nueva noticia: paso 3 de 3').'</h2>'."\n";

	echo '<form action="submit.php" method="post" id="genericform" onSubmit="$(\'#working\').html(\''._('enviando trackbacks').'...&nbsp;<img src=\\\'\'+img_src1+\'\\\'/>\'); return true;">'."\n";
	echo '<fieldset><legend><span class="sign">'._('detalles de la noticia').'</span></legend>'."\n";

	echo '<div class="genericformtxt"><label>'._('ATENCIÓN: esto es sólo una muestra!').'</label>&nbsp;&nbsp;<br/>'._('Ahora puedes 1) ').'<label>'._('retroceder').'</label>'._(' o 2)  ').'<label>'._('enviar a la cola y finalizar').'</label>'._('. Cualquier otro clic convertirá tu noticia en comida para <del>gatos</del> elefantes (o no).').'</div>';	

	echo '<div class="formnotice">'."\n";
	$linkres->print_summary('preview');
	echo '</div>'."\n";

	echo '<input type="hidden" name="phase" value="3" />'."\n";
	echo '<input type="hidden" name="randkey" value="'.intval($_POST['randkey']).'" />'."\n";
	echo '<input type="hidden" name="id" value="'.$linkres->id.'" />'."\n";
	echo '<input type="hidden" name="trackback" value="'.htmlspecialchars(trim($_POST['trackback'])).'" />'."\n";

	echo '<br style="clear: both;" /><br style="clear: both;" />'."\n";
	echo '<input class="genericsubmit" type="button" onclick="window.history.go(-1)" value="'._('&#171; retroceder').'"/>&nbsp;&nbsp;'."\n";
	echo '<input class="genericsubmit" type="submit" value="'._('enviar a la cola y finalizar &#187;').'" ';
	echo '/>&nbsp;&nbsp;&nbsp;<span id="working">&nbsp;</span>';
	echo '</fieldset>'."\n";
	echo '</form>'."\n";
	echo '</div>'."\n";
}

function do_submit3() {
	global $db, $current_user;

	$linkres=new Link;

	$linkres->id=$link_id = intval($_POST['id']);
	if(!$linkres->read()) die;
	// Check it is not in the queue already
	if($linkres->votes == 0 && $linkres->status != 'queued') {
		$linkres->status='queued';
		$linkres->date=time();
		$linkres->get_uri();
		$linkres->store();
		$linkres->insert_vote($current_user->user_id, $current_user->user_karma);

		// Add the new link log/event
		require_once(mnminclude.'log.php');
		log_conditional_insert('link_new', $linkres->id, $linkres->author);

		$db->query("delete from links where link_author = $linkres->author and link_status='discard' and link_votes=0");
		if(!empty($_POST['trackback'])) {
			require_once(mnminclude.'trackback.php');
			$trackres = new Trackback;
			$trackres->url=clean_input_url($_POST['trackback']);
			$trackres->link_id=$linkres->id;
			$trackres->link=$linkres->url;
			//$trackres->title=$linkres->title;
			$trackres->author=$linkres->author;
			//$trackres->content=$linkres->content;
			$res = $trackres->send($linkres);
		}
		fork("backend/send_pingbacks.php?id=$linkres->id");
	}

	header('Location: '. $linkres->get_permalink());
	die;
	
}

function link_errors($linkres)
{
	$error = false;
	// Errors
	if(intval($_POST['randkey']) != $linkres->randkey) {
		//echo '<br style="clear: both;" />';
		print_form_submit_error(_("Clave incorrecta"));
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
		do_banner_top();
		echo '<div id="container-wide">' . "\n";
		echo '<div id="genericform-contents">'."\n"; // this div MUST be closed after function call!
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
		echo '<p class="error-text"><strong><a href="'.$dupe->get_permalink().'">'.$dupe->title.'</a></strong>';
		echo '<br style="clear: both;" /><br style="clear: both;" />' . "\n";
		echo '<form id="genericform" action="">';
		echo '<input class="genericsubmit" type=button onclick="window.history.go(-1)" value="'._('&#171; retroceder').'" />';
		echo '</form>'. "\n";
		echo '</div>'. "\n";
		return true;
	}
	return false;
}

?>
