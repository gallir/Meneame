<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called 'COPYING'.

require_once __DIR__ . '/bootstrap.php';

do_header(_('enviar historia') . ' 1/3', _('enviar historia'));

if (empty($_POST['url'])) {
    do_page_error(_('Debe especificar enlace'));
}

$url = clean_input_url(urldecode($_POST['url']));
$url = preg_replace('/#.*$/', '', $url);

if (!preg_match('#^http(s)?://#', $url)) {
    $url = 'http://'.$url;
}

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    do_page_error(_('La URL enviada no parece válida'));
}

$url_components = parse_url($url);

if (empty($url_components) || empty($url_components['host']) || gethostbyname($url_components['host']) === $url_components['host']) {
    do_page_error(_('La URL enviada no parece válida'), '', 'Hostname error: ' . $url);
}

if (empty($_POST['randkey']) || empty($_POST['key']) || !check_security_key($_POST['key'])) {
    do_page_error(_('Clave de proceso incorrecta'));
}

if (strlen($url) > 250) {
    do_page_error(_('URL demasiado larga'), _('La longitud de la URL supera el tamaño máximo permitido (250 caracteres)'));
}

// Check the user does not have too many drafts
$minutes = intval($globals['draft_time'] / 60) + 10;
$drafts = (int) $db->get_var('
    SELECT COUNT(*)
    FROM links
    WHERE (
        link_author = "'.$current_user->user_id.'"
        AND link_date > DATE_SUB(NOW(), INTERVAL '.$minutes.' MINUTE)
        AND link_status = "discard"
        AND link_votes = 0
    );
');

if ($drafts > $globals['draft_limit']) {
    do_page_error(
        _('demasiados borradores'),
        (_('has hecho demasiados intentos, debes esperar o continuar con ellos desde la') . ' <a href="'.$globals['base_url'].'queue?meta=_discarded">'. _('Cola de descartadas').'</a>'),
        'too many drafts: ' . $url
    );
}

// Delete dangling drafts
if ($drafts > 0) {
    $db->query('
        DELETE FROM links
        WHERE (
            link_author = "'.$current_user->user_id.'"
            AND link_date > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            AND link_date < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
            AND link_status = "discard"
            AND link_votes = 0
        );
    ');
}

$warnings = array();

$anti_spam = !empty($site_properties['no_anti_spam']);
$new_user = false;

if ($anti_spam) {
    // Number of links sent by the user
    $total_sents = (int) $db->get_var('
        SELECT COUNT(*)
        FROM links
        WHERE link_author = "'.$current_user->user_id.'";
    ') - $drafts;

    if ($total_sents > 0) {
        $sents = (int) $db->get_var('
            SELECT COUNT(*)
            FROM links
            WHERE (
                link_author = "'.$current_user->user_id.'"
                AND link_date > DATE_SUB(NOW(), INTERVAL 60 day)
            );
        ') - $drafts;
    } else {
        $new_user = true;
        $sents = 0;
    }

    $register_date = $current_user->Date();

    if ($globals['now'] - $register_date < $globals['new_user_time']) {
        $new_user = true;
    }

    if ($globals['min_karma_for_links'] > 0 && $current_user->user_karma < $globals['min_karma_for_links']) {
        do_page_error(_('No tienes el mínimo de karma para enviar una nueva historia'));
    }

    // Check for banned IPs
    if (($ban = check_ban($globals['user_ip'], 'ip', true)) || ($ban = check_ban_proxy())) {
        if ($ban['expire'] > 0) {
            $expires = _('caduca').': '.get_date_time($ban['expire']);
        } else {
            $expires = '';
        }

        do_page_error(_('dirección IP no permitida para enviar'), $expires, 'banned IP '.$globals['user_ip'].': ' . $url);
    }
} // END anti_spam

// check that a new user also votes, not only sends links
// it requires $globals['min_user_votes'] votes
if ($new_user && $globals['min_user_votes'] > 0 && $current_user->user_karma < $globals['new_user_karma']) {
    $user_votes_total = (int) $db->get_var('
        SELECT COUNT(*)
        FROM votes
        WHERE (
            vote_type = "links"
            AND vote_user_id = "'.$current_user->user_id.'"
        );
    ');

    $user_votes = (int) $db->get_var('
        SELECT COUNT(*)
        FROM votes
        WHERE (
            vote_type = "links"
            AND vote_date > DATE_SUB(NOW(), INTERVAL 72 HOUR)
            AND vote_user_id = "'.$current_user->user_id.'"
        );
    ');

    $user_links = 1 + $db->get_var('
        SELECT COUNT(*)
        FROM links
        WHERE (
            link_author = "'.$current_user->user_id.'"
            AND link_date > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND link_status != "discard"
        );
    ');

    $total_links = (int) $db->get_var('
        SELECT COUNT(*)
        FROM links
        WHERE (
            link_date > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND link_status = "queued"
        );
    ');

    if ($sents == 0) {
        // If is a new user, requires more votes, to avoid spam
        $min_votes = $globals['min_user_votes'];
    } else {
        $min_votes = min(4, intval($total_links/20)) * $user_links;
    }

    if (!$current_user->admin && $user_votes < $min_votes) {
        $needed = $min_votes - $user_votes;

        if ($new_user) {
            do_page_error(_('¿Es la primera vez que envías una historia?'), _('Necesitas como mínimo'). ' ' . $needed . ' ' . _('votos'));
        } else {
            do_page_error(_('No tienes el mínimo de votos necesarios para enviar una nueva historia'), _('Necesitas votar como mínimo a'). ' ' . $needed . ' ' . _('envíos'));
        }

        do_page_error(_('No votes de forma apresurada, penaliza el karma'), '<a href="'.$globals['base_url'].'queue" target="_blank">'._('Haz clic aquí para ir a votar').'</a>');
    }
}

if ($anti_spam) {
    // Don't allow to send a link by a clone
    $hours = intval($globals['user_links_clon_interval']);
    $clones = $current_user->get_clones($hours + 1);

    if ($hours > 0 && $clones) {
        $count = (int) $db->get_var('
            SELECT COUNT(*)
            FROM links
            WHERE (
                link_status != "published"
                AND link_date > DATE_SUB(NOW(), INTERVAL '.$hours.' HOUR)
                AND link_author IN ('.implode(', ', $clones).')
            );
        ');

        if ($count) {
            do_page_error(_('Ya se envió con otro usuario «clon» en las últimas horas'). ', '._('disculpa las molestias'), '' , 'Clon submit: ' . $url);
        }
    }

    // Check the number of links sent by a user
    $queued_24_hours = (int) $db->get_var('
        SELECT COUNT(*)
        FROM links, subs, sub_statuses
        WHERE (
            status != "published"
            AND `date` > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND link_author = "'.$current_user->user_id.'"
            AND sub_statuses.link = link_id
            AND subs.id = sub_statuses.id
            AND sub_statuses.origen = sub_statuses.id
            AND subs.parent = 0
            AND subs.owner = 0
        );
    ');

    if ($globals['limit_user_24_hours'] && $queued_24_hours > $globals['limit_user_24_hours']) {
        do_page_error(_('Debes esperar, tienes demasiados envíos en cola de las últimas 24 horas'). ' ('.$queued_24_hours.'), '._('disculpa las molestias'), '', 'Too many queued in 24 hours: ' . $url);
    }

    // Check the number of links sent by the user in the last MINUTEs
    $enqueued_last_minutes = (int) $db->get_var('
        SELECT COUNT(*)
        FROM links
        WHERE (
            link_status = "queued"
            AND link_date > DATE_SUB(NOW(), INTERVAL 3 MINUTE)
            AND link_author = "'.$current_user->user_id.'"
        );
    ');

    if ($current_user->user_karma > $globals['limit_3_minutes_karma']) {
        $enqueued_limit = $globals['limit_3_minutes'] * 1.5;
    } else {
        $enqueued_limit = $globals['limit_3_minutes'];
    }

    if ($enqueued_last_minutes > $enqueued_limit) {
        do_page_error(_('Exceso de envíos'), _('se han enviado demasiadas historias en los últimos 3 minutos'). ' ('.$enqueued_last_minutes.' > '.$enqueued_limit.'), '._('disculpa las molestias'), 'Too many queued: ' . $url);
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

    $same_user = (int) $db->get_var('
        SELECT COUNT(*)
        FROM links
        WHERE (
            link_date > DATE_SUB(NOW(), INTERVAL '.$user_links_interval.' HOUR)
            AND link_author = "'.$current_user->user_id.'"
        );
    ') - $drafts;

    $same_ip = (int) $db->get_var('
        SELECT COUNT(*)
        FROM links
        WHERE (
            link_date > DATE_SUB(NOW(), INTERVAL '.$user_links_interval.' HOUR)
            AND link_ip = "'.$globals['user_ip'].'"
        );
    ') - $drafts;

    if ($same_user > $user_links_limit || $same_ip > $user_links_limit) {
        do_page_error(_('Debes esperar, ya se enviaron varias con el mismo usuario o dirección IP'));
    }

    // avoid users sending continuous 'rubbish' or 'propaganda', specially new users
    // it takes in account the number of positive votes in the last six hours
    if ($same_user > 1 && $current_user->user_karma < $globals['karma_propaganda']) {
        $positives_received = $db->get_var('
            SELECT SUM(link_votes)
            FROM links
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL '.$user_links_interval.' HOUR)
                AND link_author = "'.$current_user->user_id.'"
            );
        ');

        $negatives_received = $db->get_var('
            SELECT SUM(link_negatives)
            FROM links
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL '.$user_links_interval.' HOUR)
                AND link_author = "'.$current_user->user_id.'"
            );
        ');

        if ($negatives_received > 10 && $negatives_received > $positives_received * 1.5) {
            do_page_error(_('Debes esperar, has tenido demasiados votos negativos en tus últimos envíos'));
        }
    }
} // END anti_spam

$link = new Link;
$link->url = $url;
$link->is_new = true; // Disable several options in the editing form
$link->status = 'discard';
$link->author = $current_user->user_id;

if (!empty($site_properties['rules']) && $site_properties['no_link'] == 2) {
    $link->rules = LCPBase::html($site_properties['rules']);
}

$edit = false;

if (!empty($link->url)) {
    report_duplicated($url);

    if (!$link->check_url($url, $anti_spam, true) || !$link->get($url, null, $anti_spam)) {
        $e = _('URL erróneo o no permitido') . ': ';

        if ($link->ban && $link->ban['match']) {
            $e .= $link->ban['match'];
        } else {
            $e .= $link->url;
        }

        $info = _('Razón') . ': '. $link->ban['comment'];

        if ($link->ban['expire'] > 0) {
            $info .= ', '._('caduca').': '. get_date_time($link->ban['expire']);
        }

        do_page_error($e, $info);
    }

    // If the URL has changed, check again is not dupe
    if ($link->url !== $url) {
        report_duplicated($link->url);
    }

    $link->randkey = intval($_POST['randkey']);

    if (!$link->valid) {
        $e = _('Error leyendo la URL').': '. htmlspecialchars($url);

        // Dont allow new users with low karma to post wrong URLs
        if ($current_user->user_karma < 7 && $current_user->user_level === 'normal' && ! $site->owner) {
            do_page_error($e, _('URL inválida, incompleta o no permitida. Está fuera de línea, o tiene mecanismos antibots.'));
        }
    }

    if (!$link->pingback()) {
        $link->trackback();
    }

    $link->trackback = htmlspecialchars($link->trackback);

    $link->create_blog_entry();

    $blog = new Blog;
    $blog->id = $link->blog;
    $blog->read();

    $blog_url_components = @parse_url($blog->url);
    $blog_url = $blog_url_components['host'].$blog_url_components['path'];
}

if ($anti_spam) {
    // Now we check again against the blog table
    // it's done because there could be banned blogs like http://lacotelera.com/something
    if (($ban = check_ban($blog->url, 'hostname', false, true))) {
        $info = _('El sitio').' '.$ban['match'].' '. _('está deshabilitado'). ' ('. $ban['comment'].')';

        if ($ban['expire'] > 0) {
            $info .= ', '._('caduca').': '.get_date_time($ban['expire']);
        }

        do_page_error(_('URL inválida').': '.htmlspecialchars($url), $info, 'banned site: '.$blog->url.' <- ' . $url);
    }

    // check for users spamming several sites AND networks
    // it does not allow a low 'entropy'
    if ($sents > 30) {
        $ratio = (float) $db->get_var('
            SELECT COUNT(distinct link_blog) / COUNT(*)
            FROM links
            WHERE (
                link_author = "'.$current_user->user_id.'"
                AND link_date > DATE_SUB(NOW(), INTERVAL 60 DAY)
            );
        ');

        $threshold = 1 / log($sents, 2);

        if ($ratio <  $threshold) {
            $count = (int)$db->get_var('
                SELECT COUNT(*)
                FROM links
                WHERE (
                    link_author = "'.$current_user->user_id.'"
                    AND link_date > DATE_SUB(NOW(), INTERVAL 60 DAY)
                    AND link_blog = "'.$blog->id.'"
                );
            ');

            if ($count > 2) {
                do_page_error(
                    _('Ya has enviado demasiados enlaces a los mismos sitios'),
                    _('Varía las fuentes, podría ser considerado spam'),
                    'forbidden due to low entropy: '.$ratio.' < '.$threshold.': '.$link->url
                );
            }
        }
    }

    // Check the user does not send too many images or vídeos
    // they think this is a fotolog
    if ($sents > 5 && ($link->content_type === 'image' || $link->content_type === 'video')) {
        $image_links = (int)$db->get_var('
            SELECT COUNT(*)
            FROM links, subs, sub_statuses
            WHERE (
                link_author = "'.$current_user->user_id.'"
                AND link_date > DATE_SUB(NOW(), INTERVAL 60 DAY)
                AND link_content_type IN ("image", "video")
                AND sub_statuses.link = link_id
                AND subs.id = sub_statuses.id
                AND sub_statuses.origen = sub_statuses.id
                AND subs.parent = 0
                AND subs.owner = 0
            );
        ');

        if ($image_links > $sents * 0.8) {
            do_page_error(_('Ya has enviado demasiadas imágenes o vídeos', '', 'forbidden due to too many images or video sent by user: '.$link->url));
        }
    }

    // Avoid users sending too many links to the same site in last hours
    $hours = 24;
    $same_blog = $db->get_var('
        SELECT COUNT(*)
        FROM links
        WHERE (
            link_date > DATE_SUB(NOW(), INTERVAL '.$hours.' HOUR)
            AND link_author = "'.$current_user->user_id.'"
            AND link_blog = "'.$link->blog.'"
            AND link_votes > 0
        );
    ');

    if ($same_blog >= $globals['limit_same_site_24_hours']) {
        do_page_error(_('Demasiados enlaces al mismo sitio en las últimas horas'), '', 'forbidden due to too many links to the same site in last '.$hours.' hours: '.$link->url);
    }

    // avoid auto-promotion (autobombo)
    $minutes = 30;
    $same_blog = $db->get_var('SELECT COUNT(*) FROM links WHERE link_date > DATE_SUB(NOW(), INTERVAL $minutes MINUTE) AND link_author = "'.$current_user->user_id.'" AND link_blog=$link->blog AND link_votes > 0');

    if ($same_blog > 0 && $current_user->user_karma < 12) {
        do_page_error(
            _('Ya has enviado un enlace al mismo sitio hace poco tiempo'),
            _('Debes esperar'). ' '.$minutes.' ' . _('minutos entre cada envío al mismo sitio.') . ', ' . '<a href="'.$globals['base_url_general'].'faq-'.$dblang.'.php">'._('lee el FAQ').'</a>',
            'forbidden due to short period between links to same site: '.$link->url
        );
    }

    // Avoid spam (autobombo), count links in last two months
    $same_blog = (int)$db->get_var('
        SELECT COUNT(*)
        FROM links
        WHERE (
            link_author = "'.$current_user->user_id.'"
            AND link_date > DATE_SUB(NOW(), INTERVAL 60 DAY)
            AND link_blog = "'.$link->blog.'"
        );
    ');

    $check_history = $sents > 3 && $same_blog > 0 && ($ratio = $same_blog/$sents) > 0.5;

    if ($check_history) {
        $e = _('Has enviado demasiados enlaces a').' '.$blog->url;

        if ($sents > 5 && $ratio > 0.75) {
            do_page_error($e, _('has superado los límites de envíos de este sitio'), 'warn, high ratio, process interrumped: '.$link->url);
        }

        $warnings[] = array(
            'title' => $e,
            'info' => _('Continúa, pero ten en cuenta podría recibir votos negativos').', '. '<a href="'.$globals['base_url'].$globals['legal'].'">'._('condiciones de uso').'</a>'
        );
    }

    if (! $site->owner) { // Only for the main subs
        $links_12hs = $db->get_var('
            SELECT COUNT(*)
            FROM links, subs, sub_statuses
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL 12 HOUR)
                AND sub_statuses.link = link_id
                AND subs.id = sub_statuses.id
                AND sub_statuses.origen = sub_statuses.id
                AND subs.parent = 0
                AND subs.owner = 0
            );
        ');

        // check there is no an 'overflow' FROM the same site
        $site_links = (int)$db->get_var('
            SELECT COUNT(*)
            FROM links, subs, sub_statuses
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL 12 HOUR)
                AND link_blog = "'.$link->blog.'"
                AND link_status = "queued"
                AND sub_statuses.link = link_id
                AND subs.id = sub_statuses.id
                AND sub_statuses.origen = sub_statuses.id
                AND subs.parent = 0
                AND subs.owner = 0
            );
        ');

        if ($site_links > 10 && $site_links > $links_12hs * 0.05) { // Only 5% FROM the same site
            do_page_error(
                _('Hay en cola demasiados envíos del mismo sitio, espera unos minutos por favor'),
                _('Total en 12 horas').': '.$site_links.' , '. _('el máximo actual es'). ': ' . intval($links_12hs * 0.05),
                'forbidden due to overflow to the same site: '.$link->url
            );
        }

        // check there is no an 'overflow' of images
        if ($link->content_type === 'image' || $link->content_type === 'video') {
            $image_links = (int)$db->get_var('
                SELECT COUNT(*)
                FROM links, subs, sub_statuses
                WHERE (
                    link_date > DATE_SUB(NOW(), INTERVAL 12 HOUR)
                    AND link_content_type IN ("image", "video")
                    AND sub_statuses.link = link_id
                    AND subs.id = sub_statuses.id
                    AND sub_statuses.origen = sub_statuses.id
                    AND subs.parent = 0
                    AND subs.owner = 0
                );
            ');

            if ($image_links > 5 && $image_links > $links_12hs * 0.15) { // Only 15% images AND videos
                do_page_error(
                    _('Ya se han enviado demasiadas imágenes o vídeos, espera unos minutos por favor'),
                    _('Total en 12 horas').': '.$image_links,' , '. _('el máximo actual es'). ': ' . intval($links_12hs * 0.05),
                    'forbidden due to overflow images: '.$link->url
                );
            }
        }

        if (($ban = check_ban($link->url, 'punished_hostname', false, true))) {
            $warnings[] = array(
                'title' => _('Aviso').' '.$ban['match']. ': '.$ban['comment'],
                'info' => _('Mejor enviar el enlace a la fuente original')
            );
        }
    }
} // END anti_spam

// Now stores new draft
$link->sent_date = $link->date = time();
$link->store();

$link->url_title = mb_substr($link->url_title, 0, 200);

if (mb_strlen($link->url_description) > 40) {
    $link->content = $link->url_description;
}

$link->chars_left = $site_properties['intro_max_len'] - mb_strlen(html_entity_decode($link->content, ENT_COMPAT, 'UTF-8'), 'UTF-8');

if (empty($link->url)) {
    $link->poll = new Poll;

    if ($link->id) {
        $link->poll->read('link_id', $link->id);
    }
}

Haanga::Load('story/submit/step2.html', compact(
    'globals', 'link', 'site_properties', 'warnings'
));

do_footer();
