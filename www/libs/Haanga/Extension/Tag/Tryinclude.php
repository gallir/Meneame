<?php

class Haanga_Extension_Tag_Tryinclude
{
    public static function generator($cmp, $args, $declared)
    {
        if ($declared) {
            $cmp->Error("try_include can't be redirected to a variable");
        }

        $code = hcode();
        $exec = hexec('Haanga::Safe_Load', $args[0], $cmp->getScopeVariable(), true, array());

        $cmp->do_print($code, $exec);

        return $code;
    }
}
