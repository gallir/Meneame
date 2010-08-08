<?php

class Haanga_Extension_Tag_Templatetag
{
    function generator($compiler, $args)
    {
        if (count($args) != 1) {
            throw new Haanga_Compiler_Exception("templatetag only needs one parameter");
        }

        if (Haanga_AST::is_var($args[0])) {
            $type = $args[0]['var'];
            if (!is_string($type)) {
                throw new Haanga_Compiler_Exception("Invalid parameter");
            }
        } else if (Haanga_AST::is_str($args[0])) {
            $type = $args[0]['string'];
        }

        switch ($type)
        {
        case 'openblock':
            $str = '{%';
            break;
        case 'closeblock':
            $str = '%}';
            break;
        case 'openbrace':
            $str = '{';
            break;
        case 'closebrace':
            $str = '}';
            break;
        case 'openvariable':
            $str = '{{';
            break;
        case 'closevariable':
            $str = '}}';
            break;
        case 'opencomment':
            $str = '{#';
            break;
        case 'closecomment':
            $str = '#}';
            break;
        default:
            throw new Haanga_Compiler_Exception("Invalid parameter");
            break;
        }

        $code = hcode();
        $compiler->do_print($code, Haanga_AST::str($str));

        return $code;
    }
}
