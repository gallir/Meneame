<?php
$data = array('text' => 'hello');

if (!is_callable('bindtextdomain')) {
    throw new Exception('no gettext enabled');
}

$locale='en_US.utf8';
putenv("LC_ALL=$locale");
if (!setlocale(LC_ALL, $locale)) {
    throw new Exception('no gettext enabled');
}

bindtextdomain("messages", dirname(__FILE__) . "/locale");
textdomain("messages");
