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

global $errors;
$errors = array();

if(isset($_POST["phase"])) {
	force_authentication();
	switch ($_POST["phase"]) {
		case 1:
			do_header(_("enviar historia") . " 2/3", "post");
			echo '<div id="singlewrap">' . "\n";
			if (! do_submit1()) {
				// Just to display error messages
				$link = new Link;
				$link->randkey = rand(10000,10000000);
				$link->key = md5($link->randkey.$current_user->user_id.$current_user->user_email.$site_key.get_server_name());
				Haanga::Load('link/submit_empty_form.html', compact('link', 'errors'));
			}
			break;
		case 2:
			do_header(_("enviar historia") . " 3/3", "post");
			echo '<div id="singlewrap">' . "\n";
			if (! do_submit2()) {
				// Just to display error messages
				$link = new Link;
				$link->randkey = rand(10000,10000000);
				$link->key = md5($link->randkey.$current_user->user_id.$current_user->user_email.$site_key.get_server_name());
				Haanga::Load('link/submit_empty_form.html', compact('link', 'errors'));
			}
			break;
		case 3:
			do_submit3();
			break;
	}
} else {
	check_already_sent();
	force_authentication();
	do_header(_("enviar historia") . " 1/3", "post");
	echo '<div id="singlewrap">' . "\n";
	do_submit0();
}
echo "</div>\n"; // singlewrap

do_footer();
exit;

function check_already_sent() {
	global $db;
	// Check if the url has been sent already
	if (!empty($_GET['url'])) {
		if (($found = Link::duplicates($_GET['url']))) {
			$link = new Link;
			$link->id = $found;
			if($link->read()) {
				header('Location: ' . $link->get_permalink());
				die;
			}
		}
	}
}

function do_submit0() {
	global $current_user, $site_key;


	$link = new Link;
	$link->randkey = rand(10000,10000000);
	$link->key = md5($link->randkey.$current_user->user_id.$current_user->user_email.$site_key.get_server_name());
	if (!empty($_GET['url'])) {
		$link->url = clean_input_url($_GET['url']);
	}
	Haanga::Load('link/submit0.html', compact('link'));
	return true;
}

function do_submit1() {
	global $db, $dblang, $current_user, $globals, $errors;



	$url = clean_input_url($_POST['url']);
	$url = preg_replace('/#[^\/]*$/', '', $url); // Remove the "#", people just abuse
	$url = preg_replace('/^http:\/\/http:\/\//', 'http://', $url); // Some users forget to delete the foo http://
	if (! preg_match('/^\w{3,6}:\/\//', $url)) { // http:// forgotten, add it
		$url = 'http://'.$url;
	}

	$new_user = false;
	if (!check_link_key()) {
		add_submit_error( _('clave incorrecta'));
		return false;
	}
	if ($globals['min_karma_for_links'] > 0 && $current_user->user_karma < $globals['min_karma_for_links'] ) {
		add_submit_error( _('no tienes el mínimo de karma para enviar una nueva historia'));
		return false;
	}


	// Don't allow to send a link by a clone
	$hours = intval($globals['user_links_clon_interval']);
	$clones = $current_user->get_clones($hours+1);
	if ($hours > 0 && $clones) {
		$l = implode(',', $clones);
		$c = (int) $db->get_var("select count(*) from links where link_status!='published' and link_date > date_sub(now(), interval $hours hour) and link_author in ($l)");
		if ($c > 0) {
			add_submit_error( _('ya se envió con otro usuario «clon» en las últimas horas'). ", "._('disculpa las molestias'));
			syslog(LOG_NOTICE, "Meneame, clon submit ($current_user->user_login): $_POST[url]");
			return false;
		}
	}

	// Check the number of links sent by a user
	$queued_24_hours = (int) $db->get_var("select count(*) from links where link_status!='published' and link_date > date_sub(now(), interval 24 hour) and link_author=$current_user->user_id");

	if ($globals['limit_user_24_hours'] && $queued_24_hours > $globals['limit_user_24_hours']) {
		add_submit_error( _('debes esperar, tienes demasiados envíos en cola de las últimas 24 horas'). " ($queued_24_hours), "._('disculpa las molestias') );
		syslog(LOG_NOTICE, "Meneame, too many queued in 24 hours ($current_user->user_login): $_POST[url]");
		return false;
	}

	// check the URL is OK and that it resolves
	$url_components = @parse_url($url);
	if (!$url_components || ! $url_components['host'] || gethostbyname($url_components['host']) == $url_components['host']) {
		add_submit_error( _('URL o nombre de servidor erróneo'),
			_('el nombre del servidor es incorrecto o éste tiene problemas para resolver el nombre'));
		syslog(LOG_NOTICE, "Meneame, hostname error ($current_user->user_login): $url");
		return false;
	}

	$enqueued_last_minutes = (int) $db->get_var("select count(*) from links where link_status='queued' and link_date > date_sub(now(), interval 3 minute)");
	if ($current_user->user_karma > $globals['limit_3_minutes_karma']) $enqueued_limit = $globals['limit_3_minutes'] * 1.5;
	else $enqueued_limit = $globals['limit_3_minutes'];

	if ($enqueued_last_minutes > $enqueued_limit) {
		//echo '<p class="error"><strong>'._('exceso de envíos').':</strong></p>';
		//echo '<p>'._('se han enviado demasiadas historias en los últimos 3 minutos'). " ($enqueued_last_minutes > $enqueued_limit), "._('disculpa las molestias'). ' </p>';
		//echo '</div>'. "\n";
		add_submit_error( _('exceso de envíos'),
			_('se han enviado demasiadas historias en los últimos 3 minutos'). " ($enqueued_last_minutes > $enqueued_limit), "._('disculpa las molestias'));
		syslog(LOG_NOTICE, "Meneame, too many queued ($current_user->user_login): $_POST[url]");
		return false;
	}

	// Check the user does not have too many drafts
	$minutes = intval($globals['draft_time'] / 60) + 10;
	$drafts = (int) $db->get_var("select count(*) from links where link_author=$current_user->user_id  and link_date > date_sub(now(), interval $minutes minute) and link_status='discard' and link_votes = 0");
	if ($drafts > $globals['draft_limit']) {
		add_submit_error( _('demasiados borradores'),
			_('has hecho demasiados intentos, debes esperar o continuar con ellos desde la'). ' <a href="shakeit.php?meta=_discarded">'. _('cola de descartadas').'</a></p>');
		syslog(LOG_NOTICE, "Meneame, too many drafts ($current_user->user_login): $_POST[url]");
		return false;
	}
	// Delete dangling drafts
	if ($drafts > 0) {
		$db->query("delete from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 30 minute) and link_date < date_sub(now(), interval 10 minute) and link_status='discard' and link_votes = 0");
	}


	// Check for banned IPs
	if(($ban = check_ban($globals['user_ip'], 'ip', true)) || ($ban = check_ban_proxy())) {
		if ($ban['expire'] > 0) {
			$expires = _('caduca').': '.get_date_time($ban['expire']);
		} else $expires = '';
		add_submit_error( _('dirección IP no permitida para enviar'), $expires);
		syslog(LOG_NOTICE, "Meneame, banned IP ".$globals['user_ip']." ($current_user->user_login): $url");
		return false;
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
		if ($sents == 0) {
			// If is a new user, requires more votes, to avoid spam
			$min_votes = $globals['min_user_votes'];
		} else {
			$min_votes = min(4, intval($total_links/20)) * $user_links;
		}
		if (!$current_user->admin && $user_votes < $min_votes) {
			$needed = $min_votes - $user_votes;
			if ($new_user) {
				add_submit_error( _('¿es la primera vez que envías una historia?'),
					_('necesitas como mínimo'). " $needed " . _('votos'));
			} else {
				add_submit_error( _('no tienes el mínimo de votos necesarios para enviar una nueva historia'),
					_('necesitas votar como mínimo a'). " $needed " . _('envíos'));
			}
			add_submit_error( _('no votes de forma apresurada, penaliza el karma'),
				'<a href="'.$globals['base_url'].'shakeit.php" target="_blank">'._('haz clic aquí para ir a votar').'</a>');
			return false;
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
		add_submit_error( _('debes esperar, ya se enviaron varias con el mismo usuario o dirección IP'));
		return false;
	}

	// avoid users sending continuous "rubbish" or "propaganda", specially new users
	// it takes in account the number of positive votes in the last six hours
	if ($same_user > 1 && $current_user->user_karma < $globals['karma_propaganda']) {
		$positives_received = $db->get_var("select sum(link_votes) from links where link_date > date_sub(now(), interval $user_links_interval hour) and link_author = $current_user->user_id");
		$negatives_received = $db->get_var("select sum(link_negatives) from links where link_date > date_sub(now(), interval $user_links_interval hour) and link_author = $current_user->user_id");
		if ($negatives_received > 10 && $negatives_received > $positives_received * 1.5) {
			add_submit_error( _('debes esperar, has tenido demasiados votos negativos en tus últimos envíos'));
			return false;
		}
	}

	$link=new Link;
	$link->url = $url;
	$link->is_new = true; // Disable several options in the editing form

	$edit = false;

	if(report_duplicated($url)) return true; // Don't output error messages


	if(!$link->check_url($url, true, true) || !$link->get($url)) {
		$e = _('URL erróneo o no permitido') . ': ';
		if ($link->ban && $link->ban['match']) {
			$e .= $link->ban['match'];
		} else {
			$e .= $link->url;
		}
		add_submit_error( $e, _('Razón') . ': '. $link->ban['comment']);

		if ($link->ban['expire'] > 0) {
			add_submit_error( $e, _('caduca').': '. get_date_time($link->ban['expire']));
		}
		return false;
	}

	// If the URL has changed, check again is not dupe
	if($link->url != $url && report_duplicated($link->url)) return;

	$link->randkey = intval($_POST['randkey']);
	if(!$link->valid) {
		//echo '<p class="error"><strong>'._('error leyendo el url').':</strong> '.htmlspecialchars($url).'</p>';
		$e = _('error leyendo el url').': '. htmlspecialchars($url);
		// Dont allow new users with low karma to post wrong URLs
		if ($current_user->user_karma < 8 && $current_user->user_level == 'normal') {
			add_submit_error( $e, _('URL inválido, incompleto o no permitido. Está fuera de línea, o tiene mecanismos antibots.'));
			return false;
		}
		add_submit_error( $e, _('no es válido, está fuera de línea, o tiene mecanismos antibots. <strong>Continúa</strong>, pero asegúrate que sea correcto'));
	}

	$link->status='discard';
	$link->author=$current_user->user_id;

	if (!$link->pingback()) {
		$link->trackback();
	}
	$link->trackback=htmlspecialchars($link->trackback);


	$link->create_blog_entry();
	$blog = new Blog;
	$blog->id = $link->blog;
	$blog->read();

	$blog_url_components = @parse_url($blog->url);
	$blog_url = $blog_url_components['host'].$blog_url_components['path'];
	// Now we check again against the blog table
	// it's done because there could be banned blogs like http://lacotelera.com/something
	if(($ban = check_ban($blog->url, 'hostname', false, true))) {
		$e = _('URL inválido').': '.htmlspecialchars($url);
		add_submit_error( $e, _('el sitio').' '.$ban['match'].' '. _('está deshabilitado'). ' ('. $ban['comment'].')');
		if ($ban['expire'] > 0) {
			add_submit_error( $e, _('caduca').': '.get_date_time($ban['expire']));
		}
		syslog(LOG_NOTICE, "Meneame, banned site ($current_user->user_login): $blog->url <- $_POST[url]");
		return false;
	}


	// check for users spamming several sites and networks
	// it does not allow a low "entropy"
	if ($sents > 30) {
		$ratio = (float) $db->get_var("select count(distinct link_blog)/count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 60 day)");
		$threshold = 1/log($sents, 2);
		if ($ratio <  $threshold ) {
			if ($db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 60 day) and link_blog = $blog->id") > 2) {
				syslog(LOG_NOTICE, "Meneame, forbidden due to low entropy: $ratio <  $threshold  ($current_user->user_login): $link->url");
				add_submit_error( _('ya has enviado demasiados enlaces a los mismos sitios'), _('varía las fuentes, podría ser considerado spam'));
				return false;
			}
		}
	}

	// Check the user does not send too many images or vídeos
	// they think this is a fotolog
	if ($sents > 5 && ($link->content_type == 'image' || $link->content_type == 'video')) {
		$image_links = intval($db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 60  day) and link_content_type in ('image', 'video')"));
		if ($image_links > $sents * 0.7) {
			syslog(LOG_NOTICE, "Meneame, forbidden due to too many images or video sent by user ($current_user->user_login): $link->url");
			add_submit_error( _('ya has enviado demasiadas imágenes o vídeos'));
			return false;
		}
	}

	// Avoid users sending too many links to the same site in last hours
	$hours = 24;
	$same_blog = $db->get_var("select count(*) from links where link_date > date_sub(now(), interval $hours hour) and link_author=$current_user->user_id and link_blog=$link->blog and link_votes > 0");
	if ($same_blog >= $globals['limit_same_site_24_hours']) {
		syslog(LOG_NOTICE, "Meneame, forbidden due to too many links to the same site in last $hours hours ($current_user->user_login): $link->url");
		add_submit_error( _('demasiados enlaces al mismo sitio en las últimas horas'));
		return false;
	}

	// avoid auto-promotion (autobombo)
	$minutes = 30;
	$same_blog = $db->get_var("select count(*) from links where link_date > date_sub(now(), interval $minutes minute) and link_author=$current_user->user_id and link_blog=$link->blog and link_votes > 0");
	if ($same_blog > 0 && $current_user->user_karma < 12) {
		syslog(LOG_NOTICE, "Meneame, forbidden due to short period between links to same site ($current_user->user_login): $link->url");
		add_submit_error( _('ya has enviado un enlace al mismo sitio hace poco tiempo'),
			_('debes esperar'). " $minutes " . _('minutos entre cada envío al mismo sitio.') . ', ' . '<a href="'.$globals['base_url'].'faq-'.$dblang.'.php">'._('lee el FAQ').'</a>');
		return false;
	}

	// Avoid spam (autobombo), count links in last two months
	$same_blog = $db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > date_sub(now(), interval 60 day) and link_blog=$link->blog");

	$check_history =  $sents > 3 && $same_blog > 0 && ($ratio = $same_blog/$sents) > 0.5;
	if ($check_history) {
		$e = _('has enviado demasiados enlaces a')." $blog->url";
		if ($sents > 5 && $ratio > 0.75) {
			add_submit_error( $e, _('has superado los límites de envíos de este sitio'));
			// don't allow to continue
			syslog(LOG_NOTICE, "Meneame, warn, high ratio, process interrumped ($current_user->user_login): $link->url");
			return false;
		} else {
			add_submit_error( $e,
				_('continúa, pero ten en cuenta podría recibir votos negativos').', '. '<a href="'.$globals['base_url'].$globals['legal'].'">'._('condiciones de uso').'</a>');
			syslog(LOG_NOTICE, "Meneame, warn, high ratio, continue ($current_user->user_login): $link->url");
		}
	}



	$links_12hs = $db->get_var("select count(*) from links where link_date > date_sub(now(), interval 12 hour)");

	// check there is no an "overflow" from the same site
	$site_links = intval($db->get_var("select count(*) from links where link_date > date_sub(now(), interval 12 hour) and link_blog=$link->blog and link_status in ('queued')"));
	if ($site_links > 10 && $site_links > $links_12hs * 0.05) { // Only 5% from the same site
		syslog(LOG_NOTICE, "Meneame, forbidden due to overflow to the same site ($current_user->user_login): $link->url");
		add_submit_error( _('ya se han enviado demasiadas artículos del mismo sitio, espera unos minutos por favor'),
			_('total en 12 horas').": $site_links , ". _('el máximo actual es'). ': ' . intval($links_12hs * 0.05));
		return false;
	}

	// check there is no an "overflow" of images
	if ($link->content_type == 'image' || $link->content_type == 'video') {
		$image_links = intval($db->get_var("select count(*) from links where link_date > date_sub(now(), interval 12 hour) and link_content_type in ('image', 'video')"));
		if ($image_links > 5 && $image_links > $links_12hs * 0.08) { // Only 8% images and videos
			syslog(LOG_NOTICE, "Meneame, forbidden due to overflow images ($current_user->user_login): $link->url");
			add_submit_error( _('ya se han enviado demasiadas imágenes o vídeos, espera unos minutos por favor'),
				_('total en 12 horas').": $image_links , ". _('el máximo actual es'). ': ' . intval($links_12hs * 0.05));
			return false;
		}
	}

	if(($ban = check_ban($link->url, 'punished_hostname', false, true))) {
		add_submit_error( _('Aviso').' '.$ban['match']. ': <em>'.$ban['comment'].'</em>',
			_('mejor enviar el enlace a la fuente original, si no, será penalizado'));
	}


	// Now stores new draft
	$link->ip = $globals['user_ip'];
	$link->sent_date = $link->date=time();

	$link->key = $_POST['key'];
	$link->randkey = $_POST['randkey'];
	$link->store();

	$link->url_title = mb_substr($link->url_title, 0, 200);
	if (mb_strlen($link->url_description) > 40) {
		$link->content = $link->url_description;
	}
	$link->chars_left = 550 - mb_strlen(html_entity_decode($link->content, ENT_COMPAT, 'UTF-8'), 'UTF-8');

	Haanga::Load('link/submit1.html', compact('link', 'errors'));
	return true;
}


function do_submit2() {
	global $db, $dblang, $globals, $errors;

	$link=new Link;
	$link->id=$link_id = intval($_POST['id']);
	$link->read();

	if(report_duplicated($link->url)) return true;
	$link->read_content_type_buttons($_POST['type']);

	// Check if the title contains [IMG], [IMGs], (IMG)... and mark it as image
	if (preg_match('/[\(\[](IMG|PICT*)s*[\)\]]/i', $_POST['title'])) {
		$_POST['title'] = preg_replace('/[\(\[](IMG|PICT*)s*[\)\]]/i', ' ', $_POST['title']);
		$link->content_type = 'image';
	} elseif (preg_match('/[\(\[](VID|VIDEO|Vídeo*)s*[\)\]]/i', $_POST['title'])) {
		$_POST['title'] = preg_replace('/[\(\[](VID|VIDEO|Vídeo*)s*[\)\]]/i', ' ', $_POST['title']);
		$link->content_type = 'video';
	}

	$link->category=intval($_POST['category']);
	$link->title = clean_text(preg_replace('/(\w) *[;.,] *$/', "$1", $_POST['title']), 40);  // It also deletes punctuaction signs at the end
	$link->tags = tags_normalize_string($_POST['tags']);
	$link->key = $_POST['key'];
	$link->content = clean_text_with_tags($_POST['bodytext']);
	if (link_errors($link)) {
		// Show the edit form again
		$link->is_new = true; // Disable several options in the editing form
		Haanga::Load('link/submit1.html', compact('link', 'errors'));
		return true;
	}

	$link->store();
	tags_insert_string($link->id, $dblang, $link->tags);
	$link->read();
	$link->randkey = $_POST['randkey'];

	$related = $link->get_related(6);

	Haanga::Load('link/submit2.html', compact('link', 'errors', 'related'));
	return true;
}

function do_submit3() {
	global $db, $current_user;

	$link=new Link;

	$link->id=$link_id = intval($_POST['id']);

	if(!check_link_key() || !$link->read() || link_errors($link)) die;

	// Check it is not in the queue already
	if (Link::duplicates($link->url)) {
		// Write headers, they were not printed yet
		do_header(_("enviar historia"), "post");
		echo '<div id="singlewrap">' . "\n";
		report_duplicated($link->url);
		return;
	}

	// Check this one was not already queued
	if($link->votes == 0 && $link->status != 'queued') {
		$link->enqueue();
	}

	header('Location: '. $link->get_permalink());
	die;

}

function check_link_key() {
	global $site_key, $current_user;
	return $_POST['key'] == md5($_POST['randkey'].$current_user->user_id.$current_user->user_email.$site_key.get_server_name());
}

function link_errors($link) {
	$error = false;
	// Errors
	if(! check_link_key() || intval($_POST['randkey']) != $link->randkey) {
		add_submit_error(_("clave incorrecta"));
		$error = true;
	}
	if($link->status != 'discard') {
		add_submit_error(_("la historia ya está en cola").": $link->status");
		$error = true;
	}
	if(strlen($link->title) < 8  || strlen($link->content) < 30 ) {
		add_submit_error(_("título o texto incompletos"));
		$error = true;
	}
	if(get_uppercase_ratio($link->title) > 0.25  || get_uppercase_ratio($link->content) > 0.25 ) {
		add_submit_error(_("demasiadas mayúsculas en el título o texto"));
		$error = true;
	}
	if(mb_strlen(html_entity_decode($link->title, ENT_COMPAT, 'UTF-8'), 'UTF-8') > 120  || mb_strlen(html_entity_decode($link->content, ENT_COMPAT, 'UTF-8'), 'UTF-8') > 550 ) {
		add_submit_error(_("título o texto demasiado largos"));
		$error = true;
	}
	if(strlen($link->tags) < 3 ) {
		add_submit_error(_("no has puesto etiquetas"));
		$error = true;
	}

	if(preg_match('/.*http:\//', $link->title)) {
		add_submit_error(_("por favor, no pongas URLs en el título, no ofrece información"));
		$error = true;
	}
	if(!$link->category > 0) {
		add_submit_error(_("categoría no seleccionada"));
		$error = true;
	}
	return $error;
}

function report_duplicated($url) {
	global $globals;

	if(($found = Link::duplicates($url))) {
		$link = new Link;
		$link->id = $found;
		$link->read();
		Haanga::Load('link/duplicated.html', compact('link'));
		return true;
	}
	return false;
}

function add_submit_error() {
	global $errors;
	if (func_num_args() < 1) return false;
	$title = func_get_arg(0);
	if (! isset($errors[$title])) {
		$errors[$title] = array();
	}
	if (func_num_args() < 2) return true;
	for ($i = 1; $i < func_num_args(); $i++) {
		array_push($errors[$title], func_get_arg($i));
	}
	return true;
}
?>
