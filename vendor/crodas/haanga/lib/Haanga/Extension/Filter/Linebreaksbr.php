<?php

class Haanga_Extension_Filter_Linebreaksbr
{
    static function generator($compiler, $args)
    {
    	$compiler->var_is_safe = TRUE;			/* we assume that if you use |linebreaksbr, you also want |safe */
        return hexec('preg_replace', "/\r\n|\r|\n/", "<br />\n", $args[0]);
    }
}
