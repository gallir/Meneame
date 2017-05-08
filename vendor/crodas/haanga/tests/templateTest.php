<?php

class Foo_Bar {
    static $Bar = 'haanga';
    static $Arr = array('foo', 'Bar' => 'Foo');
    protected $foo = 'foo';
    public $bar = 'bar';

    static function something()
    {
        return 'something';
    }

    function method() {
        return $this->foo;
    }

    function bar() {
        return "something else";
    }
}

/**
 *  @runTestsInSeparateProcess
 */
class templateTest extends PHPUnit_Framework_TestCase
{
    public function init($test_file, &$expected)
    {
        Haanga_Compiler::setOption('allow_exec', true);
        if ($test_file === '/assert_templates/strip_whitespace.tpl') {
            Haanga_Compiler::setOption('strip_whitespace', TRUE);
            $expected = rtrim($expected). ' '; /* weird output */
        } else {
            Haanga_Compiler::setOption('strip_whitespace', FALSE);
        }
    }
    /** 
     * @dataProvider tplProvider
     */
    public function testRuntime($test_file, $data, $expected)
    {
        $this->init($test_file, $expected);
        $output = Haanga::Load($test_file, $data, TRUE);
        $this->assertEquals($output, $expected);
        $this->assertTrue(filemtime(__DIR__ . $test_file) <= filemtime(__DIR__ . '/tmp/assert_templates/' . basename($test_file) . '.php'));
    }

    /** 
     * @dataProvider tplProvider
     */
    public function testLambda($test_file, $data, $expected)
    {
        chdir(dirname(__DIR__ . '/' . $test_file));
        $this->init($test_file, $expected);
        $callback = Haanga::compile(file_get_contents(__DIR__ . $test_file), $data);
        $output   = $callback($data);
        $this->assertEquals($output, $expected);
    }


    /** 
     * @dataProvider tplProvider
     */
    public function testIsCached($test_file, $data, $expected)
    {
        /* same as above, but we ensure that the file wasn't compiled */
        $this->init($test_file, $expected);
        $output = Haanga::Load($test_file, $data, TRUE);
        $this->assertEquals($output, $expected);
        $this->assertFalse(Haanga::$has_compiled);
    }

    public static function tplProvider()
    {
        $datas = array();
        foreach (glob(__DIR__  . "/assert_templates/*.tpl") as $test_file) {
            $data = array();
            $data_file = substr($test_file, 0, -3)."php";
            $expected  = substr($test_file, 0, -3)."html";
            $test_file = substr($test_file, strlen(__DIR__));
            if (!is_file($expected)) {
                if (!is_file($expected.".php")) {
                    continue;
                } 
                $expected .= ".php";
                ob_start();
                require $expected;
                $expected = ob_get_clean();
            } else {
                $expected = file_get_contents($expected);
            }

            if (is_file($data_file)) {
                try {
                    include $data_file;
                } Catch (Exception $e) {
                    continue;
                }
            }
            $datas[] = array($test_file, $data, $expected);
        }

        return $datas;
    }
}
