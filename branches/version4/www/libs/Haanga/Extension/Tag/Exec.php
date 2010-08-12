<?php


class Haanga_Extension_Tag_Exec
{
    public $is_block = FALSE;

    static function generator($cmp, $args, $assign=NULL)
    {
        if (!$cmp->getOption('allow_exec')) {
            $cmp->Error("Tag exec is disabled for security reasons");
        }


        $code = hcode();
        if (Haanga_AST::is_var($args[0])) {
            $args[0] = $args[0]['var'];
        } else if (Haanga_AST::is_str($args[0])) {
            $args[0] = $args[0]['string'];
        } else {
            $cmp->Error("invalid param");
        }

        $exec = hexec($args[0]);
        for ($i=1; $i < count($args); $i++) {
            $exec->param($args[$i]);
        }
        $exec->end();
        if ($assign) {
            $code->decl($assign, $exec);
        } else {
            $cmp->do_print($code, $exec);
        }
        return $code;
    }
}

