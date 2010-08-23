<?php

class Haanga_Extension_Filter_Hostname
{
    static function generator($cmp, $args)
    {
        return hexec('parser_url', $args[0], hconst('PHP_URL_HOST'));
    }
}
