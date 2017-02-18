<?php

class Haanga_Extension_Filter_Date
{
    public static function generator($compiler, $args)
    {
        return hexec('date', $args[1], $args[0]);
    }
}
