<?php

require "../lib/Haanga.php";

$fnc = Haanga::compile(<<<EOT
    <h1>{{foobar}}{{    foobar  }}</h1>

    Este template será compilado a una función PHP ({{foo|default:foobar}})


EOT
);

$fnc(array("foobar" => 'hola', 'foo' => '.I.'), FALSE /* print it */);
$fnc(array("foobar" => 'chau'), FALSE /* print it */);
