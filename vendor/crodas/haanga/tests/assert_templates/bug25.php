<?php

if (!class_exists('Obj', false)) {
    require __DIR__ . '/bug25_class.php';
}

$data = array('obj' => new Obj, 'base' => array('endpoint' => 'a'));
