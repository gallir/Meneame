<?php

class Haanga_Extension_Filter_Substr
{
    public static function generator($compiler, $args)
    {
        if (count($args) != 2) {
            throw new Haanga_Compiler_Exception("substr parameter must have one param");
        }
        if (!isset($args[1]['string'])) {
            throw new Haanga_Compiler_Exception("substr parameter must be a string");
        }
        list($start, $end) = explode(",", $args[1]['string']);
        return hexec('substr', $args[0],  (int)$start, (int)$end);
    }
}
