<?php

class Haanga_Extension_Tag_Tryinclude
{
    static function generator($cmp, $args, $declared)
    {
        if ($declared) {
            $cmp->Error("try_include can't be redirected to a variable");
        }

        $code = hcode();
        $exec = hexec('Haanga::Safe_Load', $args[0], hvar('vars'), TRUE, array());

        $cmp->do_print($code, $exec);

        return $code;

    }
}
