<?php

class Haanga_Extension_Filter_Reverse
{
    static function generator($compiler, $args)
    {
        if (count($args) != 1) {
            throw new Haanga_Compiler_Exception("Reverse only needs one parameter");
        }

        return hexec('array_reverse', $args[0], TRUE);
    }
}
