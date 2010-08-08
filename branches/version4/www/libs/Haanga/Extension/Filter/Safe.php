<?php

class Haanga_Extension_Filter_Safe
{
    function generator($compiler, $args)
    {
        $compiler->var_is_safe = TRUE;
        return current($args);
    }
}
