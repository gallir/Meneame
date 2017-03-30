<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once mnminclude . 'favorites.php';

class LinkValidator
{
    public $link;
    public $error;
    public $warning;

    public $userDrafts;
    public $userVotes;
    public $userLinks;
    public $userSent;
    public $userSentRecent;
    public $linksQueded;

    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    public function fixUrl()
    {
        $this->link->url = preg_replace('/#.*$/', '', clean_input_url(urldecode($this->link->url)));

        if (!preg_match('#^http(s)?://#', $this->link->url)) {
            $this->link->url = 'http://' . $this->link->url;
        }

        if (!filter_var($this->link->url, FILTER_VALIDATE_URL)) {
            $this->setError(_('La URL enviada no parece válida'));
        }

        return $this;
    }

    public function checkUrl()
    {
        $components = parse_url($this->link->url);

        if (empty($components) || empty($components['host']) || gethostbyname($components['host']) === $components['host']) {
            $this->setError(_('La URL enviada no parece válida'), '', 'Hostname error: ' . $this->link->url);
        }

        if (strlen($this->link->url) > 250) {
            $this->setError(_('URL demasiado larga'), _('La longitud de la URL supera el tamaño máximo permitido (250 caracteres)'));
        }

        return $this;
    }

    public function checkKey()
    {
        if (empty($_POST['randkey']) || empty($_POST['key']) || !check_security_key($_POST['key'])) {
            $this->setError(_('Clave de proceso incorrecta'));
        }

        if ($link->randkey && ($link->randkey != $_POST['randkey'])) {
            $this->setError(_('Clave de proceso incorrecta'));
        }

        return $this;
    }

    public function checkSiteSend()
    {
        if ($this->link->sub_id > 0 && !SitesMgr::can_send($this->link->sub_id)) {
            $this->setError(__('Los envíos en %s están deshabilitados.', $this->link->sub_name));
        }

        return $this;
    }

    public function checkBasicData()
    {
        if (empty($this->link->title)) {
            $this->setError(_('El envío no dispone de un título.'));
        }

        if (empty($this->link->tags)) {
            $this->setError(_('El envío no dispone de tags.'));
        }

        if (empty($this->link->sub_id)) {
            $this->setError(_('El envío no dispone de un sub.'));
        }

        return $this;
    }

    public function checkDuplicates()
    {
        global $globals;

        if (!($found = Link::duplicates($this->link->url))) {
            return $this;
        }

        $link = new Link;
        $link->id = $found;
        $link->read();

        $this->setError(
            _('El envío es duplicado'),
            __('Puedes ver el actual <a href="%s">aquí</a>', $link->get_permalink())
        );
    }

    public function checkRemote($anti_spam)
    {
        global $current_user, $site;

        if (!$this->link->get($this->link->url, null, $anti_spam)) {
            $this->setError(_('Enlace erróneo o no permitido'));
        }

        if ($this->link->valid) {
            return $this;
        }

        $e = _('Error leyendo la URL') . ': ' . htmlspecialchars($this->link->url);

        if ($current_user->user_karma < 7 && $current_user->user_level === 'normal' && !$site->owner) {
            $this->setError($e, _('URL inválida, incompleta o no permitida. Está fuera de línea, o tiene mecanismos antibots.'));
        }

        $this->setWarning($e, _('No es válida, está fuera de línea, o tiene mecanismos antibots. <strong>Continúa</strong>, pero asegúrate que sea correcto.'));

        return $this;
    }

    public function checkLocal()
    {
        $components = parse_url($this->link->url);
        $quoted = preg_quote(get_server_name(), '/');

        if (preg_match('/^' . $quoted . '$/', $components['host'])) {
            $this->setError(_('El servidor es local'), '', 'Server name is local name: ' . $this->link->url);
        }

        return $this;
    }

    public function checkBan($url = null)
    {
        require_once mnminclude . 'ban.php';

        $url = $url ?: $this->link->url;

        if (!($ban = check_ban($url, 'hostname', false, true))) {
            return $this;
        }

        $info = _('Razón') . ': ' . $ban['comment'];

        if ($ban['expire'] > 0) {
            $info .= ', ' . _('caduca') . ': ' . get_date_time($ban['expire']);
        }

        $this->setError(_('El servidor tiene aplicado un BAN'), $info, 'Server name is banned: ' . $url);
    }

    public function checkBanPunished($url = null)
    {
        require_once mnminclude . 'ban.php';

        $url = $url ?: $this->link->url;

        if (!($ban = check_ban($url, 'punished_hostname', false, true))) {
            return $this;
        }

        $info = _('Mejor enviar el enlace a la fuente original');
        $info .= '.' . _('Razón') . ': ' . $ban['comment'];

        if ($ban['expire'] > 0) {
            $info .= ', ' . _('caduca') . ': ' . get_date_time($ban['expire']);
        }

        $this->setWarning(_('Aviso') . ' ' . $ban['match'], $info);
    }

    public function checkBanUser($url = null)
    {
        global $globals;

        require_once mnminclude . 'ban.php';

        if (!($ban = check_ban($globals['user_ip'], 'ip', true)) && !($ban = check_ban_proxy())) {
            return $this;
        }

        if ($ban['expire'] > 0) {
            $info = _('Caduca') . ': ' . get_date_time($ban['expire']);
        } else {
            $info = '';
        }

        $this->setError(_('Dirección IP no permitida para enviar'), $info, 'Banned IP ' . $globals['user_ip'] . ': ' . $this->link->url);
    }

    public function checkDrafts()
    {
        global $globals, $db, $current_user;

        // Check the user does not have too many drafts
        if ($this->getUserDrafts() > $globals['draft_limit']) {
            $this->setError(
                _('Demasiados borradores'),
                _('Has hecho demasiados intentos, debes esperar o continuar con ellos desde la') . ' <a href="' . $globals['base_url'] . 'queue?meta=_discarded">' . _('Cola de descartadas') . '</a>',
                'too many drafts: ' . $this->link->url
            );
        }

        $db->query('
            DELETE FROM links
            WHERE (
                link_author = "' . $current_user->user_id . '"
                AND link_date > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                AND link_date < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                AND link_status = "discard"
                AND link_votes = 0
            );
        ');

        return $this;
    }

    public function checkKarmaMin()
    {
        global $globals, $db, $current_user;

        if ($globals['min_karma_for_links'] > 0 && $current_user->user_karma < $globals['min_karma_for_links']) {
            $this->setError(_('No tienes el mínimo de karma para enviar una nueva historia'));
        }

        return $this;
    }

    public function checkVotesMin()
    {
        global $globals, $db, $current_user;

        $total = $this->getUserSent();

        if (($total === 0) || !$globals['min_user_votes'] || $current_user->user_karma >= $globals['new_user_karma']) {
            return $this;
        }

        $user_votes = $this->getUservotes();

        if ($this->getUserSentRecent() === 0) {
            $min_votes = $globals['min_user_votes'];
        } else {
            $min_votes = min(4, intval($this->getLinksQueded() / 20)) * $this->getUserLinks();
        }

        if ($current_user->admin || $user_votes >= $min_votes) {
            return $this;
        }

        $needed = $min_votes - $user_votes;

        if ($total === 0) {
            $this->setError(_('¿Es la primera vez que envías una historia?'), __('Necesitas como mínimo %s votos', $needed));
        }

        $this->setError(_('No tienes el mínimo de votos necesarios para enviar una nueva historia'), __('Necesitas votar como mínimo a %s envíos', $needed));
    }

    public function checkClones()
    {
        global $globals, $db, $current_user;

        $hours = intval($globals['user_links_clon_interval']);
        $clones = $current_user->get_clones($hours + 1);

        if ($hours <= 0 || !$clones) {
            return $this;
        }

        $count = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE (
                link_status != "published"
                AND link_date > DATE_SUB(NOW(), INTERVAL ' . $hours . ' HOUR)
                AND link_author IN (' . implode(', ', $clones) . ')
            );
        ');

        if ($count) {
            $this->setError(
                _('Ya se envió con otro usuario «clon» en las últimas horas'),
                '',
                'Clon submit: ' . $this->link->url
            );
        }

        return $this;
    }

    public function checkUserNotPulished($hours, $limit)
    {
        global $globals, $db, $current_user;

        if (empty($limit)) {
            return $this;
        }

        $queued = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links, subs, sub_statuses
            WHERE (
                status != "published"
                AND `date` > DATE_SUB(NOW(), INTERVAL ' . (int) $hours . ' HOUR)
                AND link_author = "' . $current_user->user_id . '"
                AND sub_statuses.link = link_id
                AND subs.id = sub_statuses.id
                AND sub_statuses.origen = sub_statuses.id
                AND subs.parent = 0
                AND subs.owner = 0
            );
        ');

        if ($queued > $limit) {
            $this->setError(
                __('Debes esperar, tienes demasiados envíos en cola de las últimas 24 horas (%s)', $queued),
                '',
                'Too many queued in 24 hours: ' . $this->link->url
            );
        }

        return $this;
    }

    public function checkUserQueued($minutes)
    {
        global $globals, $db, $current_user;

        $limit = $globals['limit_3_minutes'];

        if ($current_user->user_karma > $globals['limit_3_minutes_karma']) {
            $limit *= 1.5;
        }

        // Check the number of links sent by the user in the last MINUTEs
        $queued = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE (
                link_status = "queued"
                AND link_date > DATE_SUB(NOW(), INTERVAL ' . (int) $minutes . ' MINUTE)
                AND link_author = "' . $current_user->user_id . '"
            );
        ');

        if ($queued > $limit) {
            $this->setError(
                _('Exceso de envíos'),
                __('Se han enviado demasiadas historias en los últimos 3 minutos (%s > %s)', $queued, $limit),
                'Too many queued: ' . $this->link->url
            );
        }

        return $this;
    }

    public function checkUserSame()
    {
        global $globals, $db, $current_user;

        list($limit, $interval) = $this->getUserLimitInterval();

        $count = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL ' . $interval . ' HOUR)
                AND link_author = "' . $current_user->user_id . '"
            );
        ') - $this->getUserDrafts();

        if ($count > $limit) {
            $this->setError(_('Debes esperar, ya se enviaron demasiados enlaces con el mismo usuario.'));
        }

        return $this;
    }

    public function checkUserIP()
    {
        global $globals, $db, $current_user;

        list($limit, $interval) = $this->getUserLimitInterval();

        $count = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL ' . $interval . ' HOUR)
                AND link_ip = "' . $globals['user_ip'] . '"
            );
        ') - $this->getUserDrafts();

        if ($count > $limit) {
            $this->setError(_('Debes esperar, ya se enviaron varias desde esta misma IP'));
        }

        return $this;
    }

    public function checkUserNegatives()
    {
        global $globals, $db, $current_user;

        list($limit, $interval) = $this->getUserLimitInterval();

        $count = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL ' . $interval . ' HOUR)
                AND link_author = "' . $current_user->user_id . '"
            );
        ') - $this->getUserDrafts();

        if ($count <= 1 || $current_user->user_karma >= $globals['karma_propaganda']) {
            return $this;
        }

        $positives = (int) $db->get_var('
            SELECT SUM(link_votes)
            FROM links
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL ' . $interval . ' HOUR)
                AND link_author = "' . $current_user->user_id . '"
            );
        ');

        $negatives = (int) $db->get_var('
            SELECT SUM(link_negatives)
            FROM links
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL ' . $interval . ' HOUR)
                AND link_author = "' . $current_user->user_id . '"
            );
        ');

        if ($negatives > 10 && $negatives > $positives * 1.5) {
            $this->setError(_('Debes esperar, has tenido demasiados votos negativos en tus últimos envíos'));
        }

        return $this;
    }

    public function checkRatio($blog)
    {
        global $globals, $db, $current_user;

        $sents = $this->getUserSent();

        if ($sents <= 30) {
            return $this;
        }

        $ratio = (float) $db->get_var('
            SELECT COUNT(DISTINCT link_blog) / COUNT(*)
            FROM links
            WHERE (
                link_author = "' . $current_user->user_id . '"
                AND link_date > DATE_SUB(NOW(), INTERVAL 60 DAY)
            );
        ');

        $threshold = 1 / log($sents, 2);

        if ($ratio >= $threshold) {
            return $this;
        }

        $count = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE (
                link_author = "' . $current_user->user_id . '"
                AND link_date > DATE_SUB(NOW(), INTERVAL 60 DAY)
                AND link_blog = "' . $blog->id . '"
            );
        ');

        if ($count > 2) {
            $this->setError(
                _('Ya has enviado demasiados enlaces a los mismos sitios'),
                _('Varía las fuentes, podría ser considerado spam'),
                'Forbidden due to low entropy: ' . $ratio . ' < ' . $threshold . ': ' . $this->link->url
            );
        }

        return $this;
    }

    public function checkMedia()
    {
        global $globals, $db, $current_user;

        $sents = $this->getUserSent();

        if ($sents <= 5 || ($this->link->content_type !== 'image' && $this->link->content_type !== 'video')) {
            return $this;
        }

        $count = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links, subs, sub_statuses
            WHERE (
                link_author = "' . $current_user->user_id . '"
                AND link_date > DATE_SUB(NOW(), INTERVAL 60 DAY)
                AND link_content_type IN ("image", "video")
                AND sub_statuses.link = link_id
                AND subs.id = sub_statuses.id
                AND sub_statuses.origen = sub_statuses.id
                AND subs.parent = 0
                AND subs.owner = 0
            );
        ');

        if ($count > $sents * 0.8) {
            $this->setError(
                _('Ya has enviado demasiadas imágenes o vídeos'),
                '',
                'Forbidden due to too many images or video sent by user: ' . $this->link->url
            );
        }

        return $this;
    }

    public function checkMediaOverflow($hours)
    {
        global $globals, $db, $current_user;

        if ($this->link->content_type !== 'image' && $this->link->content_type !== 'video') {
            return $this;
        }

        $limit = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links, subs, sub_statuses
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL ' . (int) $hours . ' HOUR)
                AND sub_statuses.link = link_id
                AND subs.id = sub_statuses.id
                AND sub_statuses.origen = sub_statuses.id
                AND subs.parent = 0
                AND subs.owner = 0
            );
        ');

        $count = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links, subs, sub_statuses
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL ' . (int) $hours . ' HOUR)
                AND link_content_type IN ("image", "video")
                AND sub_statuses.link = link_id
                AND subs.id = sub_statuses.id
                AND sub_statuses.origen = sub_statuses.id
                AND subs.parent = 0
                AND subs.owner = 0
            );
        ');

        if ($count > 5 && $count > $limit * 0.15) {
            // Only 15% images AND videos
            $this->setError(
                _('Ya se han enviado demasiadas imágenes o vídeos, espera unos minutos por favor'),
                __('El total en 12 horas ha sido % y el máximo actual es %s', $count, intval($limit * 0.05)),
                'Forbidden due to overflow images: ' . $this->link->url
            );
        }

        return $this;
    }

    public function checkBlogSame($blog, $hours)
    {
        global $globals, $db, $current_user;

        $count = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL ' . (int) $hours . ' HOUR)
                AND link_author = "' . $current_user->user_id . '"
                AND link_blog = "' . $blog->id . '"
                AND link_votes > 0
            );
        ');

        if ($count >= $globals['limit_same_site_24_hours']) {
            $this->setError(
                _('Demasiados enlaces al mismo sitio en las últimas horas'),
                '',
                'Forbidden due to too many links to the same site in last ' . $hours . ' hours: ' . $this->link->url
            );
        }

        return $this;
    }

    public function checkBlogFast($blog, $minutes)
    {
        global $globals, $db, $current_user;

        $count = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL ' . (int) $minutes . ' MINUTE)
                AND link_author = "' . $current_user->user_id . '"
                AND link_blog = "' . $blog->id . '"
                AND link_votes > 0
            );
        ');

        if ($count && $current_user->user_karma < 12) {
            $this->setError(
                _('Ya has enviado un enlace al mismo sitio hace poco tiempo'),
                __('Debes esperar %s minutos entre envíos al mismo sitio.', $minutes),
                'Forbidden due to short period between links to same site: ' . $this->link->url
            );
        }

        return $this;
    }

    public function checkBlogHistory($blog, $days)
    {
        global $globals, $db, $current_user;

        $sents = $this->getUserSent();

        if ($sents <= 3) {
            return $this;
        }

        $count = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE (
                link_author = "' . $current_user->user_id . '"
                AND link_date > DATE_SUB(NOW(), INTERVAL ' . (int) $days . ' DAY)
                AND link_blog = "' . $blog->id . '"
            );
        ');

        $ratio = $count / $sents;

        if (!$count || ($ratio <= 0.5)) {
            return $this;
        }

        $e = __('Has enviado demasiados enlaces a %s', $blog->url);

        if ($sents > 5 && $ratio > 0.75) {
            $this->setError(
                $e,
                _('Has superado los límites de envíos de este sitio'),
                'Warn, high ratio, process interrumped: ' . $this->link->url
            );
        }

        $this->setWarning(
            $e,
            _('Continúa, pero ten en cuenta podría recibir votos negativos') . ', ' . '<a href="' . $globals['base_url'] . $globals['legal'] . '">' . _('condiciones de uso') . '</a>' .
            'warn, high ratio, continue: ' . $this->link->url
        );

        return $this;
    }

    public function checkBlogOverflow($blog, $hours)
    {
        global $globals, $db, $current_user;

        // check there is no an 'overflow' FROM the same site
        $site = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links, subs, sub_statuses
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL ' . (int) $hours . ' HOUR)
                AND link_blog = "' . $blog->id . '"
                AND link_status = "queued"
                AND sub_statuses.link = link_id
                AND subs.id = sub_statuses.id
                AND sub_statuses.origen = sub_statuses.id
                AND subs.parent = 0
                AND subs.owner = 0
            );
        ');

        if ($site <= 10) {
            return $this;
        }

        $time = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links, subs, sub_statuses
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL ' . (int) $hours . ' HOUR)
                AND sub_statuses.link = link_id
                AND subs.id = sub_statuses.id
                AND sub_statuses.origen = sub_statuses.id
                AND subs.parent = 0
                AND subs.owner = 0
            );
        ');

        if ($site > $time * 0.05) {
            // Only 5% FROM the same site
            $this->setError(
                _('Hay en cola demasiados envíos del mismo sitio, espera unos minutos por favor'),
                __('Total en 12 horas %s y el máximo actual es de %s', $site, intval($time * 0.05)),
                'Forbidden due to overflow to the same site: ' . $this->link->url
            );
        }

        return $this;
    }

    public function getUserLimitInterval()
    {
        if ($this->getUserSent()) {
            $limit = $globals['user_links_limit'];
            $interval = $globals['user_links_interval'];
        } else {
            $limit = $globals['new_user_links_limit'];
            $interval = $globals['new_user_links_interval'];
        }

        return array($limit, intval($interval / 3600));
    }

    public function getUserDrafts()
    {
        global $globals, $db, $current_user;

        if ($this->userDrafts !== null) {
            return $this->userDrafts;
        }

        $minutes = intval($globals['draft_time'] / 60) + 10;

        return $this->userDrafts = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE (
                link_author = "' . $current_user->user_id . '"
                AND link_date > DATE_SUB(NOW(), INTERVAL ' . $minutes . ' MINUTE)
                AND link_status = "discard"
                AND link_votes = 0
            );
        ');
    }

    public function getUserVotes()
    {
        global $globals, $db, $current_user;

        if ($this->userVotes !== null) {
            return $this->userVotes;
        }

        return $this->userVotes = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM votes
            WHERE (
                vote_type = "links"
                AND vote_date > DATE_SUB(NOW(), INTERVAL 72 HOUR)
                AND vote_user_id = "' . $current_user->user_id . '"
            );
        ');
    }

    public function getUserLinks()
    {
        global $globals, $db, $current_user;

        if ($this->userLinks !== null) {
            return $this->userLinks;
        }

        return $this->userLinks = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE (
                link_author = "' . $current_user->user_id . '"
                AND link_date > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND link_status != "discard"
            );
        ') + 1;
    }

    public function getUserSent()
    {
        global $globals, $db, $current_user;

        if ($this->userSent !== null) {
            return $this->userSent;
        }

        return $this->userSent = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE link_author = "' . $current_user->user_id . '";
        ') - $this->getUserDrafts();
    }

    public function getUserSentRecent()
    {
        global $globals, $db, $current_user;

        if ($this->userSentRecent !== null) {
            return $this->userSentRecent;
        }

        return $this->userSentRecent = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE (
                link_author = "' . $current_user->user_id . '"
                AND link_date > DATE_SUB(NOW(), INTERVAL 60 DAY)
            );
        ') - $this->getUserDrafts();
    }

    public function getLinksQueded()
    {
        global $globals, $db, $current_user;

        if ($this->linksQueded !== null) {
            return $this->linksQueded;
        }

        return $this->linksQueded = (int) $db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE (
                link_date > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND link_status = "queued"
            );
        ');
    }

    private function setError($title, $info = null, $syslog = '')
    {
        $this->error = [
            'title' => $title,
            'info' => $info,
            'syslog' => $syslog
        ];

        throw new Exception($title);
    }

    private function setWarning($title, $info = null, $syslog = '')
    {
        $this->warning = [
            'title' => $title,
            'info' => $info,
            'syslog' => $syslog
        ];
    }
}
