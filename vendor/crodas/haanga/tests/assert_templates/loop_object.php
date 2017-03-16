<?php

$obj = new stdclass;
$obj->foo = 'bar';
$obj->nombre = 'crodas';

$objects = array($obj);

$data = compact('obj', 'objects');
