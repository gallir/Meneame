<?php
require_once __DIR__.'/config.php';
require_once mnminclude.'html1.php';

do_header(_('Novedades en Menéame') . ' | ' . _('menéame'));

Haanga::Load('changelog.html');

do_footer_menu();
do_footer();
