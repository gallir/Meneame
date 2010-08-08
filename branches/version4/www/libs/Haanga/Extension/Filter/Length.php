<?php

class Haanga_Extension_Filter_Length
{
    function generator($compiler, $args)
    {
        if (Haanga_AST::is_str($args[0])) {
            return hexec('strlen', $args[0]);
        }
        return hexpr_cond(hexec('is_array', $args[0]), hexec('count', $args[0]),
            hexec('strlen', $args[0]));
    }
}
