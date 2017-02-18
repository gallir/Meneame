<?php

class Haanga_Extension_Filter_StringFormat
{
    public static function generator($compiler, $args)
    {
        return hexec('sprintf', $args[1], $args[0]);
    }
}
