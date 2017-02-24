<?php

class Haanga_Extension_Tag_FirstOf
{
    /**
     *  firstof tag
     *
     */
    public static function generator($cmp, $args)
    {
        $count = count($args);
        $args  = array_reverse($args);
        for ($i=0; $i < $count; $i++) {
            if (isset($expr) && Haanga_AST::is_var($args[$i])) {
                $expr = hexpr_cond(hexpr(hexec('empty', $args[$i]), '==', false), $args[$i], $expr);
            } else {
                $expr = $args[$i];
            }
        }
        return $expr;
    }
}
