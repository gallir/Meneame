%name Haanga_
%include {
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
}

%declare_class { class Haanga_Compiler_Parser }
%include_class {
    protected $lex;
    protected $file;

    function __construct($lex, $file='')
    {
        $this->lex  = $lex;
        $this->file = $file;
    }

    function Error($text)
    {
        throw new Haanga_Compiler_Exception($text.' in '.$this->file.':'.$this->lex->getLine());
    }

}

%parse_accept {
}

%right T_OPEN_TAG.
%right T_NOT.
%left T_AND.
%left T_OR.
%nonassoc T_EQ T_NE.
%nonassoc T_GT T_GE T_LT T_LE.
%nonassoc T_IN.
%left T_PLUS T_MINUS.
%left T_TIMES T_DIV T_MOD.

%syntax_error {
    $expect = array();
    foreach ($this->yy_get_expected_tokens($yymajor) as $token) {
        $expect[] = self::$yyTokenName[$token];
    }
    $this->Error('Unexpected ' . $this->tokenName($yymajor) . '(' . $TOKEN. '), expected one of: ' . implode(',', $expect));
}


start ::= body(B). { $this->body = B; }

body(A) ::= body(B) code(C). { A=B; A[] = C; }
body(A) ::= . { A = array(); }

/* List of statements */
code(A) ::= T_OPEN_TAG stmts(B). { if (count(B)) B['line'] = $this->lex->getLine();  A = B; }
code(A) ::= T_HTML(B). { A = array('operation' => 'html', 'html' => B, 'line' => $this->lex->getLine() ); }
code(A) ::= T_COMMENT_OPEN T_COMMENT(B). { B=rtrim(B); A = array('operation' => 'comment', 'comment' => substr(B, 0, strlen(B)-2)); } 
code(A) ::= T_PRINT_OPEN filtered_var(B) T_PRINT_CLOSE.  { A = array('operation' => 'print_var', 'variable' => B, 'line' => $this->lex->getLine() ); }

stmts(A) ::= T_EXTENDS var_or_string(B) T_CLOSE_TAG. { A = array('operation' => 'base', B); }
stmts(A) ::= stmt(B) T_CLOSE_TAG. { A = B; }
stmts(A) ::= for_stmt(B). { A = B; }
stmts(A) ::= ifchanged_stmt(B). { A = B; }
stmts(A) ::= block_stmt(B). { A = B; }
stmts(A) ::= filter_stmt(B). { A = B; }
stmts(A) ::= if_stmt(B). { A = B; }
stmts(A) ::= T_INCLUDE var_or_string(B) T_CLOSE_TAG. { A = array('operation' => 'include', B); }
stmts(A) ::= custom_tag(B). { A = B; }
stmts(A) ::= alias(B). { A = B; }
stmts(A) ::= ifequal(B). { A = B; }
stmts(A) ::= T_AUTOESCAPE T_OFF|T_ON(B) T_CLOSE_TAG body(X) T_OPEN_TAG T_END_AUTOESCAPE T_CLOSE_TAG. { A = array('operation' => 'autoescape', 'value' => strtolower(@B), 'body' => X); }

/* Statement */

/* CUSTOM TAGS */
custom_tag(A) ::= T_CUSTOM_TAG(B) T_CLOSE_TAG. { A = array('operation' => 'custom_tag', 'name' => B, 'list'=>array()); }
custom_tag(A) ::= T_CUSTOM_TAG(B) T_AS varname(C) T_CLOSE_TAG. { A = array('operation' => 'custom_tag', 'name' => B, 'as' => C, 'list'=>array()); }
custom_tag(A) ::= T_CUSTOM_TAG(B) var_list(X) T_CLOSE_TAG. { A = array('operation' => 'custom_tag', 'name' => B, 'list' => X); }
custom_tag(A) ::= T_CUSTOM_TAG(B) var_list(X) T_AS varname(C) T_CLOSE_TAG. { A = array('operation' => 'custom_tag', 'name' => B, 'as' => C, 'list' => X); }
/* tags as blocks */
custom_tag(A) ::= T_CUSTOM_BLOCK(B) T_CLOSE_TAG body(X) T_OPEN_TAG T_CUSTOM_END(C) T_CLOSE_TAG. { if ('end'.B != C) { $this->error("Unexpected ".C); } A = array('operation' => 'custom_tag', 'name' => B, 'body' => X, 'list' => array());}
custom_tag(A) ::= T_BUFFER varname(Y) T_CLOSE_TAG body(X) T_OPEN_TAG T_CUSTOM_END(C) T_CLOSE_TAG. { if ('endbuffer' != C) { $this->error("Unexpected ".C); } A = array('operation' => 'buffer', 'name' => Y, 'body' => X);}
custom_tag(A) ::= T_SPACEFULL T_CLOSE_TAG body(X) T_OPEN_TAG T_CUSTOM_END(C) T_CLOSE_TAG. { if ('endspacefull' != C) { $this->error("Unexpected ".C); } A = array('operation' => 'spacefull', 'body' => X);}

/* variable alias */
alias(A) ::= T_WITH varname(B) T_AS varname(C) T_CLOSE_TAG body(X) T_OPEN_TAG T_ENDWITH T_CLOSE_TAG. { A = array('operation' => 'alias', 'var' => B, 'as' => C, 'body' => X); }

/* Simple statements (don't require a end_tag or a body ) */
stmt(A) ::= regroup(B). { A = B; }
stmt ::= T_LOAD string(B). {
    if (!is_file(B)) {
        $this->error(B." is not a valid file"); 
    } 
    require_once B;
}

/* FOR loop */
for_def(A) ::= T_FOR varname(B) T_IN filtered_var(C) T_CLOSE_TAG . {
    /* Try to get the variable */
    $var = $this->compiler->get_context(is_array(C[0]) ? C[0] : array(C[0]));
    if (is_array($var)) {
        /* let's check if it is an object or array */
        $this->compiler->set_context(B, current($var));
    }

    A = array('operation' => 'loop', 'variable' => B, 'index' => NULL, 'array' => C);
}

for_def(A) ::= T_FOR varname(I) T_COMMA varname(B) T_IN filtered_var(C) T_CLOSE_TAG . {
    /* Try to get the variable */
    $var = $this->compiler->get_context(is_array(C[0]) ? C[0] : array(C[0]));
    if (is_array($var)) {
        /* let's check if it is an object or array */
        $this->compiler->set_context(B, current($var));
    }
    A = array('operation' => 'loop', 'variable' => B, 'index' => I, 'array' => C);
}


for_stmt(A) ::= for_def(B) body(D) T_OPEN_TAG T_CLOSEFOR T_CLOSE_TAG. { 
    A = B;
    A['body'] = D;
}

for_stmt(A) ::= for_def(B) body(D) T_OPEN_TAG T_EMPTY T_CLOSE_TAG body(E)  T_OPEN_TAG T_CLOSEFOR T_CLOSE_TAG. { 
    A = B;
    A['body']  = D;
    A['empty'] = E;
}
/* IF */
if_stmt(A) ::= T_IF expr(B) T_CLOSE_TAG body(X) T_OPEN_TAG T_ENDIF T_CLOSE_TAG. { A = array('operation' => 'if', 'expr' => B, 'body' => X); }
if_stmt(A) ::= T_IF expr(B) T_CLOSE_TAG body(X) T_OPEN_TAG T_ELSE T_CLOSE_TAG body(Y) T_OPEN_TAG T_ENDIF  T_CLOSE_TAG. { A = array('operation' => 'if', 'expr' => B, 'body' => X, 'else' => Y); }

/* ifchanged */
ifchanged_stmt(A) ::= T_IFCHANGED T_CLOSE_TAG body(B) T_OPEN_TAG T_ENDIFCHANGED T_CLOSE_TAG. { 
    A = array('operation' => 'ifchanged', 'body' => B); 
}

ifchanged_stmt(A) ::= T_IFCHANGED var_list(X) T_CLOSE_TAG body(B) T_OPEN_TAG T_ENDIFCHANGED T_CLOSE_TAG. { 
    A = array('operation' => 'ifchanged', 'body' => B, 'check' => X);
}
ifchanged_stmt(A) ::= T_IFCHANGED T_CLOSE_TAG body(B) T_OPEN_TAG T_ELSE T_CLOSE_TAG body(C) T_OPEN_TAG T_ENDIFCHANGED T_CLOSE_TAG. { 
    A = array('operation' => 'ifchanged', 'body' => B, 'else' => C); 
}

ifchanged_stmt(A) ::= T_IFCHANGED var_list(X) T_CLOSE_TAG body(B) T_OPEN_TAG T_ELSE T_CLOSE_TAG body(C) T_OPEN_TAG T_ENDIFCHANGED T_CLOSE_TAG. { 
    A = array('operation' => 'ifchanged', 'body' => B, 'check' => X, 'else' => C);
}

/* ifequal */
ifequal(A) ::= T_IFEQUAL fvar_or_string(B) fvar_or_string(C) T_CLOSE_TAG body(X) T_OPEN_TAG T_END_IFEQUAL T_CLOSE_TAG. {  A = array('operation' => 'ifequal', 'cmp' => '==', 1 => B, 2 => C, 'body' => X); }
ifequal(A) ::= T_IFEQUAL fvar_or_string(B) fvar_or_string(C) T_CLOSE_TAG body(X) T_OPEN_TAG T_ELSE T_CLOSE_TAG body(Y) T_OPEN_TAG T_END_IFEQUAL T_CLOSE_TAG. {  A = array('operation' => 'ifequal', 'cmp' => '==', 1 => B, 2 => C, 'body' => X, 'else' => Y); }
ifequal(A) ::= T_IFNOTEQUAL fvar_or_string(B) fvar_or_string(C) T_CLOSE_TAG body(X) T_OPEN_TAG T_END_IFNOTEQUAL T_CLOSE_TAG. {  A = array('operation' => 'ifequal', 'cmp' => '!=', 1 => B, 2 => C, 'body' => X); }
ifequal(A) ::= T_IFNOTEQUAL fvar_or_string(B) fvar_or_string(C) T_CLOSE_TAG body(X) T_OPEN_TAG T_ELSE T_CLOSE_TAG body(Y) T_OPEN_TAG T_END_IFNOTEQUAL T_CLOSE_TAG. {  A = array('operation' => 'ifequal', 'cmp' => '!=', 1 => B, 2 => C, 'body' => X, 'else' => Y); }

 
/* block stmt */
block_stmt(A) ::= T_BLOCK varname(B) T_CLOSE_TAG body(C) T_OPEN_TAG T_END_BLOCK T_CLOSE_TAG. { A = array('operation' => 'block', 'name' => B, 'body' => C); }

block_stmt(A) ::= T_BLOCK varname(B) T_CLOSE_TAG body(C) T_OPEN_TAG T_END_BLOCK varname T_CLOSE_TAG. { A = array('operation' => 'block', 'name' => B, 'body' => C); }

block_stmt(A) ::= T_BLOCK T_NUMERIC(B) T_CLOSE_TAG body(C) T_OPEN_TAG T_END_BLOCK T_CLOSE_TAG. { A = array('operation' => 'block', 'name' => B, 'body' => C); }

block_stmt(A) ::= T_BLOCK T_NUMERIC(B) T_CLOSE_TAG body(C) T_OPEN_TAG T_END_BLOCK T_NUMERIC T_CLOSE_TAG. { A = array('operation' => 'block', 'name' => B, 'body' => C); }

/* filter stmt */
filter_stmt(A) ::= T_FILTER filtered_var(B) T_CLOSE_TAG body(X) T_OPEN_TAG T_END_FILTER T_CLOSE_TAG. { A = array('operation' => 'filter', 'functions' => B, 'body' => X); }

/* regroup stmt */
regroup(A) ::= T_REGROUP filtered_var(B) T_BY varname(C) T_AS varname(X). { A=array('operation' => 'regroup', 'array' => B, 'row' => C, 'as' => X); }

/* variables with filters */
filtered_var(A) ::= filtered_var(B) T_PIPE varname_args(C). { A = B; A[] = C; }
filtered_var(A) ::= varname_args(B). { A = array(B); }

varname_args(A) ::= varname(B) T_COLON var_or_string(X) . { A = array(B, 'args'=>array(X)); }
varname_args(A) ::= varname(B). { A = B; }

/* List of variables */
var_list(A) ::= var_list(B) var_or_string(C).           { A = B; A[] = C; }
var_list(A) ::= var_list(B) T_COMMA var_or_string(C).   { A = B; A[] = C; }
var_list(A) ::= var_or_string(B).                       { A = array(B); }


/* variable or string (used on var_list) */
var_or_string(A) ::= varname(B).    { A = array('var' => B); }  
var_or_string(A) ::= T_NUMERIC(B).  { A = array('number' => B); }  
var_or_string(A) ::= string(B).     { A = array('string' => B); }

fvar_or_string(A) ::= filtered_var(B).  { A = array('var_filter' => B); }  
fvar_or_string(A) ::= T_NUMERIC(B).     { A = array('number' => B); }  
fvar_or_string(A) ::= T_TRUE|T_FALSE(B).   { A = trim(@B); }  
fvar_or_string(A) ::= string(B).        { A = array('string' => B); }

/* */
string(A)    ::= T_INTL string(B) T_RPARENT.      { A = B; }
string(A)    ::= T_STRING_SINGLE_INIT  T_STRING_SINGLE_END. {  A = ""; }
string(A)    ::= T_STRING_DOUBLE_INIT  T_STRING_DOUBLE_END. {  A = ""; }
string(A)    ::= T_STRING_SINGLE_INIT s_content(B)  T_STRING_SINGLE_END. {  A = B; }
string(A)    ::= T_STRING_DOUBLE_INIT s_content(B)  T_STRING_DOUBLE_END. {  A = B; }
s_content(A) ::= s_content(B) T_STRING_CONTENT(C). { A = B.C; }
s_content(A) ::= T_STRING_CONTENT(B). { A = B; }

/* expr */
expr(A) ::= T_NOT expr(B). { A = array('op_expr' => 'not', B); }
expr(A) ::= expr(B) T_AND(X)  expr(C).  { A = array('op_expr' => @X, B, C); }
expr(A) ::= expr(B) T_OR(X)  expr(C).  { A = array('op_expr' => @X, B, C); }
expr(A) ::= expr(B) T_PLUS|T_MINUS(X)  expr(C).  { A = array('op_expr' => @X, B, C); }
expr(A) ::= expr(B) T_EQ|T_NE|T_GT|T_GE|T_LT|T_LE|T_IN(X)  expr(C).  { A = array('op_expr' => trim(@X), B, C); }
expr(A) ::= expr(B) T_TIMES|T_DIV|T_MOD(X)  expr(C).  { A = array('op_expr' => @X, B, C); }
expr(A) ::= T_LPARENT expr(B) T_RPARENT. { A = array('op_expr' => 'expr', B); }
expr(A) ::= fvar_or_string(B). { A = B; }

/* Variable name */
varname(A) ::= varname(B) T_OBJ T_ALPHA(C). { if (!is_array(B)) { A = array(B); } else { A = B; }  A[]=array('object' => C);}
varname(A) ::= varname(B) T_DOT T_ALPHA(C). { if (!is_array(B)) { A = array(B); } else { A = B; } A[] = ($this->compiler->var_is_object(A)) ? array('object' => C) : C;}
varname(A) ::= varname(B) T_BRACKETS_OPEN var_or_string(C) T_BRACKETS_CLOSE. { if (!is_array(B)) { A = array(B); } else { A = B; }  A[]=C;}
varname(A) ::= T_ALPHA(B). { A = B; } 
/* T_CUSTOM|T_CUSTOM_BLOCK are also T_ALPHA */
varname(A) ::= T_CUSTOM_TAG|T_CUSTOM_BLOCK(B). { A = B; } 
