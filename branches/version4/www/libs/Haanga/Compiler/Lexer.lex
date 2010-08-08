<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2010 Haanga                                                       |
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

    function init($template, $compiler)
    {
        $lexer  = new Haanga_Compiler_Lexer($template, $compiler);
        $parser = new Haanga_Compiler_Parser($lexer);

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

/*!lex2php
%input          $this->data
%counter        $this->N
%token          $this->token
%value          $this->value
%line           $this->line
alpha           = /([a-zA-Z_][a-zA-Z_0-9]*)/
number          = /[0-9]/
numerals        = /([0-9])+/
whitespace      = /[ \r\t\n]+/
html            = /([^{]+(.[^%{#])?)+/
comment         = /([^\#]+\#\})+/
custom_tag_end  = /end([a-zA-Z][a-zA-Z0-9]*)/
token_end       = /[^a-zA-Z0-9_\.]/
single_string   = /[^'\\]+/
double_string   = /[^"\\]+/
*/
/*!lex2php
%statename IN_HTML
"{%" {
    $this->token = HG_Parser::T_OPEN_TAG;
    $this->yypushstate(self::IN_CODE);
}

"{#" {
    $this->token = HG_Parser::T_COMMENT_OPEN;
    $this->yypushstate(self::IN_COMMENT);
}


"{{" {
    $this->token = HG_Parser::T_PRINT_OPEN;
    $this->yypushstate(self::IN_PRINT);
}

html {
    $this->token = HG_Parser::T_HTML;
}

*/
/*!lex2php
%statename IN_CODE
"%}" {
    $this->token = HG_Parser::T_CLOSE_TAG;
    $this->yypopstate();
}

"->" {
    $this->token = HG_Parser::T_OBJ;
}


"." {
    $this->token = HG_Parser::T_DOT;
}

"buffer" token_end {
    $this->token = HG_Parser::T_BUFFER;
}


"for" token_end {
    $this->token = HG_Parser::T_FOR;
}

"empty" token_end {
    $this->token = HG_Parser::T_EMPTY;
}

"load" token_end {
    $this->token = HG_Parser::T_LOAD;
}

"block" token_end {
    $this->token = HG_Parser::T_BLOCK;
}

"&&" { 
    $this->token = HG_Parser::T_AND;
}

"AND" token_end {
    $this->token = HG_Parser::T_AND;
}

"||" {
    $this->token = HG_Parser::T_OR;
}

"OR" token_end {
    $this->token = HG_Parser::T_OR;
}

"==" {
    $this->token = HG_Parser::T_EQ;
}

"!=" {
    $this->token = HG_Parser::T_NE;
}

">=" {
    $this->token = HG_Parser::T_GE;
}

"not" token_end {
    $this->token = HG_Parser::T_NOT;
}
    
"!" token_end {
    $this->token = HG_Parser::T_NOT;
}
    

"[" {
    $this->token = HG_Parser::T_BRACKETS_OPEN;
}

"]" {
    $this->token = HG_Parser::T_BRACKETS_CLOSE;
}

">" {
    $this->token = HG_Parser::T_GT;
}

"<" {
    $this->token = HG_Parser::T_LT;
}
"=<" {
    $this->token = HG_Parser::T_LE;
}

"|" {
    $this->token = HG_Parser::T_PIPE;
}

":" {
    $this->token = HG_Parser::T_COLON;
}

"filter" token_end {
    $this->token = HG_Parser::T_FILTER;
}

"regroup" token_end {
    $this->token = HG_Parser::T_REGROUP;
}

"endfilter" token_end {
    $this->token = HG_Parser::T_END_FILTER;
}

"autoescape" token_end {
    $this->token = HG_Parser::T_AUTOESCAPE;
}

"spacefull" token_end {
    $this->token = HG_Parser::T_SPACEFULL;
}


"endautoescape" token_end {
    $this->token = HG_Parser::T_END_AUTOESCAPE;
}


"endblock" token_end {
    $this->token = HG_Parser::T_END_BLOCK;
}

"ifchanged" token_end {
    $this->token = HG_Parser::T_IFCHANGED;
}

"ifequal" token_end {
    $this->token = HG_Parser::T_IFEQUAL;
}

"endifequal" token_end {
    $this->token = HG_Parser::T_END_IFEQUAL;
}

"ifnotequal" token_end {
    $this->token = HG_Parser::T_IFNOTEQUAL;
}

"endifnotequal" token_end {
    $this->token = HG_Parser::T_END_IFNOTEQUAL;
}


"else" token_end {
    $this->token = HG_Parser::T_ELSE;
}

"endifchanged" token_end {
    $this->token = HG_Parser::T_ENDIFCHANGED;
}


"in" token_end {
    $this->token = HG_Parser::T_IN;
}

"endfor" token_end {
    $this->token = HG_Parser::T_CLOSEFOR;
}

"with" token_end {
    $this->token = HG_Parser::T_WITH;
}

"endwith" token_end {
    $this->token = HG_Parser::T_ENDWITH;
}

"as" {
    $this->token = HG_Parser::T_AS;
}

"on" {
    $this->token = HG_Parser::T_ON;
}

"off" {
    $this->token = HG_Parser::T_OFF;
}

"by" {
    $this->token = HG_Parser::T_BY;
}

"if" token_end {
    $this->token = HG_Parser::T_IF;
} 

"else" token_end {
    $this->token = HG_Parser::T_ELSE;
}

"endif" token_end {
    $this->token = HG_Parser::T_ENDIF;
}

"_("  {
    $this->token = HG_Parser::T_INTL;
}


"(" {
    $this->token = HG_Parser::T_LPARENT;
}

")" {
    $this->token = HG_Parser::T_RPARENT;
}

"%" {
    $this->token = HG_Parser::T_MOD;
}

"," {
    $this->token = HG_Parser::T_COMMA;
}

"+" {
    $this->token = HG_Parser::T_PLUS;
}
"-" {
    $this->token = HG_Parser::T_MINUS;
}
"*" {
    $this->token = HG_Parser::T_TIMES;
}

"/" {
    $this->token = HG_Parser::T_DIV;
}

"'" {
    $this->token = HG_Parser::T_STRING_SINGLE_INIT;
    $this->yypushstate(self::IN_STRING_SINGLE);
}

"\"" {
    $this->token = HG_Parser::T_STRING_DOUBLE_INIT;
    $this->yypushstate(self::IN_STRING_DOUBLE);
}

custom_tag_end {
    $this->token = HG_Parser::T_CUSTOM_END;
}

"extends" token_end {
    $this->token = HG_Parser::T_EXTENDS;
}

"include" token_end {
    $this->token = HG_Parser::T_INCLUDE;
}

numerals {
    $this->token = HG_Parser::T_NUMERIC;
}

numerals "."  numerals {
    $this->token = HG_Parser::T_NUMERIC;
}

alpha  {
    $this->is_custom_tag();
}

whitespace {
    return FALSE;
}

*/
/*!lex2php
%statename IN_PRINT
"}}" {
    $this->token = HG_Parser::T_PRINT_CLOSE;
    $this->yypopstate();
}

"|" {
    $this->token = HG_Parser::T_PIPE;
}

":" {
    $this->token = HG_Parser::T_COLON;
}

"->" {
    $this->token = HG_Parser::T_OBJ;
}


"." {
    $this->token = HG_Parser::T_DOT;
}

"[" {
    $this->token = HG_Parser::T_BRACKETS_OPEN;
}

"]" {
    $this->token = HG_Parser::T_BRACKETS_CLOSE;
}

numerals {
    $this->token = HG_Parser::T_NUMERIC;
}

numerals "."  numerals {
    $this->token = HG_Parser::T_NUMERIC;
}

"'" {
    $this->token = HG_Parser::T_STRING_SINGLE_INIT;
    $this->yypushstate(self::IN_STRING_SINGLE);
}

"\"" {
    $this->token = HG_Parser::T_STRING_DOUBLE_INIT;
    $this->yypushstate(self::IN_STRING_DOUBLE);
}

alpha {
    $this->token = HG_Parser::T_ALPHA;
}

whitespace {
    return FALSE;
}
*/

/*!lex2php
%statename IN_STRING_DOUBLE

"\\" "\""  {
    $this->token = HG_Parser::T_STRING_CONTENT;
    $this->value = "\"";
    $this->N    += 1;
}

"\'"  {
    $this->token = HG_Parser::T_STRING_CONTENT;
    $this->value = "'";
    $this->N    += 1;
}


"\"" {
    $this->token = HG_Parser::T_STRING_DOUBLE_END;
    $this->yypopstate();
}

double_string {
    $this->token = HG_Parser::T_STRING_CONTENT;
}

*/

/*!lex2php
%statename IN_STRING_SINGLE
"\'"  {
    $this->token = HG_Parser::T_STRING_CONTENT;
    $this->value = "'";
    $this->N    += 1;
}

"\\" "\""  {
    $this->token = HG_Parser::T_STRING_CONTENT;
    $this->value = "\"";
    $this->N    += 1;
}


"'" {
    $this->token = HG_Parser::T_STRING_SINGLE_END;
    $this->yypopstate();
}

single_string {
    $this->token = HG_Parser::T_STRING_CONTENT;
}

*/

/*!lex2php
%statename IN_COMMENT
comment {
    $this->token = HG_Parser::T_COMMENT;
    $this->yypopstate();
}
*/
}
