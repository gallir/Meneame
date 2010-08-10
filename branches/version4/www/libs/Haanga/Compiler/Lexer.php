<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2010 César Rodas and Menéame Comunicacions S.L.                   |
  +---------------------------------------------------------------------------------+
  | Redistribution and use in source and binary forms, with or without              |
  | modification, are permitted provided that the following conditions are met:     |
  | 1. Redistributions of source code must retain the above copyright               |
  |    notice, this list of conditions and the following disclaimer.                |
  |                                                                                 |
  | 2. Redistributions in binary form must reproduce the above copyright            |
  |    notice, this list of conditions and the following disclaimer in the          |
  |    documentation and/or other materials provided with the distribution.         |
  |                                                                                 |
  | 3. All advertising materials mentioning features or use of this software        |
  |    must display the following acknowledgement:                                  |
  |    This product includes software developed by César D. Rodas.                  |
  |                                                                                 |
  | 4. Neither the name of the César D. Rodas nor the                               |
  |    names of its contributors may be used to endorse or promote products         |
  |    derived from this software without specific prior written permission.        |
  |                                                                                 |
  | THIS SOFTWARE IS PROVIDED BY CÉSAR D. RODAS ''AS IS'' AND ANY                   |
  | EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED       |
  | WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE          |
  | DISCLAIMED. IN NO EVENT SHALL CÉSAR D. RODAS BE LIABLE FOR ANY                  |
  | DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES      |
  | (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;    |
  | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND     |
  | ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT      |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS   |
  | SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE                     |
  +---------------------------------------------------------------------------------+
  | Authors: César Rodas <crodas@php.net>                                           |
  +---------------------------------------------------------------------------------+
*/

class HG_Parser Extends Haanga_Compiler_Parser
{
    /* subclass to made easier references to constants */
}

class Haanga_Compiler_Lexer
{
    private $data;
    private $N;
    public $token;
    public $value;
    private $line;
    private $state = 1;

    function __construct($data, $compiler)
    {
        $this->data     = $data;
        $this->compiler = $compiler;
        $this->N        = 0;
        $this->line     = 1;
    }

    static function init($template, $compiler, $file='')
    {
        $lexer  = new Haanga_Compiler_Lexer($template, $compiler);
        $parser = new Haanga_Compiler_Parser($lexer, $file);

        $parser->compiler = $compiler;

        try {
            for($i=0; ; $i++) {
                if  (!$lexer->yylex()) {
                    break;
                }
                $parser->doParse($lexer->token, $lexer->value);
            }
        } catch (Exception $e) {
            throw new Haanga_Compiler_Exception($e->getMessage(). ' on line '.$lexer->getLine());
        }
        $parser->doParse(0, 0);
        return (array)$parser->body;
    }

    function getLine()
    {
        return $this->line;
    }

    public $custom_tags=array();

    function is_custom_tag()
    {
        static $tag=NULL;
        if (!$tag) {
            $tag = Haanga_Extension::getInstance('Tag');
        }
        $value = $tag->isValid($this->value);
        $this->token = $value ? $value : HG_Parser::T_ALPHA;
    }


    private $_yy_state = 1;
    private $_yy_stack = array();

    function yylex()
    {
        return $this->{'yylex' . $this->_yy_state}();
    }

    function yypushstate($state)
    {
        array_push($this->_yy_stack, $this->_yy_state);
        $this->_yy_state = $state;
    }

    function yypopstate()
    {
        $this->_yy_state = array_pop($this->_yy_stack);
    }

    function yybegin($state)
    {
        $this->_yy_state = $state;
    }



    function yylex1()
    {
        $tokenMap = array (
              1 => 0,
              2 => 0,
              3 => 0,
              4 => 2,
            );
        if ($this->N >= strlen($this->data)) {
            return false; // end of input
        }
        $yy_global_pattern = "/^(\\{%)|^(\\{#)|^(\\{\\{)|^(([^{]+(.[^%{#])?)+)/";

        do {
            if (preg_match($yy_global_pattern, substr($this->data, $this->N), $yymatches)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        'an empty string.  Input "' . substr($this->data,
                        $this->N, 5) . '... state IN_HTML');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r1_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    if ($this->N >= strlen($this->data)) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                } else {                    $yy_yymore_patterns = array(
        1 => array(0, "^(\\{#)|^(\\{\\{)|^(([^{]+(.[^%{#])?)+)"),
        2 => array(0, "^(\\{\\{)|^(([^{]+(.[^%{#])?)+)"),
        3 => array(0, "^(([^{]+(.[^%{#])?)+)"),
        4 => array(2, ""),
    );

                    // yymore is needed
                    do {
                        if (!strlen($yy_yymore_patterns[$this->token][1])) {
                            throw new Exception('cannot do yymore for the last token');
                        }
                        $yysubmatches = array();
                        if (preg_match('/' . $yy_yymore_patterns[$this->token][1] . '/',
                              substr($this->data, $this->N), $yymatches)) {
                            $yysubmatches = $yymatches;
                            $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                            next($yymatches); // skip global match
                            $this->token += key($yymatches) + $yy_yymore_patterns[$this->token][0]; // token number
                            $this->value = current($yymatches); // token value
                            $this->line = substr_count($this->value, "\n");
                            if ($tokenMap[$this->token]) {
                                // extract sub-patterns for passing to lex function
                                $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                                    $tokenMap[$this->token]);
                            } else {
                                $yysubmatches = array();
                            }
                        }
                    	$r = $this->{'yy_r1_' . $this->token}($yysubmatches);
                    } while ($r !== null && !is_bool($r));
			        if ($r === true) {
			            // we have changed state
			            // process this token in the new state
			            return $this->yylex();
                    } elseif ($r === false) {
                        $this->N += strlen($this->value);
                        $this->line += substr_count($this->value, "\n");
                        if ($this->N >= strlen($this->data)) {
                            return false; // end of input
                        }
                        // skip this token
                        continue;
			        } else {
	                    // accept
	                    $this->N += strlen($this->value);
	                    $this->line += substr_count($this->value, "\n");
	                    return true;
			        }
                }
            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->N]);
            }
            break;
        } while (true);

    } // end function


    const IN_HTML = 1;
    function yy_r1_1($yy_subpatterns)
    {

    $this->token = HG_Parser::T_OPEN_TAG;
    $this->yypushstate(self::IN_CODE);
    }
    function yy_r1_2($yy_subpatterns)
    {

    $this->token = HG_Parser::T_COMMENT_OPEN;
    $this->yypushstate(self::IN_COMMENT);
    }
    function yy_r1_3($yy_subpatterns)
    {

    $this->token = HG_Parser::T_PRINT_OPEN;
    $this->yypushstate(self::IN_PRINT);
    }
    function yy_r1_4($yy_subpatterns)
    {

    $this->token = HG_Parser::T_HTML;
    }


    function yylex2()
    {
        $tokenMap = array (
              1 => 0,
              2 => 0,
              3 => 0,
              4 => 0,
              5 => 0,
              6 => 0,
              7 => 0,
              8 => 0,
              9 => 0,
              10 => 0,
              11 => 0,
              12 => 0,
              13 => 0,
              14 => 0,
              15 => 0,
              16 => 0,
              17 => 0,
              18 => 0,
              19 => 0,
              20 => 0,
              21 => 0,
              22 => 0,
              23 => 0,
              24 => 0,
              25 => 0,
              26 => 0,
              27 => 0,
              28 => 0,
              29 => 0,
              30 => 0,
              31 => 0,
              32 => 0,
              33 => 0,
              34 => 0,
              35 => 0,
              36 => 0,
              37 => 0,
              38 => 0,
              39 => 0,
              40 => 0,
              41 => 0,
              42 => 0,
              43 => 0,
              44 => 0,
              45 => 0,
              46 => 0,
              47 => 0,
              48 => 0,
              49 => 0,
              50 => 0,
              51 => 0,
              52 => 0,
              53 => 0,
              54 => 0,
              55 => 0,
              56 => 0,
              57 => 0,
              58 => 0,
              59 => 0,
              60 => 0,
              61 => 1,
              63 => 0,
              64 => 0,
              65 => 1,
              67 => 2,
              70 => 1,
              72 => 0,
            );
        if ($this->N >= strlen($this->data)) {
            return false; // end of input
        }
        $yy_global_pattern = "/^(%\\})|^(->)|^(\\.)|^(buffer[^a-zA-Z0-9_\.])|^(for[^a-zA-Z0-9_\.])|^(empty[^a-zA-Z0-9_\.])|^(load[^a-zA-Z0-9_\.])|^(block[^a-zA-Z0-9_\.])|^(&&)|^(AND[^a-zA-Z0-9_\.])|^(\\|\\|)|^(OR[^a-zA-Z0-9_\.])|^(==)|^(!=)|^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)/";

        do {
            if (preg_match($yy_global_pattern, substr($this->data, $this->N), $yymatches)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        'an empty string.  Input "' . substr($this->data,
                        $this->N, 5) . '... state IN_CODE');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r2_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    if ($this->N >= strlen($this->data)) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                } else {                    $yy_yymore_patterns = array(
        1 => array(0, "^(->)|^(\\.)|^(buffer[^a-zA-Z0-9_\.])|^(for[^a-zA-Z0-9_\.])|^(empty[^a-zA-Z0-9_\.])|^(load[^a-zA-Z0-9_\.])|^(block[^a-zA-Z0-9_\.])|^(&&)|^(AND[^a-zA-Z0-9_\.])|^(\\|\\|)|^(OR[^a-zA-Z0-9_\.])|^(==)|^(!=)|^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        2 => array(0, "^(\\.)|^(buffer[^a-zA-Z0-9_\.])|^(for[^a-zA-Z0-9_\.])|^(empty[^a-zA-Z0-9_\.])|^(load[^a-zA-Z0-9_\.])|^(block[^a-zA-Z0-9_\.])|^(&&)|^(AND[^a-zA-Z0-9_\.])|^(\\|\\|)|^(OR[^a-zA-Z0-9_\.])|^(==)|^(!=)|^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        3 => array(0, "^(buffer[^a-zA-Z0-9_\.])|^(for[^a-zA-Z0-9_\.])|^(empty[^a-zA-Z0-9_\.])|^(load[^a-zA-Z0-9_\.])|^(block[^a-zA-Z0-9_\.])|^(&&)|^(AND[^a-zA-Z0-9_\.])|^(\\|\\|)|^(OR[^a-zA-Z0-9_\.])|^(==)|^(!=)|^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        4 => array(0, "^(for[^a-zA-Z0-9_\.])|^(empty[^a-zA-Z0-9_\.])|^(load[^a-zA-Z0-9_\.])|^(block[^a-zA-Z0-9_\.])|^(&&)|^(AND[^a-zA-Z0-9_\.])|^(\\|\\|)|^(OR[^a-zA-Z0-9_\.])|^(==)|^(!=)|^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        5 => array(0, "^(empty[^a-zA-Z0-9_\.])|^(load[^a-zA-Z0-9_\.])|^(block[^a-zA-Z0-9_\.])|^(&&)|^(AND[^a-zA-Z0-9_\.])|^(\\|\\|)|^(OR[^a-zA-Z0-9_\.])|^(==)|^(!=)|^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        6 => array(0, "^(load[^a-zA-Z0-9_\.])|^(block[^a-zA-Z0-9_\.])|^(&&)|^(AND[^a-zA-Z0-9_\.])|^(\\|\\|)|^(OR[^a-zA-Z0-9_\.])|^(==)|^(!=)|^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        7 => array(0, "^(block[^a-zA-Z0-9_\.])|^(&&)|^(AND[^a-zA-Z0-9_\.])|^(\\|\\|)|^(OR[^a-zA-Z0-9_\.])|^(==)|^(!=)|^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        8 => array(0, "^(&&)|^(AND[^a-zA-Z0-9_\.])|^(\\|\\|)|^(OR[^a-zA-Z0-9_\.])|^(==)|^(!=)|^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        9 => array(0, "^(AND[^a-zA-Z0-9_\.])|^(\\|\\|)|^(OR[^a-zA-Z0-9_\.])|^(==)|^(!=)|^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        10 => array(0, "^(\\|\\|)|^(OR[^a-zA-Z0-9_\.])|^(==)|^(!=)|^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        11 => array(0, "^(OR[^a-zA-Z0-9_\.])|^(==)|^(!=)|^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        12 => array(0, "^(==)|^(!=)|^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        13 => array(0, "^(!=)|^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        14 => array(0, "^(>=)|^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        15 => array(0, "^(not[^a-zA-Z0-9_\.])|^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        16 => array(0, "^(![^a-zA-Z0-9_\.])|^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        17 => array(0, "^(\\[)|^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        18 => array(0, "^(\\])|^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        19 => array(0, "^(>)|^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        20 => array(0, "^(<)|^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        21 => array(0, "^(=<)|^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        22 => array(0, "^(\\|)|^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        23 => array(0, "^(:)|^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        24 => array(0, "^(filter[^a-zA-Z0-9_\.])|^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        25 => array(0, "^(regroup[^a-zA-Z0-9_\.])|^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        26 => array(0, "^(endfilter[^a-zA-Z0-9_\.])|^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        27 => array(0, "^(autoescape[^a-zA-Z0-9_\.])|^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        28 => array(0, "^(spacefull[^a-zA-Z0-9_\.])|^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        29 => array(0, "^(endautoescape[^a-zA-Z0-9_\.])|^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        30 => array(0, "^(endblock[^a-zA-Z0-9_\.])|^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        31 => array(0, "^(ifchanged[^a-zA-Z0-9_\.])|^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        32 => array(0, "^(ifequal[^a-zA-Z0-9_\.])|^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        33 => array(0, "^(endifequal[^a-zA-Z0-9_\.])|^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        34 => array(0, "^(ifnotequal[^a-zA-Z0-9_\.])|^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        35 => array(0, "^(endifnotequal[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        36 => array(0, "^(else[^a-zA-Z0-9_\.])|^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        37 => array(0, "^(endifchanged[^a-zA-Z0-9_\.])|^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        38 => array(0, "^(in[^a-zA-Z0-9_\.])|^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        39 => array(0, "^(endfor[^a-zA-Z0-9_\.])|^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        40 => array(0, "^(with[^a-zA-Z0-9_\.])|^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        41 => array(0, "^(endwith[^a-zA-Z0-9_\.])|^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        42 => array(0, "^(as)|^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        43 => array(0, "^(on)|^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        44 => array(0, "^(off)|^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        45 => array(0, "^(by)|^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        46 => array(0, "^(if[^a-zA-Z0-9_\.])|^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        47 => array(0, "^(else[^a-zA-Z0-9_\.])|^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        48 => array(0, "^(endif[^a-zA-Z0-9_\.])|^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        49 => array(0, "^(_\\()|^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        50 => array(0, "^(\\()|^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        51 => array(0, "^(\\))|^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        52 => array(0, "^(%)|^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        53 => array(0, "^(,)|^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        54 => array(0, "^(\\+)|^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        55 => array(0, "^(-)|^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        56 => array(0, "^(\\*)|^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        57 => array(0, "^(\/)|^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        58 => array(0, "^(')|^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        59 => array(0, "^(\")|^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        60 => array(0, "^(end([a-zA-Z][a-zA-Z0-9]*))|^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        61 => array(1, "^(extends[^a-zA-Z0-9_\.])|^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        63 => array(1, "^(include[^a-zA-Z0-9_\.])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        64 => array(1, "^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        65 => array(2, "^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        67 => array(4, "^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        70 => array(5, "^([ \r\t\n]+)"),
        72 => array(5, ""),
    );

                    // yymore is needed
                    do {
                        if (!strlen($yy_yymore_patterns[$this->token][1])) {
                            throw new Exception('cannot do yymore for the last token');
                        }
                        $yysubmatches = array();
                        if (preg_match('/' . $yy_yymore_patterns[$this->token][1] . '/',
                              substr($this->data, $this->N), $yymatches)) {
                            $yysubmatches = $yymatches;
                            $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                            next($yymatches); // skip global match
                            $this->token += key($yymatches) + $yy_yymore_patterns[$this->token][0]; // token number
                            $this->value = current($yymatches); // token value
                            $this->line = substr_count($this->value, "\n");
                            if ($tokenMap[$this->token]) {
                                // extract sub-patterns for passing to lex function
                                $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                                    $tokenMap[$this->token]);
                            } else {
                                $yysubmatches = array();
                            }
                        }
                    	$r = $this->{'yy_r2_' . $this->token}($yysubmatches);
                    } while ($r !== null && !is_bool($r));
			        if ($r === true) {
			            // we have changed state
			            // process this token in the new state
			            return $this->yylex();
                    } elseif ($r === false) {
                        $this->N += strlen($this->value);
                        $this->line += substr_count($this->value, "\n");
                        if ($this->N >= strlen($this->data)) {
                            return false; // end of input
                        }
                        // skip this token
                        continue;
			        } else {
	                    // accept
	                    $this->N += strlen($this->value);
	                    $this->line += substr_count($this->value, "\n");
	                    return true;
			        }
                }
            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->N]);
            }
            break;
        } while (true);

    } // end function


    const IN_CODE = 2;
    function yy_r2_1($yy_subpatterns)
    {

    $this->token = HG_Parser::T_CLOSE_TAG;
    $this->yypopstate();
    }
    function yy_r2_2($yy_subpatterns)
    {

    $this->token = HG_Parser::T_OBJ;
    }
    function yy_r2_3($yy_subpatterns)
    {

    $this->token = HG_Parser::T_DOT;
    }
    function yy_r2_4($yy_subpatterns)
    {

    $this->token = HG_Parser::T_BUFFER;
    }
    function yy_r2_5($yy_subpatterns)
    {

    $this->token = HG_Parser::T_FOR;
    }
    function yy_r2_6($yy_subpatterns)
    {

    $this->token = HG_Parser::T_EMPTY;
    }
    function yy_r2_7($yy_subpatterns)
    {

    $this->token = HG_Parser::T_LOAD;
    }
    function yy_r2_8($yy_subpatterns)
    {

    $this->token = HG_Parser::T_BLOCK;
    }
    function yy_r2_9($yy_subpatterns)
    {
 
    $this->token = HG_Parser::T_AND;
    }
    function yy_r2_10($yy_subpatterns)
    {

    $this->token = HG_Parser::T_AND;
    }
    function yy_r2_11($yy_subpatterns)
    {

    $this->token = HG_Parser::T_OR;
    }
    function yy_r2_12($yy_subpatterns)
    {

    $this->token = HG_Parser::T_OR;
    }
    function yy_r2_13($yy_subpatterns)
    {

    $this->token = HG_Parser::T_EQ;
    }
    function yy_r2_14($yy_subpatterns)
    {

    $this->token = HG_Parser::T_NE;
    }
    function yy_r2_15($yy_subpatterns)
    {

    $this->token = HG_Parser::T_GE;
    }
    function yy_r2_16($yy_subpatterns)
    {

    $this->token = HG_Parser::T_NOT;
    }
    function yy_r2_17($yy_subpatterns)
    {

    $this->token = HG_Parser::T_NOT;
    }
    function yy_r2_18($yy_subpatterns)
    {

    $this->token = HG_Parser::T_BRACKETS_OPEN;
    }
    function yy_r2_19($yy_subpatterns)
    {

    $this->token = HG_Parser::T_BRACKETS_CLOSE;
    }
    function yy_r2_20($yy_subpatterns)
    {

    $this->token = HG_Parser::T_GT;
    }
    function yy_r2_21($yy_subpatterns)
    {

    $this->token = HG_Parser::T_LT;
    }
    function yy_r2_22($yy_subpatterns)
    {

    $this->token = HG_Parser::T_LE;
    }
    function yy_r2_23($yy_subpatterns)
    {

    $this->token = HG_Parser::T_PIPE;
    }
    function yy_r2_24($yy_subpatterns)
    {

    $this->token = HG_Parser::T_COLON;
    }
    function yy_r2_25($yy_subpatterns)
    {

    $this->token = HG_Parser::T_FILTER;
    }
    function yy_r2_26($yy_subpatterns)
    {

    $this->token = HG_Parser::T_REGROUP;
    }
    function yy_r2_27($yy_subpatterns)
    {

    $this->token = HG_Parser::T_END_FILTER;
    }
    function yy_r2_28($yy_subpatterns)
    {

    $this->token = HG_Parser::T_AUTOESCAPE;
    }
    function yy_r2_29($yy_subpatterns)
    {

    $this->token = HG_Parser::T_SPACEFULL;
    }
    function yy_r2_30($yy_subpatterns)
    {

    $this->token = HG_Parser::T_END_AUTOESCAPE;
    }
    function yy_r2_31($yy_subpatterns)
    {

    $this->token = HG_Parser::T_END_BLOCK;
    }
    function yy_r2_32($yy_subpatterns)
    {

    $this->token = HG_Parser::T_IFCHANGED;
    }
    function yy_r2_33($yy_subpatterns)
    {

    $this->token = HG_Parser::T_IFEQUAL;
    }
    function yy_r2_34($yy_subpatterns)
    {

    $this->token = HG_Parser::T_END_IFEQUAL;
    }
    function yy_r2_35($yy_subpatterns)
    {

    $this->token = HG_Parser::T_IFNOTEQUAL;
    }
    function yy_r2_36($yy_subpatterns)
    {

    $this->token = HG_Parser::T_END_IFNOTEQUAL;
    }
    function yy_r2_37($yy_subpatterns)
    {

    $this->token = HG_Parser::T_ELSE;
    }
    function yy_r2_38($yy_subpatterns)
    {

    $this->token = HG_Parser::T_ENDIFCHANGED;
    }
    function yy_r2_39($yy_subpatterns)
    {

    $this->token = HG_Parser::T_IN;
    }
    function yy_r2_40($yy_subpatterns)
    {

    $this->token = HG_Parser::T_CLOSEFOR;
    }
    function yy_r2_41($yy_subpatterns)
    {

    $this->token = HG_Parser::T_WITH;
    }
    function yy_r2_42($yy_subpatterns)
    {

    $this->token = HG_Parser::T_ENDWITH;
    }
    function yy_r2_43($yy_subpatterns)
    {

    $this->token = HG_Parser::T_AS;
    }
    function yy_r2_44($yy_subpatterns)
    {

    $this->token = HG_Parser::T_ON;
    }
    function yy_r2_45($yy_subpatterns)
    {

    $this->token = HG_Parser::T_OFF;
    }
    function yy_r2_46($yy_subpatterns)
    {

    $this->token = HG_Parser::T_BY;
    }
    function yy_r2_47($yy_subpatterns)
    {

    $this->token = HG_Parser::T_IF;
    }
    function yy_r2_48($yy_subpatterns)
    {

    $this->token = HG_Parser::T_ELSE;
    }
    function yy_r2_49($yy_subpatterns)
    {

    $this->token = HG_Parser::T_ENDIF;
    }
    function yy_r2_50($yy_subpatterns)
    {

    $this->token = HG_Parser::T_INTL;
    }
    function yy_r2_51($yy_subpatterns)
    {

    $this->token = HG_Parser::T_LPARENT;
    }
    function yy_r2_52($yy_subpatterns)
    {

    $this->token = HG_Parser::T_RPARENT;
    }
    function yy_r2_53($yy_subpatterns)
    {

    $this->token = HG_Parser::T_MOD;
    }
    function yy_r2_54($yy_subpatterns)
    {

    $this->token = HG_Parser::T_COMMA;
    }
    function yy_r2_55($yy_subpatterns)
    {

    $this->token = HG_Parser::T_PLUS;
    }
    function yy_r2_56($yy_subpatterns)
    {

    $this->token = HG_Parser::T_MINUS;
    }
    function yy_r2_57($yy_subpatterns)
    {

    $this->token = HG_Parser::T_TIMES;
    }
    function yy_r2_58($yy_subpatterns)
    {

    $this->token = HG_Parser::T_DIV;
    }
    function yy_r2_59($yy_subpatterns)
    {

    $this->token = HG_Parser::T_STRING_SINGLE_INIT;
    $this->yypushstate(self::IN_STRING_SINGLE);
    }
    function yy_r2_60($yy_subpatterns)
    {

    $this->token = HG_Parser::T_STRING_DOUBLE_INIT;
    $this->yypushstate(self::IN_STRING_DOUBLE);
    }
    function yy_r2_61($yy_subpatterns)
    {

    $this->token = HG_Parser::T_CUSTOM_END;
    }
    function yy_r2_63($yy_subpatterns)
    {

    $this->token = HG_Parser::T_EXTENDS;
    }
    function yy_r2_64($yy_subpatterns)
    {

    $this->token = HG_Parser::T_INCLUDE;
    }
    function yy_r2_65($yy_subpatterns)
    {

    $this->token = HG_Parser::T_NUMERIC;
    }
    function yy_r2_67($yy_subpatterns)
    {

    $this->token = HG_Parser::T_NUMERIC;
    }
    function yy_r2_70($yy_subpatterns)
    {

    $this->is_custom_tag();
    }
    function yy_r2_72($yy_subpatterns)
    {

    return FALSE;
    }


    function yylex3()
    {
        $tokenMap = array (
              1 => 0,
              2 => 0,
              3 => 0,
              4 => 0,
              5 => 0,
              6 => 0,
              7 => 0,
              8 => 1,
              10 => 2,
              13 => 0,
              14 => 0,
              15 => 1,
              17 => 0,
            );
        if ($this->N >= strlen($this->data)) {
            return false; // end of input
        }
        $yy_global_pattern = "/^(\\}\\})|^(\\|)|^(:)|^(->)|^(\\.)|^(\\[)|^(\\])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(')|^(\")|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)/";

        do {
            if (preg_match($yy_global_pattern, substr($this->data, $this->N), $yymatches)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        'an empty string.  Input "' . substr($this->data,
                        $this->N, 5) . '... state IN_PRINT');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r3_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    if ($this->N >= strlen($this->data)) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                } else {                    $yy_yymore_patterns = array(
        1 => array(0, "^(\\|)|^(:)|^(->)|^(\\.)|^(\\[)|^(\\])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(')|^(\")|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        2 => array(0, "^(:)|^(->)|^(\\.)|^(\\[)|^(\\])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(')|^(\")|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        3 => array(0, "^(->)|^(\\.)|^(\\[)|^(\\])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(')|^(\")|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        4 => array(0, "^(\\.)|^(\\[)|^(\\])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(')|^(\")|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        5 => array(0, "^(\\[)|^(\\])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(')|^(\")|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        6 => array(0, "^(\\])|^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(')|^(\")|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        7 => array(0, "^([0-9]+(\\.[0-9]+)?)|^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(')|^(\")|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        8 => array(1, "^([0-9]+(\\.[0-9]+)?\\.[0-9]+(\\.[0-9]+)?)|^(')|^(\")|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        10 => array(3, "^(')|^(\")|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        13 => array(3, "^(\")|^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        14 => array(3, "^(([a-zA-Z_][a-zA-Z_0-9]*))|^([ \r\t\n]+)"),
        15 => array(4, "^([ \r\t\n]+)"),
        17 => array(4, ""),
    );

                    // yymore is needed
                    do {
                        if (!strlen($yy_yymore_patterns[$this->token][1])) {
                            throw new Exception('cannot do yymore for the last token');
                        }
                        $yysubmatches = array();
                        if (preg_match('/' . $yy_yymore_patterns[$this->token][1] . '/',
                              substr($this->data, $this->N), $yymatches)) {
                            $yysubmatches = $yymatches;
                            $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                            next($yymatches); // skip global match
                            $this->token += key($yymatches) + $yy_yymore_patterns[$this->token][0]; // token number
                            $this->value = current($yymatches); // token value
                            $this->line = substr_count($this->value, "\n");
                            if ($tokenMap[$this->token]) {
                                // extract sub-patterns for passing to lex function
                                $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                                    $tokenMap[$this->token]);
                            } else {
                                $yysubmatches = array();
                            }
                        }
                    	$r = $this->{'yy_r3_' . $this->token}($yysubmatches);
                    } while ($r !== null && !is_bool($r));
			        if ($r === true) {
			            // we have changed state
			            // process this token in the new state
			            return $this->yylex();
                    } elseif ($r === false) {
                        $this->N += strlen($this->value);
                        $this->line += substr_count($this->value, "\n");
                        if ($this->N >= strlen($this->data)) {
                            return false; // end of input
                        }
                        // skip this token
                        continue;
			        } else {
	                    // accept
	                    $this->N += strlen($this->value);
	                    $this->line += substr_count($this->value, "\n");
	                    return true;
			        }
                }
            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->N]);
            }
            break;
        } while (true);

    } // end function


    const IN_PRINT = 3;
    function yy_r3_1($yy_subpatterns)
    {

    $this->token = HG_Parser::T_PRINT_CLOSE;
    $this->yypopstate();
    }
    function yy_r3_2($yy_subpatterns)
    {

    $this->token = HG_Parser::T_PIPE;
    }
    function yy_r3_3($yy_subpatterns)
    {

    $this->token = HG_Parser::T_COLON;
    }
    function yy_r3_4($yy_subpatterns)
    {

    $this->token = HG_Parser::T_OBJ;
    }
    function yy_r3_5($yy_subpatterns)
    {

    $this->token = HG_Parser::T_DOT;
    }
    function yy_r3_6($yy_subpatterns)
    {

    $this->token = HG_Parser::T_BRACKETS_OPEN;
    }
    function yy_r3_7($yy_subpatterns)
    {

    $this->token = HG_Parser::T_BRACKETS_CLOSE;
    }
    function yy_r3_8($yy_subpatterns)
    {

    $this->token = HG_Parser::T_NUMERIC;
    }
    function yy_r3_10($yy_subpatterns)
    {

    $this->token = HG_Parser::T_NUMERIC;
    }
    function yy_r3_13($yy_subpatterns)
    {

    $this->token = HG_Parser::T_STRING_SINGLE_INIT;
    $this->yypushstate(self::IN_STRING_SINGLE);
    }
    function yy_r3_14($yy_subpatterns)
    {

    $this->token = HG_Parser::T_STRING_DOUBLE_INIT;
    $this->yypushstate(self::IN_STRING_DOUBLE);
    }
    function yy_r3_15($yy_subpatterns)
    {

    $this->token = HG_Parser::T_ALPHA;
    }
    function yy_r3_17($yy_subpatterns)
    {

    return FALSE;
    }



    function yylex4()
    {
        $tokenMap = array (
              1 => 0,
              2 => 0,
              3 => 0,
              4 => 0,
            );
        if ($this->N >= strlen($this->data)) {
            return false; // end of input
        }
        $yy_global_pattern = "/^(\\\\\")|^(\\\\')|^(\")|^([^\"\\\\]+)/";

        do {
            if (preg_match($yy_global_pattern, substr($this->data, $this->N), $yymatches)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        'an empty string.  Input "' . substr($this->data,
                        $this->N, 5) . '... state IN_STRING_DOUBLE');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r4_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    if ($this->N >= strlen($this->data)) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                } else {                    $yy_yymore_patterns = array(
        1 => array(0, "^(\\\\')|^(\")|^([^\"\\\\]+)"),
        2 => array(0, "^(\")|^([^\"\\\\]+)"),
        3 => array(0, "^([^\"\\\\]+)"),
        4 => array(0, ""),
    );

                    // yymore is needed
                    do {
                        if (!strlen($yy_yymore_patterns[$this->token][1])) {
                            throw new Exception('cannot do yymore for the last token');
                        }
                        $yysubmatches = array();
                        if (preg_match('/' . $yy_yymore_patterns[$this->token][1] . '/',
                              substr($this->data, $this->N), $yymatches)) {
                            $yysubmatches = $yymatches;
                            $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                            next($yymatches); // skip global match
                            $this->token += key($yymatches) + $yy_yymore_patterns[$this->token][0]; // token number
                            $this->value = current($yymatches); // token value
                            $this->line = substr_count($this->value, "\n");
                            if ($tokenMap[$this->token]) {
                                // extract sub-patterns for passing to lex function
                                $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                                    $tokenMap[$this->token]);
                            } else {
                                $yysubmatches = array();
                            }
                        }
                    	$r = $this->{'yy_r4_' . $this->token}($yysubmatches);
                    } while ($r !== null && !is_bool($r));
			        if ($r === true) {
			            // we have changed state
			            // process this token in the new state
			            return $this->yylex();
                    } elseif ($r === false) {
                        $this->N += strlen($this->value);
                        $this->line += substr_count($this->value, "\n");
                        if ($this->N >= strlen($this->data)) {
                            return false; // end of input
                        }
                        // skip this token
                        continue;
			        } else {
	                    // accept
	                    $this->N += strlen($this->value);
	                    $this->line += substr_count($this->value, "\n");
	                    return true;
			        }
                }
            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->N]);
            }
            break;
        } while (true);

    } // end function


    const IN_STRING_DOUBLE = 4;
    function yy_r4_1($yy_subpatterns)
    {

    $this->token = HG_Parser::T_STRING_CONTENT;
    $this->value = "\"";
    $this->N    += 1;
    }
    function yy_r4_2($yy_subpatterns)
    {

    $this->token = HG_Parser::T_STRING_CONTENT;
    $this->value = "'";
    $this->N    += 1;
    }
    function yy_r4_3($yy_subpatterns)
    {

    $this->token = HG_Parser::T_STRING_DOUBLE_END;
    $this->yypopstate();
    }
    function yy_r4_4($yy_subpatterns)
    {

    $this->token = HG_Parser::T_STRING_CONTENT;
    }



    function yylex5()
    {
        $tokenMap = array (
              1 => 0,
              2 => 0,
              3 => 0,
              4 => 0,
            );
        if ($this->N >= strlen($this->data)) {
            return false; // end of input
        }
        $yy_global_pattern = "/^(\\\\')|^(\\\\\")|^(')|^([^'\\\\]+)/";

        do {
            if (preg_match($yy_global_pattern, substr($this->data, $this->N), $yymatches)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        'an empty string.  Input "' . substr($this->data,
                        $this->N, 5) . '... state IN_STRING_SINGLE');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r5_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    if ($this->N >= strlen($this->data)) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                } else {                    $yy_yymore_patterns = array(
        1 => array(0, "^(\\\\\")|^(')|^([^'\\\\]+)"),
        2 => array(0, "^(')|^([^'\\\\]+)"),
        3 => array(0, "^([^'\\\\]+)"),
        4 => array(0, ""),
    );

                    // yymore is needed
                    do {
                        if (!strlen($yy_yymore_patterns[$this->token][1])) {
                            throw new Exception('cannot do yymore for the last token');
                        }
                        $yysubmatches = array();
                        if (preg_match('/' . $yy_yymore_patterns[$this->token][1] . '/',
                              substr($this->data, $this->N), $yymatches)) {
                            $yysubmatches = $yymatches;
                            $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                            next($yymatches); // skip global match
                            $this->token += key($yymatches) + $yy_yymore_patterns[$this->token][0]; // token number
                            $this->value = current($yymatches); // token value
                            $this->line = substr_count($this->value, "\n");
                            if ($tokenMap[$this->token]) {
                                // extract sub-patterns for passing to lex function
                                $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                                    $tokenMap[$this->token]);
                            } else {
                                $yysubmatches = array();
                            }
                        }
                    	$r = $this->{'yy_r5_' . $this->token}($yysubmatches);
                    } while ($r !== null && !is_bool($r));
			        if ($r === true) {
			            // we have changed state
			            // process this token in the new state
			            return $this->yylex();
                    } elseif ($r === false) {
                        $this->N += strlen($this->value);
                        $this->line += substr_count($this->value, "\n");
                        if ($this->N >= strlen($this->data)) {
                            return false; // end of input
                        }
                        // skip this token
                        continue;
			        } else {
	                    // accept
	                    $this->N += strlen($this->value);
	                    $this->line += substr_count($this->value, "\n");
	                    return true;
			        }
                }
            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->N]);
            }
            break;
        } while (true);

    } // end function


    const IN_STRING_SINGLE = 5;
    function yy_r5_1($yy_subpatterns)
    {

    $this->token = HG_Parser::T_STRING_CONTENT;
    $this->value = "'";
    $this->N    += 1;
    }
    function yy_r5_2($yy_subpatterns)
    {

    $this->token = HG_Parser::T_STRING_CONTENT;
    $this->value = "\"";
    $this->N    += 1;
    }
    function yy_r5_3($yy_subpatterns)
    {

    $this->token = HG_Parser::T_STRING_SINGLE_END;
    $this->yypopstate();
    }
    function yy_r5_4($yy_subpatterns)
    {

    $this->token = HG_Parser::T_STRING_CONTENT;
    }



    function yylex6()
    {
        $tokenMap = array (
              1 => 1,
            );
        if ($this->N >= strlen($this->data)) {
            return false; // end of input
        }
        $yy_global_pattern = "/^(([^#]+#\\})+)/";

        do {
            if (preg_match($yy_global_pattern, substr($this->data, $this->N), $yymatches)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        'an empty string.  Input "' . substr($this->data,
                        $this->N, 5) . '... state IN_COMMENT');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r6_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    if ($this->N >= strlen($this->data)) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                } else {                    $yy_yymore_patterns = array(
        1 => array(0, ""),
    );

                    // yymore is needed
                    do {
                        if (!strlen($yy_yymore_patterns[$this->token][1])) {
                            throw new Exception('cannot do yymore for the last token');
                        }
                        $yysubmatches = array();
                        if (preg_match('/' . $yy_yymore_patterns[$this->token][1] . '/',
                              substr($this->data, $this->N), $yymatches)) {
                            $yysubmatches = $yymatches;
                            $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                            next($yymatches); // skip global match
                            $this->token += key($yymatches) + $yy_yymore_patterns[$this->token][0]; // token number
                            $this->value = current($yymatches); // token value
                            $this->line = substr_count($this->value, "\n");
                            if ($tokenMap[$this->token]) {
                                // extract sub-patterns for passing to lex function
                                $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                                    $tokenMap[$this->token]);
                            } else {
                                $yysubmatches = array();
                            }
                        }
                    	$r = $this->{'yy_r6_' . $this->token}($yysubmatches);
                    } while ($r !== null && !is_bool($r));
			        if ($r === true) {
			            // we have changed state
			            // process this token in the new state
			            return $this->yylex();
                    } elseif ($r === false) {
                        $this->N += strlen($this->value);
                        $this->line += substr_count($this->value, "\n");
                        if ($this->N >= strlen($this->data)) {
                            return false; // end of input
                        }
                        // skip this token
                        continue;
			        } else {
	                    // accept
	                    $this->N += strlen($this->value);
	                    $this->line += substr_count($this->value, "\n");
	                    return true;
			        }
                }
            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->N]);
            }
            break;
        } while (true);

    } // end function


    const IN_COMMENT = 6;
    function yy_r6_1($yy_subpatterns)
    {

    $this->token = HG_Parser::T_COMMENT;
    $this->yypopstate();
    }

}
