<?php
$obj = new stdclass;
$obj->str = 'foo';
$data = array('index' => array('name' => $obj), 'indexstr' => 'foo');
$obj  = new Stdclass;
$obj->foo = array('bar' => 'c');

global $test_global, $global1;

$test_global = array('b' => 'string');
$global1     = array('foo' => $obj);

if (!is_callable('set_global_template')) {
    function set_global_template()
    {
        global $test_global, $global1;
        $global1['bar']      = new stdclass;
        $global1['bar']->xxx = new stdclass;
        $global1['bar']->xxx->yyyy = 'xxx';
    }
}
