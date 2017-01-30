<?php
include('config.php');
include(mnminclude.'html1.php');

do_header(_('Novedades en Menéame') . ' | ' . _('menéame'));

Haanga::Load('changelog.html');

do_footer_menu();
do_footer();
