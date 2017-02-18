<?php

class Haanga_Extension_Filter_Default
{
    public static function generator($compiler, $args)
    {
        return hexpr_cond(hexpr(hexec('empty', $args[0]), '==', true), $args[1], $args[0]);
    }
}
