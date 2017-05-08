<?php

class Haanga_Extension_Filter_Explode
{
    public static function generator($compiler, $args)
    {
        if (count($args) == 1 || $args[1] == "") {
            return hexec("str_split", $args[0]);
        }
        return hexec("explode", $args[1], $args[0]);
    }
}
