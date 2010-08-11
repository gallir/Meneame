<?php

class Haanga_Extension_Tag_MeneameEndtime
{
    public $is_block = FALSE;

    static function generator($cmp, $args, $assign=NULL)
    {
        /* abs */
        $code = hcode();
        
        /* llamar a la funcion */
        $exec = hexec('sprintf', "<!--Generated in %4.3f seconds-->", 
            hexpr( hexec('microtime', TRUE), '-', hvar('globals', 'start_time') )
        );

        /* imprimir la funcion */
        $cmp->do_print($code, $exec);

        return $code;
    }
}

class Haanga_Extension_Tag_GetURL
{
    public $is_block = FALSE;

    static function generator($cmp, $args, $assign=NULL)
    {
        $code = hcode();

        if ($assign) {
            /* Return the variable */
            $assign = hvar($assign);
            #$code->decl($assign, Haanga_AST::Str('http://'));
            #$code->append($assign, hexec('get_server_name'));
            $code->append($assign, hvar('globals', 'base_static'));
            foreach ($args as $arg) {
                $code->append($assign, $arg);
            }
        } else {
            /* print */
            #$cmp->do_print($code, Haanga_AST::str('http://'));
            #$cmp->do_print($code, hexec('get_server_name'));
            $cmp->do_print($code, hvar('globals', 'base_url'));
            foreach ($args as $arg) {
                $cmp->do_print($code, $arg);
            }
        }

        return $code;
    }
}

class Haanga_Extension_Tag_GetStaticURL
{
    public $is_block = FALSE;

    static function generator($cmp, $args, $assign=NULL)
    {
        $code = hcode();
        if ($assign) {
            /* Return the variable */
            $assign = hvar($assign);
            #$code->decl($assign, Haanga_AST::Str('http://'));
            #$code->append($assign, hexec('get_server_name'));
            $code->append($assign, hvar('globals', 'base_static'));
            foreach ($args as $arg) {
                $code->append($assign, $arg);
            }
        } else {
            /* print */
            #$cmp->do_print($code, Haanga_AST::str('http://'));
            #$cmp->do_print($code, hexec('get_server_name'));
            $cmp->do_print($code, hvar('globals', 'base_static'));
            foreach ($args as $arg) {
                $cmp->do_print($code, $arg);
            }
        }

        return $code;
    }
}
