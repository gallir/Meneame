<?php

$obj = new Stdclass;
$obj->name = 'foo';
$obj->obj['name'] = 'bar';
$arr['obj'] = $obj;
$data = compact('obj', 'arr');
