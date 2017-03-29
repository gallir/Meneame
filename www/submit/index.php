<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once __DIR__ . '/bootstrap.php';

do_header(_('Enviar historia') . ' 1/3', _('Enviar historia'));

Haanga::Load('story/submit/step1.html', array(
    'randkey' => rand(10000, 10000000),
    'key' => get_security_key(),
    'url' => (!empty($_GET['url']) ? clean_input_url($_GET['url']) : null),
    'site_properties' => $site_properties
));

do_footer();
