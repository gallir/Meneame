<?php

/**
 *  @runTestsInSeparateProcess
 */
class errorTest extends PHPUnit_Framework_TestCase
{
    /** 
     * @dataProvider tplProvider
     *  
     */
    public function testInvalidTemplates($tpl)
    {
        Haanga_Compiler::setOption('allow_exec', FALSE);
        try {
            Haanga::Load($tpl);
            $this->assertTrue(FALSE);
        } Catch (Haanga_Compiler_Exception $e) {
            $i = preg_match("/in.*:[0-9]+/", $e->getMessage());
            $this->assertEquals(1, $i);
        }
    }

    public static function tplProvider()
    {
        $datas = array();
        foreach (glob(__DIR__ . "/err_templates/*.tpl") as $err_file) {
            $datas[] = array(substr($err_file, strlen(__DIR__)));
        }

        return $datas;
    }

}

