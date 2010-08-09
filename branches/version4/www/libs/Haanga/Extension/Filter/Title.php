<?php

class Haanga_Extension_Filter_Title
{
    static function generator($compiler, $args)
    {
        if (count($args) != 1) {
            throw new Haanga_Compiler_Exception("title filter only needs one parameter");
        }

        return hexec('ucwords', hexec('strtolower', $args[0]));
    }
}
