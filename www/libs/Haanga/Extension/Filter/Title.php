<?php

class Haanga_Extension_Filter_Title
{
    public static function generator($compiler, $args)
    {
        if (count($args) != 1) {
            $compiler->Error("title filter only needs one parameter");
        }

        return hexec('ucwords', hexec('strtolower', $args[0]));
    }
}
