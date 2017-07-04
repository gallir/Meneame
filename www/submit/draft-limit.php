<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

defined('mnminclude') or die();

do_header(_('Alcanzado límite de borradores'), _('Aviso'));

global $current_user;

Haanga::Load('story/submit/draft_limit.html', [
    'current_user' => $current_user
]);
