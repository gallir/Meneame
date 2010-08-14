<?php
/* Driver template for the PHP_Haanga_rGenerator parser generator. (PHP port of LEMON)
*/

/**
 * This can be used to store both the string representation of
 * a token, and any useful meta-data associated with the token.
 *
 * meta-data should be stored as an array
 */
class Haanga_yyToken implements ArrayAccess
{
    public $string = '';
    public $metadata = array();

    function __construct($s, $m = array())
    {
        if ($s instanceof Haanga_yyToken) {
            $this->string = $s->string;
            $this->metadata = $s->metadata;
        } else {
            $this->string = (string) $s;
            if ($m instanceof Haanga_yyToken) {
                $this->metadata = $m->metadata;
            } elseif (is_array($m)) {
                $this->metadata = $m;
            }
        }
    }

    function __toString()
    {
        return $this->_string;
    }

    function offsetExists($offset)
    {
        return isset($this->metadata[$offset]);
    }

    function offsetGet($offset)
    {
        return $this->metadata[$offset];
    }

    function offsetSet($offset, $value)
    {
        if ($offset === null) {
            if (isset($value[0])) {
                $x = ($value instanceof Haanga_yyToken) ?
                    $value->metadata : $value;
                $this->metadata = array_merge($this->metadata, $x);
                return;
            }
            $offset = count($this->metadata);
        }
        if ($value === null) {
            return;
        }
        if ($value instanceof Haanga_yyToken) {
            if ($value->metadata) {
                $this->metadata[$offset] = $value->metadata;
            }
        } elseif ($value) {
            $this->metadata[$offset] = $value;
        }
    }

    function offsetUnset($offset)
    {
        unset($this->metadata[$offset]);
    }
}

/** The following structure represents a single element of the
 * parser's stack.  Information stored includes:
 *
 *   +  The state number for the parser at this level of the stack.
 *
 *   +  The value of the token stored at this level of the stack.
 *      (In other words, the "major" token.)
 *
 *   +  The semantic value stored at this level of the stack.  This is
 *      the information used by the action routines in the grammar.
 *      It is sometimes called the "minor" token.
 */
class Haanga_yyStackEntry
{
    public $stateno;       /* The state-number */
    public $major;         /* The major token value.  This is the code
                     ** number for the token at this stack level */
    public $minor; /* The user-supplied minor token value.  This
                     ** is the value of the token  */
};

// code external to the class is included here
#line 2 "lib/Haanga/Compiler/Parser.y"

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
#line 136 "lib/Haanga/Compiler/Parser.php"

// declare_class is output here
#line 39 "lib/Haanga/Compiler/Parser.y"
 class Haanga_Compiler_Parser #line 141 "lib/Haanga/Compiler/Parser.php"
{
/* First off, code is included which follows the "include_class" declaration
** in the input file. */
#line 40 "lib/Haanga/Compiler/Parser.y"

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

#line 162 "lib/Haanga/Compiler/Parser.php"

/* Next is all token values, as class constants
*/
/* 
** These constants (all generated automatically by the parser generator)
** specify the various kinds of tokens (terminals) that the parser
** understands. 
**
** Each symbol here is a terminal symbol in the grammar.
*/
    const T_OPEN_TAG                     =  1;
    const T_NOT                          =  2;
    const T_AND                          =  3;
    const T_OR                           =  4;
    const T_EQ                           =  5;
    const T_NE                           =  6;
    const T_GT                           =  7;
    const T_GE                           =  8;
    const T_LT                           =  9;
    const T_LE                           = 10;
    const T_IN                           = 11;
    const T_PLUS                         = 12;
    const T_MINUS                        = 13;
    const T_TIMES                        = 14;
    const T_DIV                          = 15;
    const T_MOD                          = 16;
    const T_HTML                         = 17;
    const T_COMMENT_OPEN                 = 18;
    const T_COMMENT                      = 19;
    const T_PRINT_OPEN                   = 20;
    const T_PRINT_CLOSE                  = 21;
    const T_EXTENDS                      = 22;
    const T_CLOSE_TAG                    = 23;
    const T_INCLUDE                      = 24;
    const T_AUTOESCAPE                   = 25;
    const T_OFF                          = 26;
    const T_ON                           = 27;
    const T_END_AUTOESCAPE               = 28;
    const T_CUSTOM_TAG                   = 29;
    const T_AS                           = 30;
    const T_CUSTOM_BLOCK                 = 31;
    const T_CUSTOM_END                   = 32;
    const T_BUFFER                       = 33;
    const T_SPACEFULL                    = 34;
    const T_WITH                         = 35;
    const T_ENDWITH                      = 36;
    const T_LOAD                         = 37;
    const T_FOR                          = 38;
    const T_COMMA                        = 39;
    const T_CLOSEFOR                     = 40;
    const T_EMPTY                        = 41;
    const T_IF                           = 42;
    const T_ENDIF                        = 43;
    const T_ELSE                         = 44;
    const T_IFCHANGED                    = 45;
    const T_ENDIFCHANGED                 = 46;
    const T_IFEQUAL                      = 47;
    const T_END_IFEQUAL                  = 48;
    const T_IFNOTEQUAL                   = 49;
    const T_END_IFNOTEQUAL               = 50;
    const T_BLOCK                        = 51;
    const T_END_BLOCK                    = 52;
    const T_NUMERIC                      = 53;
    const T_FILTER                       = 54;
    const T_END_FILTER                   = 55;
    const T_REGROUP                      = 56;
    const T_BY                           = 57;
    const T_PIPE                         = 58;
    const T_COLON                        = 59;
    const T_TRUE                         = 60;
    const T_FALSE                        = 61;
    const T_INTL                         = 62;
    const T_RPARENT                      = 63;
    const T_STRING_SINGLE_INIT           = 64;
    const T_STRING_SINGLE_END            = 65;
    const T_STRING_DOUBLE_INIT           = 66;
    const T_STRING_DOUBLE_END            = 67;
    const T_STRING_CONTENT               = 68;
    const T_LPARENT                      = 69;
    const T_OBJ                          = 70;
    const T_ALPHA                        = 71;
    const T_DOT                          = 72;
    const T_BRACKETS_OPEN                = 73;
    const T_BRACKETS_CLOSE               = 74;
    const YY_NO_ACTION = 331;
    const YY_ACCEPT_ACTION = 330;
    const YY_ERROR_ACTION = 329;

/* Next are that tables used to determine what action to take based on the
** current state and lookahead token.  These tables are used to implement
** functions that take a state number and lookahead value and return an
** action integer.  
**
** Suppose the action integer is N.  Then the action is determined as
** follows
**
**   0 <= N < self::YYNSTATE                              Shift N.  That is,
**                                                        push the lookahead
**                                                        token onto the stack
**                                                        and goto state N.
**
**   self::YYNSTATE <= N < self::YYNSTATE+self::YYNRULE   Reduce by rule N-YYNSTATE.
**
**   N == self::YYNSTATE+self::YYNRULE                    A syntax error has occurred.
**
**   N == self::YYNSTATE+self::YYNRULE+1                  The parser accepts its
**                                                        input. (and concludes parsing)
**
**   N == self::YYNSTATE+self::YYNRULE+2                  No such action.  Denotes unused
**                                                        slots in the yy_action[] table.
**
** The action table is constructed as a single large static array $yy_action.
** Given state S and lookahead X, the action is computed as
**
**      self::$yy_action[self::$yy_shift_ofst[S] + X ]
**
** If the index value self::$yy_shift_ofst[S]+X is out of range or if the value
** self::$yy_lookahead[self::$yy_shift_ofst[S]+X] is not equal to X or if
** self::$yy_shift_ofst[S] is equal to self::YY_SHIFT_USE_DFLT, it means that
** the action is not in the table and that self::$yy_default[S] should be used instead.  
**
** The formula above is for computing the action when the lookahead is
** a terminal symbol.  If the lookahead is a non-terminal (as occurs after
** a reduce action) then the static $yy_reduce_ofst array is used in place of
** the static $yy_shift_ofst array and self::YY_REDUCE_USE_DFLT is used in place of
** self::YY_SHIFT_USE_DFLT.
**
** The following are the tables generated in this section:
**
**  self::$yy_action        A single table containing all actions.
**  self::$yy_lookahead     A table containing the lookahead for each entry in
**                          yy_action.  Used to detect hash collisions.
**  self::$yy_shift_ofst    For each state, the offset into self::$yy_action for
**                          shifting terminals.
**  self::$yy_reduce_ofst   For each state, the offset into self::$yy_action for
**                          shifting non-terminals after a reduce.
**  self::$yy_default       Default action for each state.
*/
    const YY_SZ_ACTTAB = 1069;
static public $yy_action = array(
 /*     0 */    39,   61,   42,  128,   46,  181,  180,   34,  129,  152,
 /*    10 */   212,   79,  154,   73,  233,   72,   75,   41,   98,  118,
 /*    20 */    22,  168,  167,   35,    6,   30,  193,   31,  159,   70,
 /*    30 */   161,   38,   47,  191,   43,   39,  179,   42,  128,   87,
 /*    40 */   237,  141,   34,   45,  152,   48,   79,  154,   73,   48,
 /*    50 */    72,   75,   78,   48,   84,   22,   83,  170,   35,  169,
 /*    60 */    30,  200,   31,  159,   70,  161,   38,   47,  118,   43,
 /*    70 */    39,  123,   42,  128,  205,  202,  130,   34,  232,  152,
 /*    80 */    53,   79,  154,   73,  242,   72,   75,  118,   85,  182,
 /*    90 */    22,  182,  148,   35,  193,   30,  147,   31,  159,   70,
 /*   100 */   161,   38,   47,  204,   43,   39,  180,   42,  128,  330,
 /*   110 */    68,  186,   34,  206,  152,   48,   79,  154,   73,   90,
 /*   120 */    72,   75,   78,  158,   84,   22,   83,  145,   35,  144,
 /*   130 */    30,  176,   31,  123,   70,  240,  205,   47,  177,   43,
 /*   140 */    39,  143,   42,  128,   29,   29,   29,   34,  215,  152,
 /*   150 */   195,   79,  154,   73,   58,   72,   75,  208,  123,   36,
 /*   160 */    22,  205,  138,   35,    5,   30,   91,   31,  136,   70,
 /*   170 */   178,  179,   47,  201,   43,   39,  203,   42,  128,  217,
 /*   180 */   237,  141,   34,   45,  152,  174,   79,  154,   73,  123,
 /*   190 */    72,   75,  205,  165,  172,   22,  229,  142,   35,  182,
 /*   200 */    30,  182,   31,   44,   70,   77,   48,   47,  118,   43,
 /*   210 */    39,  189,   42,  128,  134,  193,  244,   34,  207,  152,
 /*   220 */   151,   79,  154,   73,   97,   72,   75,   99,  213,   93,
 /*   230 */    22,   82,    8,   35,  182,   30,  182,   31,  175,   70,
 /*   240 */   221,  176,   47,   39,   43,   42,  128,  164,  237,  141,
 /*   250 */    34,   45,  152,  187,   79,  154,   73,  123,   72,   75,
 /*   260 */   205,   86,  159,   22,  161,   38,   35,  139,   30,  241,
 /*   270 */    31,  159,   70,  161,   38,   47,  176,   43,   39,  231,
 /*   280 */    42,  128,  223,  166,  166,   34,  214,  152,  101,   79,
 /*   290 */   154,   73,  226,   72,   75,  182,  216,  182,   22,  184,
 /*   300 */   142,   35,   48,   30,  220,   31,  123,   70,   54,  205,
 /*   310 */    47,  118,   43,   39,  189,   42,  128,   33,  193,  149,
 /*   320 */    34,  183,  152,  156,   79,  154,   73,  111,   72,   75,
 /*   330 */    56,  160,  132,   22,   76,   15,   35,  176,   30,   92,
 /*   340 */    31,  123,   70,  118,  205,   47,   39,   43,   42,  128,
 /*   350 */   193,  237,  141,   34,   45,  152,  225,   79,  154,   73,
 /*   360 */   102,   72,   75,  222,  150,  124,   22,  103,  142,   35,
 /*   370 */   218,   30,   96,   31,  159,   70,  161,   38,   47,  118,
 /*   380 */    43,   39,  189,   42,  128,   32,  193,  133,   34,  215,
 /*   390 */   152,  219,   79,  154,   73,   74,   72,   75,  118,  123,
 /*   400 */    37,   22,  205,  142,   35,  193,   30,  192,   31,  162,
 /*   410 */    70,  125,  116,   47,  118,   43,   39,  189,   42,  128,
 /*   420 */   137,  193,  131,   34,  198,  152,  112,   79,  154,   73,
 /*   430 */   155,   72,   75,  118,  121,  159,   22,  161,   38,   35,
 /*   440 */   193,   30,  117,   31,  224,   70,  115,  113,   47,   63,
 /*   450 */    43,   39,   69,   42,  128,   51,    9,   49,   34,   59,
 /*   460 */   152,  119,   79,  154,   73,   67,   72,   75,  126,   65,
 /*   470 */   127,   22,  237,  141,   35,   45,   30,  120,   31,   71,
 /*   480 */    70,  114,   64,   47,  135,   43,   39,   57,   42,  128,
 /*   490 */    62,   10,   60,   34,   55,  152,  153,   79,  154,   73,
 /*   500 */   227,   72,   75,   66,   52,   50,   22,  237,  141,   35,
 /*   510 */    45,   30,  185,   31,  185,   70,  185,  185,   47,   39,
 /*   520 */    43,   42,  128,  185,    7,  157,   34,  185,  152,  185,
 /*   530 */    79,  154,   73,  185,   72,   75,  185,  185,  185,   22,
 /*   540 */   237,  141,   35,   45,   30,  185,   31,  159,   70,  161,
 /*   550 */    38,   47,  185,   43,   39,  185,   42,  128,  185,  185,
 /*   560 */   185,   34,  185,  152,  185,   79,  154,   73,  185,   72,
 /*   570 */    75,  185,  185,  185,   22,  185,  185,   35,  171,   30,
 /*   580 */   185,   31,  185,   70,  185,  185,   47,  185,   43,   39,
 /*   590 */   185,   42,  128,  185,    2,  185,   34,  185,  152,  185,
 /*   600 */    79,  154,   73,  185,   72,   75,  185,  185,  185,   22,
 /*   610 */   237,  141,   35,   45,   30,  185,   31,  140,   70,  185,
 /*   620 */   185,   47,  185,   43,   39,  185,   42,  128,  185,   21,
 /*   630 */   185,   34,  185,  152,  185,   79,  154,   73,  185,   72,
 /*   640 */    75,  185,  185,  185,   22,  237,  141,   35,   45,   30,
 /*   650 */   146,   31,  185,   70,  185,  185,   47,  185,   43,   39,
 /*   660 */   185,   42,  128,  185,  185,  185,   34,  185,  152,  185,
 /*   670 */    79,  154,   73,  185,   72,   75,  185,  185,  185,   22,
 /*   680 */   173,  185,   35,  185,   30,  185,   31,  185,   70,  185,
 /*   690 */   185,   47,  185,   43,   39,  185,   42,  128,  185,   20,
 /*   700 */   185,   34,  185,  152,  185,   79,  154,   73,   18,   72,
 /*   710 */    75,  185,  185,  185,   22,  237,  141,   35,   45,   30,
 /*   720 */   185,   31,  185,   70,  237,  141,   47,   45,   43,   19,
 /*   730 */    23,   28,   26,   26,   26,   26,   26,   26,   26,   25,
 /*   740 */    25,   29,   29,   29,  185,  237,  141,  185,   45,  239,
 /*   750 */    88,  185,  185,  185,   23,   28,   26,   26,   26,   26,
 /*   760 */    26,   26,   26,   25,   25,   29,   29,   29,   23,   28,
 /*   770 */    26,   26,   26,   26,   26,   26,   26,   25,   25,   29,
 /*   780 */    29,   29,   28,   26,   26,   26,   26,   26,   26,   26,
 /*   790 */    25,   25,   29,   29,   29,  185,  159,  196,  161,   38,
 /*   800 */   163,  185,  209,  210,  234,  235,  230,  236,  243,   27,
 /*   810 */   185,  245,  185,   89,  197,   13,  104,   26,   26,   26,
 /*   820 */    26,   26,   26,   26,   25,   25,   29,   29,   29,  185,
 /*   830 */   185,  237,  141,  185,   45,  185,  182,  185,  182,  185,
 /*   840 */    16,  185,  228,  185,  185,  100,  185,  185,  182,   80,
 /*   850 */   182,  182,  185,  182,  185,  185,  237,  141,   40,   45,
 /*   860 */   188,   40,  185,  159,  185,  161,   38,  190,  190,   78,
 /*   870 */   185,   84,  206,   83,  185,  206,   24,  185,  176,  185,
 /*   880 */   182,   78,  182,   84,   78,   83,   84,  185,   83,  142,
 /*   890 */   176,  238,  185,  176,  185,   95,  185,  182,   81,  182,
 /*   900 */   118,  185,  185,  189,  188,  105,  199,  193,   94,  142,
 /*   910 */   185,  190,  190,   78,  182,   84,  182,   83,  185,  185,
 /*   920 */   118,  206,  176,  189,  185,  110,  199,  193,  185,  185,
 /*   930 */    78,  185,   84,  185,   83,  185,  185,  185,  206,  176,
 /*   940 */   185,  142,  159,  185,  161,   38,  185,   78,  185,   84,
 /*   950 */   142,   83,  118,  185,  185,  189,  176,  122,  199,  193,
 /*   960 */   185,  118,  142,  185,  189,  211,  109,  199,  193,  185,
 /*   970 */   142,  185,  185,  118,  185,   14,  189,  185,  106,  199,
 /*   980 */   193,  118,  185,  185,  189,  142,  107,  199,  193,    3,
 /*   990 */   185,  237,  141,  142,   45,  185,  118,  185,  185,  189,
 /*  1000 */   185,  108,  199,  193,  118,  237,  141,  189,   45,  194,
 /*  1010 */   199,  193,  159,    1,  161,   38,  185,   12,  185,  185,
 /*  1020 */   185,  185,  185,  185,  185,  185,  185,  185,  185,  237,
 /*  1030 */   141,   11,   45,  237,  141,   17,   45,  185,  185,  185,
 /*  1040 */   185,  185,  185,  185,  185,  185,  185,  237,  141,    4,
 /*  1050 */    45,  237,  141,  185,   45,  185,  185,  185,  185,  185,
 /*  1060 */   185,  185,  185,  185,  185,  237,  141,  185,   45,
    );
    static public $yy_lookahead = array(
 /*     0 */    22,   77,   24,   25,   11,   67,   68,   29,   80,   31,
 /*    10 */    23,   33,   34,   35,   23,   37,   38,   59,   23,   91,
 /*    20 */    42,   43,   44,   45,    1,   47,   98,   49,   70,   51,
 /*    30 */    72,   73,   54,   65,   56,   22,   68,   24,   25,   23,
 /*    40 */    17,   18,   29,   20,   31,   58,   33,   34,   35,   58,
 /*    50 */    37,   38,   62,   58,   64,   42,   66,   44,   45,   46,
 /*    60 */    47,   81,   49,   70,   51,   72,   73,   54,   91,   56,
 /*    70 */    22,   91,   24,   25,   94,   98,   80,   29,   21,   31,
 /*    80 */    77,   33,   34,   35,   23,   37,   38,   91,   23,   29,
 /*    90 */    42,   31,   44,   45,   98,   47,   48,   49,   70,   51,
 /*   100 */    72,   73,   54,   65,   56,   22,   68,   24,   25,   76,
 /*   110 */    77,   23,   29,   53,   31,   58,   33,   34,   35,   23,
 /*   120 */    37,   38,   62,   81,   64,   42,   66,   44,   45,   46,
 /*   130 */    47,   71,   49,   91,   51,   23,   94,   54,   23,   56,
 /*   140 */    22,   53,   24,   25,   14,   15,   16,   29,   81,   31,
 /*   150 */    23,   33,   34,   35,   77,   37,   38,   71,   91,   92,
 /*   160 */    42,   94,   44,   45,    1,   47,   23,   49,   50,   51,
 /*   170 */    67,   68,   54,   71,   56,   22,   63,   24,   25,   81,
 /*   180 */    17,   18,   29,   20,   31,   23,   33,   34,   35,   91,
 /*   190 */    37,   38,   94,   40,   41,   42,   23,   80,   45,   29,
 /*   200 */    47,   31,   49,   11,   51,   57,   58,   54,   91,   56,
 /*   210 */    22,   94,   24,   25,   97,   98,   23,   29,   23,   31,
 /*   220 */    32,   33,   34,   35,   23,   37,   38,   23,   23,   23,
 /*   230 */    42,   39,    1,   45,   29,   47,   31,   49,   74,   51,
 /*   240 */    23,   71,   54,   22,   56,   24,   25,   81,   17,   18,
 /*   250 */    29,   20,   31,   23,   33,   34,   35,   91,   37,   38,
 /*   260 */    94,   23,   70,   42,   72,   73,   45,   46,   47,   23,
 /*   270 */    49,   70,   51,   72,   73,   54,   71,   56,   22,   19,
 /*   280 */    24,   25,   23,   26,   27,   29,   23,   31,   23,   33,
 /*   290 */    34,   35,   23,   37,   38,   29,   81,   31,   42,   23,
 /*   300 */    80,   45,   58,   47,   23,   49,   91,   51,   52,   94,
 /*   310 */    54,   91,   56,   22,   94,   24,   25,   97,   98,   53,
 /*   320 */    29,   23,   31,   32,   33,   34,   35,   91,   37,   38,
 /*   330 */    77,   81,   80,   42,   30,    1,   45,   71,   47,   23,
 /*   340 */    49,   91,   51,   91,   94,   54,   22,   56,   24,   25,
 /*   350 */    98,   17,   18,   29,   20,   31,   23,   33,   34,   35,
 /*   360 */    23,   37,   38,   23,   40,   91,   42,   23,   80,   45,
 /*   370 */    23,   47,   23,   49,   70,   51,   72,   73,   54,   91,
 /*   380 */    56,   22,   94,   24,   25,   97,   98,   80,   29,   81,
 /*   390 */    31,   23,   33,   34,   35,   30,   37,   38,   91,   91,
 /*   400 */    92,   42,   94,   80,   45,   98,   47,   23,   49,   94,
 /*   410 */    51,   52,   91,   54,   91,   56,   22,   94,   24,   25,
 /*   420 */    97,   98,   80,   29,   78,   31,   91,   33,   34,   35,
 /*   430 */    36,   37,   38,   91,   91,   70,   42,   72,   73,   45,
 /*   440 */    98,   47,   91,   49,   94,   51,   91,   91,   54,   77,
 /*   450 */    56,   22,   77,   24,   25,   77,    1,   77,   29,   77,
 /*   460 */    31,   91,   33,   34,   35,   77,   37,   38,   99,   77,
 /*   470 */    99,   42,   17,   18,   45,   20,   47,   91,   49,   77,
 /*   480 */    51,   91,   77,   54,   55,   56,   22,   77,   24,   25,
 /*   490 */    77,    1,   77,   29,   77,   31,   32,   33,   34,   35,
 /*   500 */    23,   37,   38,   77,   77,   77,   42,   17,   18,   45,
 /*   510 */    20,   47,  100,   49,  100,   51,  100,  100,   54,   22,
 /*   520 */    56,   24,   25,  100,    1,   28,   29,  100,   31,  100,
 /*   530 */    33,   34,   35,  100,   37,   38,  100,  100,  100,   42,
 /*   540 */    17,   18,   45,   20,   47,  100,   49,   70,   51,   72,
 /*   550 */    73,   54,  100,   56,   22,  100,   24,   25,  100,  100,
 /*   560 */   100,   29,  100,   31,  100,   33,   34,   35,  100,   37,
 /*   570 */    38,  100,  100,  100,   42,  100,  100,   45,   46,   47,
 /*   580 */   100,   49,  100,   51,  100,  100,   54,  100,   56,   22,
 /*   590 */   100,   24,   25,  100,    1,  100,   29,  100,   31,  100,
 /*   600 */    33,   34,   35,  100,   37,   38,  100,  100,  100,   42,
 /*   610 */    17,   18,   45,   20,   47,  100,   49,   50,   51,  100,
 /*   620 */   100,   54,  100,   56,   22,  100,   24,   25,  100,    1,
 /*   630 */   100,   29,  100,   31,  100,   33,   34,   35,  100,   37,
 /*   640 */    38,  100,  100,  100,   42,   17,   18,   45,   20,   47,
 /*   650 */    48,   49,  100,   51,  100,  100,   54,  100,   56,   22,
 /*   660 */   100,   24,   25,  100,  100,  100,   29,  100,   31,  100,
 /*   670 */    33,   34,   35,  100,   37,   38,  100,  100,  100,   42,
 /*   680 */    43,  100,   45,  100,   47,  100,   49,  100,   51,  100,
 /*   690 */   100,   54,  100,   56,   22,  100,   24,   25,  100,    1,
 /*   700 */   100,   29,  100,   31,  100,   33,   34,   35,    1,   37,
 /*   710 */    38,  100,  100,  100,   42,   17,   18,   45,   20,   47,
 /*   720 */   100,   49,  100,   51,   17,   18,   54,   20,   56,    1,
 /*   730 */     3,    4,    5,    6,    7,    8,    9,   10,   11,   12,
 /*   740 */    13,   14,   15,   16,  100,   17,   18,  100,   20,   23,
 /*   750 */    23,  100,  100,  100,    3,    4,    5,    6,    7,    8,
 /*   760 */     9,   10,   11,   12,   13,   14,   15,   16,    3,    4,
 /*   770 */     5,    6,    7,    8,    9,   10,   11,   12,   13,   14,
 /*   780 */    15,   16,    4,    5,    6,    7,    8,    9,   10,   11,
 /*   790 */    12,   13,   14,   15,   16,  100,   70,   79,   72,   73,
 /*   800 */    82,   83,   84,   85,   86,   87,   88,   89,   90,    2,
 /*   810 */   100,   93,  100,   95,   63,    1,   23,    5,    6,    7,
 /*   820 */     8,    9,   10,   11,   12,   13,   14,   15,   16,  100,
 /*   830 */   100,   17,   18,  100,   20,  100,   29,  100,   31,  100,
 /*   840 */     1,  100,   23,  100,  100,   23,  100,  100,   29,   30,
 /*   850 */    31,   29,  100,   31,  100,  100,   17,   18,   39,   20,
 /*   860 */    53,   39,  100,   70,  100,   72,   73,   60,   61,   62,
 /*   870 */   100,   64,   53,   66,  100,   53,   69,  100,   71,  100,
 /*   880 */    29,   62,   31,   64,   62,   66,   64,  100,   66,   80,
 /*   890 */    71,   23,  100,   71,  100,   23,  100,   29,   30,   31,
 /*   900 */    91,  100,  100,   94,   53,   96,   97,   98,   23,   80,
 /*   910 */   100,   60,   61,   62,   29,   64,   31,   66,  100,  100,
 /*   920 */    91,   53,   71,   94,  100,   96,   97,   98,  100,  100,
 /*   930 */    62,  100,   64,  100,   66,  100,  100,  100,   53,   71,
 /*   940 */   100,   80,   70,  100,   72,   73,  100,   62,  100,   64,
 /*   950 */    80,   66,   91,  100,  100,   94,   71,   96,   97,   98,
 /*   960 */   100,   91,   80,  100,   94,   23,   96,   97,   98,  100,
 /*   970 */    80,  100,  100,   91,  100,    1,   94,  100,   96,   97,
 /*   980 */    98,   91,  100,  100,   94,   80,   96,   97,   98,    1,
 /*   990 */   100,   17,   18,   80,   20,  100,   91,  100,  100,   94,
 /*  1000 */   100,   96,   97,   98,   91,   17,   18,   94,   20,   96,
 /*  1010 */    97,   98,   70,    1,   72,   73,  100,    1,  100,  100,
 /*  1020 */   100,  100,  100,  100,  100,  100,  100,  100,  100,   17,
 /*  1030 */    18,    1,   20,   17,   18,    1,   20,  100,  100,  100,
 /*  1040 */   100,  100,  100,  100,  100,  100,  100,   17,   18,    1,
 /*  1050 */    20,   17,   18,  100,   20,  100,  100,  100,  100,  100,
 /*  1060 */   100,  100,  100,  100,  100,   17,   18,  100,   20,
);
    const YY_SHIFT_USE_DFLT = -63;
    const YY_SHIFT_MAX = 173;
    static public $yy_shift_ofst = array(
 /*     0 */   -63,  153,   83,  -22,  118,   13,   48,  637,  602,  497,
 /*    10 */   324,  256,  188,  394,  464,  567,  359,  429,  532,  221,
 /*    20 */   291,  672,  807,  807,  807,  807,  807,  807,  807,  807,
 /*    30 */   851,  851,  851,  851,  868,  885,  819,  822,   60,   60,
 /*    40 */    60,   60,   60,  170,  170,  170,  170,  170,  170, 1048,
 /*    50 */  1034, 1012, 1030, 1016,  205,  839,  523,  593,  698,  707,
 /*    60 */   728,  814,  163,   23,  231,  490,  455,  334,  628,  988,
 /*    70 */   266,  974,  -10,  170,  170,  170,  170,  170,  -10,  170,
 /*    80 */   170,  170,  170,  -62,   38,  -63,  -63,  -63,  -63,  -63,
 /*    90 */   -63,  -63,  -63,  -63,  -63,  -63,  -63,  -63,  -63,  -63,
 /*   100 */   -63,  -63,  -63,  -63,  -63,  751,  727,  765,  778,  812,
 /*   110 */   812,  192,  793,  726,  304,  477,  872,  201,  -42,   -7,
 /*   120 */   942,  365,  130,   28,   28,   88,  103,  -32,  257,   -9,
 /*   130 */    -5,  148,  -13,   57,   96,  112,   61,   16,   65,  384,
 /*   140 */   281,  260,  244,  276,  115,  265,  269,  298,  316,  349,
 /*   150 */   368,  347,  344,  333,  337,  340,  259,  246,  173,  102,
 /*   160 */   127,   86,  113,  230,  164,  217,  204,  206,  263,  195,
 /*   170 */   238,  193,  143,  162,
);
    const YY_REDUCE_USE_DFLT = -77;
    const YY_REDUCE_MAX = 104;
    static public $yy_reduce_ofst = array(
 /*     0 */    33,  718,  718,  718,  718,  718,  718,  718,  718,  718,
 /*    10 */   718,  718,  718,  718,  718,  718,  718,  718,  718,  718,
 /*    20 */   718,  718,  882,  905,  809,  861,  870,  890,  829,  913,
 /*    30 */   220,  288,  117,  323,   67,  308,  215,  215,  166,  250,
 /*    40 */    98,  -20,   42,  342,  252,  307,  -72,   -4,  -23,  346,
 /*    50 */   346,  346,  346,  346,  386,  346,  346,  346,  346,  346,
 /*    60 */   346,  346,  346,  346,  346,  346,  346,  346,  346,  346,
 /*    70 */   351,  346,  350,  343,  335,  236,  274,  390,  315,  321,
 /*    80 */   355,  356,  370,  369,  371,  388,  382,  372,  375,  378,
 /*    90 */   380,  392,  405,  253,  413,  402,  417,  427,  428,  426,
 /*   100 */   410,  415,   77,    3,  -76,
);
    static public $yyExpectedTokens = array(
        /* 0 */ array(),
        /* 1 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 40, 41, 42, 45, 47, 49, 51, 54, 56, ),
        /* 2 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 44, 45, 46, 47, 49, 51, 54, 56, ),
        /* 3 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 43, 44, 45, 47, 49, 51, 54, 56, ),
        /* 4 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 44, 45, 47, 49, 50, 51, 54, 56, ),
        /* 5 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 44, 45, 46, 47, 49, 51, 54, 56, ),
        /* 6 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 44, 45, 47, 48, 49, 51, 54, 56, ),
        /* 7 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 43, 45, 47, 49, 51, 54, 56, ),
        /* 8 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 47, 48, 49, 51, 54, 56, ),
        /* 9 */ array(22, 24, 25, 28, 29, 31, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 54, 56, ),
        /* 10 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 40, 42, 45, 47, 49, 51, 54, 56, ),
        /* 11 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 52, 54, 56, ),
        /* 12 */ array(22, 24, 25, 29, 31, 32, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 54, 56, ),
        /* 13 */ array(22, 24, 25, 29, 31, 33, 34, 35, 36, 37, 38, 42, 45, 47, 49, 51, 54, 56, ),
        /* 14 */ array(22, 24, 25, 29, 31, 32, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 54, 56, ),
        /* 15 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 47, 49, 50, 51, 54, 56, ),
        /* 16 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 52, 54, 56, ),
        /* 17 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 54, 55, 56, ),
        /* 18 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 46, 47, 49, 51, 54, 56, ),
        /* 19 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 46, 47, 49, 51, 54, 56, ),
        /* 20 */ array(22, 24, 25, 29, 31, 32, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 54, 56, ),
        /* 21 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 54, 56, ),
        /* 22 */ array(2, 29, 31, 53, 60, 61, 62, 64, 66, 69, 71, ),
        /* 23 */ array(2, 29, 31, 53, 60, 61, 62, 64, 66, 69, 71, ),
        /* 24 */ array(2, 29, 31, 53, 60, 61, 62, 64, 66, 69, 71, ),
        /* 25 */ array(2, 29, 31, 53, 60, 61, 62, 64, 66, 69, 71, ),
        /* 26 */ array(2, 29, 31, 53, 60, 61, 62, 64, 66, 69, 71, ),
        /* 27 */ array(2, 29, 31, 53, 60, 61, 62, 64, 66, 69, 71, ),
        /* 28 */ array(2, 29, 31, 53, 60, 61, 62, 64, 66, 69, 71, ),
        /* 29 */ array(2, 29, 31, 53, 60, 61, 62, 64, 66, 69, 71, ),
        /* 30 */ array(29, 31, 53, 60, 61, 62, 64, 66, 71, ),
        /* 31 */ array(29, 31, 53, 60, 61, 62, 64, 66, 71, ),
        /* 32 */ array(29, 31, 53, 60, 61, 62, 64, 66, 71, ),
        /* 33 */ array(29, 31, 53, 60, 61, 62, 64, 66, 71, ),
        /* 34 */ array(23, 29, 30, 31, 53, 62, 64, 66, 71, ),
        /* 35 */ array(23, 29, 31, 53, 62, 64, 66, 71, ),
        /* 36 */ array(23, 29, 30, 31, 39, 53, 62, 64, 66, 71, ),
        /* 37 */ array(23, 29, 31, 39, 53, 62, 64, 66, 71, ),
        /* 38 */ array(29, 31, 53, 62, 64, 66, 71, ),
        /* 39 */ array(29, 31, 53, 62, 64, 66, 71, ),
        /* 40 */ array(29, 31, 53, 62, 64, 66, 71, ),
        /* 41 */ array(29, 31, 53, 62, 64, 66, 71, ),
        /* 42 */ array(29, 31, 53, 62, 64, 66, 71, ),
        /* 43 */ array(29, 31, 71, ),
        /* 44 */ array(29, 31, 71, ),
        /* 45 */ array(29, 31, 71, ),
        /* 46 */ array(29, 31, 71, ),
        /* 47 */ array(29, 31, 71, ),
        /* 48 */ array(29, 31, 71, ),
        /* 49 */ array(1, 17, 18, 20, ),
        /* 50 */ array(1, 17, 18, 20, ),
        /* 51 */ array(1, 17, 18, 20, ),
        /* 52 */ array(1, 17, 18, 20, ),
        /* 53 */ array(1, 17, 18, 20, ),
        /* 54 */ array(23, 29, 31, 71, ),
        /* 55 */ array(1, 17, 18, 20, ),
        /* 56 */ array(1, 17, 18, 20, ),
        /* 57 */ array(1, 17, 18, 20, ),
        /* 58 */ array(1, 17, 18, 20, ),
        /* 59 */ array(1, 17, 18, 20, ),
        /* 60 */ array(1, 17, 18, 20, ),
        /* 61 */ array(1, 17, 18, 20, ),
        /* 62 */ array(1, 17, 18, 20, ),
        /* 63 */ array(1, 17, 18, 20, ),
        /* 64 */ array(1, 17, 18, 20, ),
        /* 65 */ array(1, 17, 18, 20, ),
        /* 66 */ array(1, 17, 18, 20, ),
        /* 67 */ array(1, 17, 18, 20, ),
        /* 68 */ array(1, 17, 18, 20, ),
        /* 69 */ array(1, 17, 18, 20, ),
        /* 70 */ array(29, 31, 53, 71, ),
        /* 71 */ array(1, 17, 18, 20, ),
        /* 72 */ array(62, 64, 66, ),
        /* 73 */ array(29, 31, 71, ),
        /* 74 */ array(29, 31, 71, ),
        /* 75 */ array(29, 31, 71, ),
        /* 76 */ array(29, 31, 71, ),
        /* 77 */ array(29, 31, 71, ),
        /* 78 */ array(62, 64, 66, ),
        /* 79 */ array(29, 31, 71, ),
        /* 80 */ array(29, 31, 71, ),
        /* 81 */ array(29, 31, 71, ),
        /* 82 */ array(29, 31, 71, ),
        /* 83 */ array(67, 68, ),
        /* 84 */ array(65, 68, ),
        /* 85 */ array(),
        /* 86 */ array(),
        /* 87 */ array(),
        /* 88 */ array(),
        /* 89 */ array(),
        /* 90 */ array(),
        /* 91 */ array(),
        /* 92 */ array(),
        /* 93 */ array(),
        /* 94 */ array(),
        /* 95 */ array(),
        /* 96 */ array(),
        /* 97 */ array(),
        /* 98 */ array(),
        /* 99 */ array(),
        /* 100 */ array(),
        /* 101 */ array(),
        /* 102 */ array(),
        /* 103 */ array(),
        /* 104 */ array(),
        /* 105 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 63, ),
        /* 106 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 23, ),
        /* 107 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 108 */ array(4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 109 */ array(5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 110 */ array(5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 111 */ array(11, 39, 70, 72, 73, ),
        /* 112 */ array(23, 70, 72, 73, ),
        /* 113 */ array(23, 70, 72, 73, ),
        /* 114 */ array(30, 70, 72, 73, ),
        /* 115 */ array(23, 70, 72, 73, ),
        /* 116 */ array(23, 70, 72, 73, ),
        /* 117 */ array(23, 70, 72, 73, ),
        /* 118 */ array(59, 70, 72, 73, ),
        /* 119 */ array(11, 70, 72, 73, ),
        /* 120 */ array(23, 70, 72, 73, ),
        /* 121 */ array(30, 70, 72, 73, ),
        /* 122 */ array(14, 15, 16, ),
        /* 123 */ array(70, 72, 73, ),
        /* 124 */ array(70, 72, 73, ),
        /* 125 */ array(23, 53, ),
        /* 126 */ array(67, 68, ),
        /* 127 */ array(65, 68, ),
        /* 128 */ array(26, 27, ),
        /* 129 */ array(23, 58, ),
        /* 130 */ array(23, 58, ),
        /* 131 */ array(57, 58, ),
        /* 132 */ array(23, 58, ),
        /* 133 */ array(21, 58, ),
        /* 134 */ array(23, ),
        /* 135 */ array(23, ),
        /* 136 */ array(23, ),
        /* 137 */ array(23, ),
        /* 138 */ array(23, ),
        /* 139 */ array(23, ),
        /* 140 */ array(23, ),
        /* 141 */ array(19, ),
        /* 142 */ array(58, ),
        /* 143 */ array(23, ),
        /* 144 */ array(23, ),
        /* 145 */ array(23, ),
        /* 146 */ array(23, ),
        /* 147 */ array(23, ),
        /* 148 */ array(23, ),
        /* 149 */ array(23, ),
        /* 150 */ array(23, ),
        /* 151 */ array(23, ),
        /* 152 */ array(23, ),
        /* 153 */ array(23, ),
        /* 154 */ array(23, ),
        /* 155 */ array(23, ),
        /* 156 */ array(23, ),
        /* 157 */ array(23, ),
        /* 158 */ array(23, ),
        /* 159 */ array(71, ),
        /* 160 */ array(23, ),
        /* 161 */ array(71, ),
        /* 162 */ array(63, ),
        /* 163 */ array(23, ),
        /* 164 */ array(74, ),
        /* 165 */ array(23, ),
        /* 166 */ array(23, ),
        /* 167 */ array(23, ),
        /* 168 */ array(23, ),
        /* 169 */ array(23, ),
        /* 170 */ array(23, ),
        /* 171 */ array(23, ),
        /* 172 */ array(23, ),
        /* 173 */ array(23, ),
        /* 174 */ array(),
        /* 175 */ array(),
        /* 176 */ array(),
        /* 177 */ array(),
        /* 178 */ array(),
        /* 179 */ array(),
        /* 180 */ array(),
        /* 181 */ array(),
        /* 182 */ array(),
        /* 183 */ array(),
        /* 184 */ array(),
        /* 185 */ array(),
        /* 186 */ array(),
        /* 187 */ array(),
        /* 188 */ array(),
        /* 189 */ array(),
        /* 190 */ array(),
        /* 191 */ array(),
        /* 192 */ array(),
        /* 193 */ array(),
        /* 194 */ array(),
        /* 195 */ array(),
        /* 196 */ array(),
        /* 197 */ array(),
        /* 198 */ array(),
        /* 199 */ array(),
        /* 200 */ array(),
        /* 201 */ array(),
        /* 202 */ array(),
        /* 203 */ array(),
        /* 204 */ array(),
        /* 205 */ array(),
        /* 206 */ array(),
        /* 207 */ array(),
        /* 208 */ array(),
        /* 209 */ array(),
        /* 210 */ array(),
        /* 211 */ array(),
        /* 212 */ array(),
        /* 213 */ array(),
        /* 214 */ array(),
        /* 215 */ array(),
        /* 216 */ array(),
        /* 217 */ array(),
        /* 218 */ array(),
        /* 219 */ array(),
        /* 220 */ array(),
        /* 221 */ array(),
        /* 222 */ array(),
        /* 223 */ array(),
        /* 224 */ array(),
        /* 225 */ array(),
        /* 226 */ array(),
        /* 227 */ array(),
        /* 228 */ array(),
        /* 229 */ array(),
        /* 230 */ array(),
        /* 231 */ array(),
        /* 232 */ array(),
        /* 233 */ array(),
        /* 234 */ array(),
        /* 235 */ array(),
        /* 236 */ array(),
        /* 237 */ array(),
        /* 238 */ array(),
        /* 239 */ array(),
        /* 240 */ array(),
        /* 241 */ array(),
        /* 242 */ array(),
        /* 243 */ array(),
        /* 244 */ array(),
        /* 245 */ array(),
);
    static public $yy_default = array(
 /*     0 */   248,  329,  329,  329,  329,  329,  329,  329,  329,  329,
 /*    10 */   329,  329,  329,  329,  329,  329,  329,  329,  329,  329,
 /*    20 */   329,  329,  329,  329,  329,  329,  329,  329,  329,  329,
 /*    30 */   329,  329,  329,  329,  329,  329,  329,  329,  329,  329,
 /*    40 */   329,  329,  329,  329,  329,  329,  329,  329,  329,  329,
 /*    50 */   329,  329,  329,  329,  329,  329,  329,  329,  329,  329,
 /*    60 */   329,  329,  329,  329,  329,  329,  329,  329,  246,  329,
 /*    70 */   329,  329,  329,  329,  329,  329,  329,  329,  329,  329,
 /*    80 */   329,  329,  329,  329,  329,  248,  248,  248,  248,  248,
 /*    90 */   248,  248,  248,  248,  248,  248,  248,  248,  248,  248,
 /*   100 */   248,  248,  248,  248,  248,  329,  329,  316,  317,  320,
 /*   110 */   318,  329,  329,  329,  329,  329,  329,  329,  298,  329,
 /*   120 */   329,  329,  319,  302,  294,  329,  329,  329,  329,  329,
 /*   130 */   329,  329,  329,  329,  329,  329,  329,  329,  329,  329,
 /*   140 */   329,  329,  305,  329,  329,  329,  329,  329,  329,  329,
 /*   150 */   329,  329,  329,  329,  329,  329,  329,  329,  329,  329,
 /*   160 */   329,  329,  329,  329,  329,  329,  329,  329,  329,  329,
 /*   170 */   329,  329,  329,  329,  280,  326,  327,  282,  313,  314,
 /*   180 */   315,  311,  328,  285,  292,  255,  291,  254,  306,  308,
 /*   190 */   307,  312,  284,  296,  321,  253,  249,  322,  247,  323,
 /*   200 */   297,  324,  295,  309,  310,  304,  303,  281,  325,  256,
 /*   210 */   257,  290,  275,  289,  279,  301,  299,  300,  269,  278,
 /*   220 */   288,  277,  272,  271,  274,  270,  286,  268,  267,  260,
 /*   230 */   261,  251,  252,  276,  258,  259,  262,  250,  265,  266,
 /*   240 */   293,  264,  287,  263,  283,  273,
);
/* The next thing included is series of defines which control
** various aspects of the generated parser.
**    self::YYNOCODE      is a number which corresponds
**                        to no legal terminal or nonterminal number.  This
**                        number is used to fill in empty slots of the hash 
**                        table.
**    self::YYFALLBACK    If defined, this indicates that one or more tokens
**                        have fall-back values which should be used if the
**                        original value of the token will not parse.
**    self::YYSTACKDEPTH  is the maximum depth of the parser's stack.
**    self::YYNSTATE      the combined number of states.
**    self::YYNRULE       the number of rules in the grammar
**    self::YYERRORSYMBOL is the code number of the error symbol.  If not
**                        defined, then do no error processing.
*/
    const YYNOCODE = 101;
    const YYSTACKDEPTH = 100;
    const YYNSTATE = 246;
    const YYNRULE = 83;
    const YYERRORSYMBOL = 75;
    const YYERRSYMDT = 'yy0';
    const YYFALLBACK = 0;
    /** The next table maps tokens into fallback tokens.  If a construct
     * like the following:
     * 
     *      %fallback ID X Y Z.
     *
     * appears in the grammer, then ID becomes a fallback token for X, Y,
     * and Z.  Whenever one of the tokens X, Y, or Z is input to the parser
     * but it does not parse, the type of the token is changed to ID and
     * the parse is retried before an error is thrown.
     */
    static public $yyFallback = array(
    );
    /**
     * Turn parser tracing on by giving a stream to which to write the trace
     * and a prompt to preface each trace message.  Tracing is turned off
     * by making either argument NULL 
     *
     * Inputs:
     * 
     * - A stream resource to which trace output should be written.
     *   If NULL, then tracing is turned off.
     * - A prefix string written at the beginning of every
     *   line of trace output.  If NULL, then tracing is
     *   turned off.
     *
     * Outputs:
     * 
     * - None.
     * @param resource
     * @param string
     */
    static function Trace($TraceFILE, $zTracePrompt)
    {
        if (!$TraceFILE) {
            $zTracePrompt = 0;
        } elseif (!$zTracePrompt) {
            $TraceFILE = 0;
        }
        self::$yyTraceFILE = $TraceFILE;
        self::$yyTracePrompt = $zTracePrompt;
    }

    /**
     * Output debug information to output (php://output stream)
     */
    static function PrintTrace()
    {
        self::$yyTraceFILE = fopen('php://output', 'w');
        self::$yyTracePrompt = '';
    }

    /**
     * @var resource|0
     */
    static public $yyTraceFILE;
    /**
     * String to prepend to debug output
     * @var string|0
     */
    static public $yyTracePrompt;
    /**
     * @var int
     */
    public $yyidx;                    /* Index of top element in stack */
    /**
     * @var int
     */
    public $yyerrcnt;                 /* Shifts left before out of the error */
    /**
     * @var array
     */
    public $yystack = array();  /* The parser's stack */

    /**
     * For tracing shifts, the names of all terminals and nonterminals
     * are required.  The following table supplies these names
     * @var array
     */
    static public $yyTokenName = array( 
  '$',             'T_OPEN_TAG',    'T_NOT',         'T_AND',       
  'T_OR',          'T_EQ',          'T_NE',          'T_GT',        
  'T_GE',          'T_LT',          'T_LE',          'T_IN',        
  'T_PLUS',        'T_MINUS',       'T_TIMES',       'T_DIV',       
  'T_MOD',         'T_HTML',        'T_COMMENT_OPEN',  'T_COMMENT',   
  'T_PRINT_OPEN',  'T_PRINT_CLOSE',  'T_EXTENDS',     'T_CLOSE_TAG', 
  'T_INCLUDE',     'T_AUTOESCAPE',  'T_OFF',         'T_ON',        
  'T_END_AUTOESCAPE',  'T_CUSTOM_TAG',  'T_AS',          'T_CUSTOM_BLOCK',
  'T_CUSTOM_END',  'T_BUFFER',      'T_SPACEFULL',   'T_WITH',      
  'T_ENDWITH',     'T_LOAD',        'T_FOR',         'T_COMMA',     
  'T_CLOSEFOR',    'T_EMPTY',       'T_IF',          'T_ENDIF',     
  'T_ELSE',        'T_IFCHANGED',   'T_ENDIFCHANGED',  'T_IFEQUAL',   
  'T_END_IFEQUAL',  'T_IFNOTEQUAL',  'T_END_IFNOTEQUAL',  'T_BLOCK',     
  'T_END_BLOCK',   'T_NUMERIC',     'T_FILTER',      'T_END_FILTER',
  'T_REGROUP',     'T_BY',          'T_PIPE',        'T_COLON',     
  'T_TRUE',        'T_FALSE',       'T_INTL',        'T_RPARENT',   
  'T_STRING_SINGLE_INIT',  'T_STRING_SINGLE_END',  'T_STRING_DOUBLE_INIT',  'T_STRING_DOUBLE_END',
  'T_STRING_CONTENT',  'T_LPARENT',     'T_OBJ',         'T_ALPHA',     
  'T_DOT',         'T_BRACKETS_OPEN',  'T_BRACKETS_CLOSE',  'error',       
  'start',         'body',          'code',          'stmts',       
  'filtered_var',  'var_or_string',  'stmt',          'for_stmt',    
  'ifchanged_stmt',  'block_stmt',    'filter_stmt',   'if_stmt',     
  'custom_tag',    'alias',         'ifequal',       'varname',     
  'var_list',      'regroup',       'string',        'for_def',     
  'expr',          'fvar_or_string',  'varname_args',  's_content',   
    );

    /**
     * For tracing reduce actions, the names of all rules are required.
     * @var array
     */
    static public $yyRuleName = array(
 /*   0 */ "start ::= body",
 /*   1 */ "body ::= body code",
 /*   2 */ "body ::=",
 /*   3 */ "code ::= T_OPEN_TAG stmts",
 /*   4 */ "code ::= T_HTML",
 /*   5 */ "code ::= T_COMMENT_OPEN T_COMMENT",
 /*   6 */ "code ::= T_PRINT_OPEN filtered_var T_PRINT_CLOSE",
 /*   7 */ "stmts ::= T_EXTENDS var_or_string T_CLOSE_TAG",
 /*   8 */ "stmts ::= stmt T_CLOSE_TAG",
 /*   9 */ "stmts ::= for_stmt",
 /*  10 */ "stmts ::= ifchanged_stmt",
 /*  11 */ "stmts ::= block_stmt",
 /*  12 */ "stmts ::= filter_stmt",
 /*  13 */ "stmts ::= if_stmt",
 /*  14 */ "stmts ::= T_INCLUDE var_or_string T_CLOSE_TAG",
 /*  15 */ "stmts ::= custom_tag",
 /*  16 */ "stmts ::= alias",
 /*  17 */ "stmts ::= ifequal",
 /*  18 */ "stmts ::= T_AUTOESCAPE T_OFF|T_ON T_CLOSE_TAG body T_OPEN_TAG T_END_AUTOESCAPE T_CLOSE_TAG",
 /*  19 */ "custom_tag ::= T_CUSTOM_TAG T_CLOSE_TAG",
 /*  20 */ "custom_tag ::= T_CUSTOM_TAG T_AS varname T_CLOSE_TAG",
 /*  21 */ "custom_tag ::= T_CUSTOM_TAG var_list T_CLOSE_TAG",
 /*  22 */ "custom_tag ::= T_CUSTOM_TAG var_list T_AS varname T_CLOSE_TAG",
 /*  23 */ "custom_tag ::= T_CUSTOM_BLOCK T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  24 */ "custom_tag ::= T_BUFFER varname T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  25 */ "custom_tag ::= T_SPACEFULL T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  26 */ "alias ::= T_WITH varname T_AS varname T_CLOSE_TAG body T_OPEN_TAG T_ENDWITH T_CLOSE_TAG",
 /*  27 */ "stmt ::= regroup",
 /*  28 */ "stmt ::= T_LOAD string",
 /*  29 */ "for_def ::= T_FOR varname T_IN filtered_var T_CLOSE_TAG",
 /*  30 */ "for_def ::= T_FOR varname T_COMMA varname T_IN filtered_var T_CLOSE_TAG",
 /*  31 */ "for_stmt ::= for_def body T_OPEN_TAG T_CLOSEFOR T_CLOSE_TAG",
 /*  32 */ "for_stmt ::= for_def body T_OPEN_TAG T_EMPTY T_CLOSE_TAG body T_OPEN_TAG T_CLOSEFOR T_CLOSE_TAG",
 /*  33 */ "if_stmt ::= T_IF expr T_CLOSE_TAG body T_OPEN_TAG T_ENDIF T_CLOSE_TAG",
 /*  34 */ "if_stmt ::= T_IF expr T_CLOSE_TAG body T_OPEN_TAG T_ELSE T_CLOSE_TAG body T_OPEN_TAG T_ENDIF T_CLOSE_TAG",
 /*  35 */ "ifchanged_stmt ::= T_IFCHANGED T_CLOSE_TAG body T_OPEN_TAG T_ENDIFCHANGED T_CLOSE_TAG",
 /*  36 */ "ifchanged_stmt ::= T_IFCHANGED var_list T_CLOSE_TAG body T_OPEN_TAG T_ENDIFCHANGED T_CLOSE_TAG",
 /*  37 */ "ifchanged_stmt ::= T_IFCHANGED T_CLOSE_TAG body T_OPEN_TAG T_ELSE T_CLOSE_TAG body T_OPEN_TAG T_ENDIFCHANGED T_CLOSE_TAG",
 /*  38 */ "ifchanged_stmt ::= T_IFCHANGED var_list T_CLOSE_TAG body T_OPEN_TAG T_ELSE T_CLOSE_TAG body T_OPEN_TAG T_ENDIFCHANGED T_CLOSE_TAG",
 /*  39 */ "ifequal ::= T_IFEQUAL fvar_or_string fvar_or_string T_CLOSE_TAG body T_OPEN_TAG T_END_IFEQUAL T_CLOSE_TAG",
 /*  40 */ "ifequal ::= T_IFEQUAL fvar_or_string fvar_or_string T_CLOSE_TAG body T_OPEN_TAG T_ELSE T_CLOSE_TAG body T_OPEN_TAG T_END_IFEQUAL T_CLOSE_TAG",
 /*  41 */ "ifequal ::= T_IFNOTEQUAL fvar_or_string fvar_or_string T_CLOSE_TAG body T_OPEN_TAG T_END_IFNOTEQUAL T_CLOSE_TAG",
 /*  42 */ "ifequal ::= T_IFNOTEQUAL fvar_or_string fvar_or_string T_CLOSE_TAG body T_OPEN_TAG T_ELSE T_CLOSE_TAG body T_OPEN_TAG T_END_IFNOTEQUAL T_CLOSE_TAG",
 /*  43 */ "block_stmt ::= T_BLOCK varname T_CLOSE_TAG body T_OPEN_TAG T_END_BLOCK T_CLOSE_TAG",
 /*  44 */ "block_stmt ::= T_BLOCK varname T_CLOSE_TAG body T_OPEN_TAG T_END_BLOCK varname T_CLOSE_TAG",
 /*  45 */ "block_stmt ::= T_BLOCK T_NUMERIC T_CLOSE_TAG body T_OPEN_TAG T_END_BLOCK T_CLOSE_TAG",
 /*  46 */ "block_stmt ::= T_BLOCK T_NUMERIC T_CLOSE_TAG body T_OPEN_TAG T_END_BLOCK T_NUMERIC T_CLOSE_TAG",
 /*  47 */ "filter_stmt ::= T_FILTER filtered_var T_CLOSE_TAG body T_OPEN_TAG T_END_FILTER T_CLOSE_TAG",
 /*  48 */ "regroup ::= T_REGROUP filtered_var T_BY varname T_AS varname",
 /*  49 */ "filtered_var ::= filtered_var T_PIPE varname_args",
 /*  50 */ "filtered_var ::= varname_args",
 /*  51 */ "varname_args ::= varname T_COLON var_or_string",
 /*  52 */ "varname_args ::= varname",
 /*  53 */ "var_list ::= var_list var_or_string",
 /*  54 */ "var_list ::= var_list T_COMMA var_or_string",
 /*  55 */ "var_list ::= var_or_string",
 /*  56 */ "var_or_string ::= varname",
 /*  57 */ "var_or_string ::= T_NUMERIC",
 /*  58 */ "var_or_string ::= string",
 /*  59 */ "fvar_or_string ::= filtered_var",
 /*  60 */ "fvar_or_string ::= T_NUMERIC",
 /*  61 */ "fvar_or_string ::= T_TRUE|T_FALSE",
 /*  62 */ "fvar_or_string ::= string",
 /*  63 */ "string ::= T_INTL string T_RPARENT",
 /*  64 */ "string ::= T_STRING_SINGLE_INIT T_STRING_SINGLE_END",
 /*  65 */ "string ::= T_STRING_DOUBLE_INIT T_STRING_DOUBLE_END",
 /*  66 */ "string ::= T_STRING_SINGLE_INIT s_content T_STRING_SINGLE_END",
 /*  67 */ "string ::= T_STRING_DOUBLE_INIT s_content T_STRING_DOUBLE_END",
 /*  68 */ "s_content ::= s_content T_STRING_CONTENT",
 /*  69 */ "s_content ::= T_STRING_CONTENT",
 /*  70 */ "expr ::= T_NOT expr",
 /*  71 */ "expr ::= expr T_AND expr",
 /*  72 */ "expr ::= expr T_OR expr",
 /*  73 */ "expr ::= expr T_PLUS|T_MINUS expr",
 /*  74 */ "expr ::= expr T_EQ|T_NE|T_GT|T_GE|T_LT|T_LE|T_IN expr",
 /*  75 */ "expr ::= expr T_TIMES|T_DIV|T_MOD expr",
 /*  76 */ "expr ::= T_LPARENT expr T_RPARENT",
 /*  77 */ "expr ::= fvar_or_string",
 /*  78 */ "varname ::= varname T_OBJ T_ALPHA",
 /*  79 */ "varname ::= varname T_DOT T_ALPHA",
 /*  80 */ "varname ::= varname T_BRACKETS_OPEN var_or_string T_BRACKETS_CLOSE",
 /*  81 */ "varname ::= T_ALPHA",
 /*  82 */ "varname ::= T_CUSTOM_TAG|T_CUSTOM_BLOCK",
    );

    /**
     * This function returns the symbolic name associated with a token
     * value.
     * @param int
     * @return string
     */
    function tokenName($tokenType)
    {
        if ($tokenType === 0) {
            return 'End of Input';
        }
        if ($tokenType > 0 && $tokenType < count(self::$yyTokenName)) {
            return self::$yyTokenName[$tokenType];
        } else {
            return "Unknown";
        }
    }

    /**
     * The following function deletes the value associated with a
     * symbol.  The symbol can be either a terminal or nonterminal.
     * @param int the symbol code
     * @param mixed the symbol's value
     */
    static function yy_destructor($yymajor, $yypminor)
    {
        switch ($yymajor) {
        /* Here is inserted the actions which take place when a
        ** terminal or non-terminal is destroyed.  This can happen
        ** when the symbol is popped from the stack during a
        ** reduce or during error processing or when a parser is 
        ** being destroyed before it is finished parsing.
        **
        ** Note: during a reduce, the only symbols destroyed are those
        ** which appear on the RHS of the rule, but which are not used
        ** inside the C code.
        */
            default:  break;   /* If no destructor action specified: do nothing */
        }
    }

    /**
     * Pop the parser's stack once.
     *
     * If there is a destructor routine associated with the token which
     * is popped from the stack, then call it.
     *
     * Return the major token number for the symbol popped.
     * @param Haanga_yyParser
     * @return int
     */
    function yy_pop_parser_stack()
    {
        if (!count($this->yystack)) {
            return;
        }
        $yytos = array_pop($this->yystack);
        if (self::$yyTraceFILE && $this->yyidx >= 0) {
            fwrite(self::$yyTraceFILE,
                self::$yyTracePrompt . 'Popping ' . self::$yyTokenName[$yytos->major] .
                    "\n");
        }
        $yymajor = $yytos->major;
        self::yy_destructor($yymajor, $yytos->minor);
        $this->yyidx--;
        return $yymajor;
    }

    /**
     * Deallocate and destroy a parser.  Destructors are all called for
     * all stack elements before shutting the parser down.
     */
    function __destruct()
    {
        while ($this->yyidx >= 0) {
            $this->yy_pop_parser_stack();
        }
        if (is_resource(self::$yyTraceFILE)) {
            fclose(self::$yyTraceFILE);
        }
    }

    /**
     * Based on the current state and parser stack, get a list of all
     * possible lookahead tokens
     * @param int
     * @return array
     */
    function yy_get_expected_tokens($token)
    {
        $state = $this->yystack[$this->yyidx]->stateno;
        $expected = self::$yyExpectedTokens[$state];
        if (in_array($token, self::$yyExpectedTokens[$state], true)) {
            return $expected;
        }
        $stack = $this->yystack;
        $yyidx = $this->yyidx;
        do {
            $yyact = $this->yy_find_shift_action($token);
            if ($yyact >= self::YYNSTATE && $yyact < self::YYNSTATE + self::YYNRULE) {
                // reduce action
                $done = 0;
                do {
                    if ($done++ == 100) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // too much recursion prevents proper detection
                        // so give up
                        return array_unique($expected);
                    }
                    $yyruleno = $yyact - self::YYNSTATE;
                    $this->yyidx -= self::$yyRuleInfo[$yyruleno]['rhs'];
                    $nextstate = $this->yy_find_reduce_action(
                        $this->yystack[$this->yyidx]->stateno,
                        self::$yyRuleInfo[$yyruleno]['lhs']);
                    if (isset(self::$yyExpectedTokens[$nextstate])) {
                        $expected += self::$yyExpectedTokens[$nextstate];
                            if (in_array($token,
                                  self::$yyExpectedTokens[$nextstate], true)) {
                            $this->yyidx = $yyidx;
                            $this->yystack = $stack;
                            return array_unique($expected);
                        }
                    }
                    if ($nextstate < self::YYNSTATE) {
                        // we need to shift a non-terminal
                        $this->yyidx++;
                        $x = new Haanga_yyStackEntry;
                        $x->stateno = $nextstate;
                        $x->major = self::$yyRuleInfo[$yyruleno]['lhs'];
                        $this->yystack[$this->yyidx] = $x;
                        continue 2;
                    } elseif ($nextstate == self::YYNSTATE + self::YYNRULE + 1) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        return array_unique($expected);
                    } elseif ($nextstate === self::YY_NO_ACTION) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // input accepted, but not shifted (I guess)
                        return $expected;
                    } else {
                        $yyact = $nextstate;
                    }
                } while (true);
            }
            break;
        } while (true);
        return array_unique($expected);
    }

    /**
     * Based on the parser state and current parser stack, determine whether
     * the lookahead token is possible.
     * 
     * The parser will convert the token value to an error token if not.  This
     * catches some unusual edge cases where the parser would fail.
     * @param int
     * @return bool
     */
    function yy_is_expected_token($token)
    {
        if ($token === 0) {
            return true; // 0 is not part of this
        }
        $state = $this->yystack[$this->yyidx]->stateno;
        if (in_array($token, self::$yyExpectedTokens[$state], true)) {
            return true;
        }
        $stack = $this->yystack;
        $yyidx = $this->yyidx;
        do {
            $yyact = $this->yy_find_shift_action($token);
            if ($yyact >= self::YYNSTATE && $yyact < self::YYNSTATE + self::YYNRULE) {
                // reduce action
                $done = 0;
                do {
                    if ($done++ == 100) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // too much recursion prevents proper detection
                        // so give up
                        return true;
                    }
                    $yyruleno = $yyact - self::YYNSTATE;
                    $this->yyidx -= self::$yyRuleInfo[$yyruleno]['rhs'];
                    $nextstate = $this->yy_find_reduce_action(
                        $this->yystack[$this->yyidx]->stateno,
                        self::$yyRuleInfo[$yyruleno]['lhs']);
                    if (isset(self::$yyExpectedTokens[$nextstate]) &&
                          in_array($token, self::$yyExpectedTokens[$nextstate], true)) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        return true;
                    }
                    if ($nextstate < self::YYNSTATE) {
                        // we need to shift a non-terminal
                        $this->yyidx++;
                        $x = new Haanga_yyStackEntry;
                        $x->stateno = $nextstate;
                        $x->major = self::$yyRuleInfo[$yyruleno]['lhs'];
                        $this->yystack[$this->yyidx] = $x;
                        continue 2;
                    } elseif ($nextstate == self::YYNSTATE + self::YYNRULE + 1) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        if (!$token) {
                            // end of input: this is valid
                            return true;
                        }
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        return false;
                    } elseif ($nextstate === self::YY_NO_ACTION) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // input accepted, but not shifted (I guess)
                        return true;
                    } else {
                        $yyact = $nextstate;
                    }
                } while (true);
            }
            break;
        } while (true);
        $this->yyidx = $yyidx;
        $this->yystack = $stack;
        return true;
    }

    /**
     * Find the appropriate action for a parser given the terminal
     * look-ahead token iLookAhead.
     *
     * If the look-ahead token is YYNOCODE, then check to see if the action is
     * independent of the look-ahead.  If it is, return the action, otherwise
     * return YY_NO_ACTION.
     * @param int The look-ahead token
     */
    function yy_find_shift_action($iLookAhead)
    {
        $stateno = $this->yystack[$this->yyidx]->stateno;
     
        /* if ($this->yyidx < 0) return self::YY_NO_ACTION;  */
        if (!isset(self::$yy_shift_ofst[$stateno])) {
            // no shift actions
            return self::$yy_default[$stateno];
        }
        $i = self::$yy_shift_ofst[$stateno];
        if ($i === self::YY_SHIFT_USE_DFLT) {
            return self::$yy_default[$stateno];
        }
        if ($iLookAhead == self::YYNOCODE) {
            return self::YY_NO_ACTION;
        }
        $i += $iLookAhead;
        if ($i < 0 || $i >= self::YY_SZ_ACTTAB ||
              self::$yy_lookahead[$i] != $iLookAhead) {
            if (count(self::$yyFallback) && $iLookAhead < count(self::$yyFallback)
                   && ($iFallback = self::$yyFallback[$iLookAhead]) != 0) {
                if (self::$yyTraceFILE) {
                    fwrite(self::$yyTraceFILE, self::$yyTracePrompt . "FALLBACK " .
                        self::$yyTokenName[$iLookAhead] . " => " .
                        self::$yyTokenName[$iFallback] . "\n");
                }
                return $this->yy_find_shift_action($iFallback);
            }
            return self::$yy_default[$stateno];
        } else {
            return self::$yy_action[$i];
        }
    }

    /**
     * Find the appropriate action for a parser given the non-terminal
     * look-ahead token $iLookAhead.
     *
     * If the look-ahead token is self::YYNOCODE, then check to see if the action is
     * independent of the look-ahead.  If it is, return the action, otherwise
     * return self::YY_NO_ACTION.
     * @param int Current state number
     * @param int The look-ahead token
     */
    function yy_find_reduce_action($stateno, $iLookAhead)
    {
        /* $stateno = $this->yystack[$this->yyidx]->stateno; */

        if (!isset(self::$yy_reduce_ofst[$stateno])) {
            return self::$yy_default[$stateno];
        }
        $i = self::$yy_reduce_ofst[$stateno];
        if ($i == self::YY_REDUCE_USE_DFLT) {
            return self::$yy_default[$stateno];
        }
        if ($iLookAhead == self::YYNOCODE) {
            return self::YY_NO_ACTION;
        }
        $i += $iLookAhead;
        if ($i < 0 || $i >= self::YY_SZ_ACTTAB ||
              self::$yy_lookahead[$i] != $iLookAhead) {
            return self::$yy_default[$stateno];
        } else {
            return self::$yy_action[$i];
        }
    }

    /**
     * Perform a shift action.
     * @param int The new state to shift in
     * @param int The major token to shift in
     * @param mixed the minor token to shift in
     */
    function yy_shift($yyNewState, $yyMajor, $yypMinor)
    {
        $this->yyidx++;
        if ($this->yyidx >= self::YYSTACKDEPTH) {
            $this->yyidx--;
            if (self::$yyTraceFILE) {
                fprintf(self::$yyTraceFILE, "%sStack Overflow!\n", self::$yyTracePrompt);
            }
            while ($this->yyidx >= 0) {
                $this->yy_pop_parser_stack();
            }
            /* Here code is inserted which will execute if the parser
            ** stack ever overflows */
            return;
        }
        $yytos = new Haanga_yyStackEntry;
        $yytos->stateno = $yyNewState;
        $yytos->major = $yyMajor;
        $yytos->minor = $yypMinor;
        array_push($this->yystack, $yytos);
        if (self::$yyTraceFILE && $this->yyidx > 0) {
            fprintf(self::$yyTraceFILE, "%sShift %d\n", self::$yyTracePrompt,
                $yyNewState);
            fprintf(self::$yyTraceFILE, "%sStack:", self::$yyTracePrompt);
            for($i = 1; $i <= $this->yyidx; $i++) {
                fprintf(self::$yyTraceFILE, " %s",
                    self::$yyTokenName[$this->yystack[$i]->major]);
            }
            fwrite(self::$yyTraceFILE,"\n");
        }
    }

    /**
     * The following table contains information about every rule that
     * is used during the reduce.
     *
     * <pre>
     * array(
     *  array(
     *   int $lhs;         Symbol on the left-hand side of the rule
     *   int $nrhs;     Number of right-hand side symbols in the rule
     *  ),...
     * );
     * </pre>
     */
    static public $yyRuleInfo = array(
  array( 'lhs' => 76, 'rhs' => 1 ),
  array( 'lhs' => 77, 'rhs' => 2 ),
  array( 'lhs' => 77, 'rhs' => 0 ),
  array( 'lhs' => 78, 'rhs' => 2 ),
  array( 'lhs' => 78, 'rhs' => 1 ),
  array( 'lhs' => 78, 'rhs' => 2 ),
  array( 'lhs' => 78, 'rhs' => 3 ),
  array( 'lhs' => 79, 'rhs' => 3 ),
  array( 'lhs' => 79, 'rhs' => 2 ),
  array( 'lhs' => 79, 'rhs' => 1 ),
  array( 'lhs' => 79, 'rhs' => 1 ),
  array( 'lhs' => 79, 'rhs' => 1 ),
  array( 'lhs' => 79, 'rhs' => 1 ),
  array( 'lhs' => 79, 'rhs' => 1 ),
  array( 'lhs' => 79, 'rhs' => 3 ),
  array( 'lhs' => 79, 'rhs' => 1 ),
  array( 'lhs' => 79, 'rhs' => 1 ),
  array( 'lhs' => 79, 'rhs' => 1 ),
  array( 'lhs' => 79, 'rhs' => 7 ),
  array( 'lhs' => 88, 'rhs' => 2 ),
  array( 'lhs' => 88, 'rhs' => 4 ),
  array( 'lhs' => 88, 'rhs' => 3 ),
  array( 'lhs' => 88, 'rhs' => 5 ),
  array( 'lhs' => 88, 'rhs' => 6 ),
  array( 'lhs' => 88, 'rhs' => 7 ),
  array( 'lhs' => 88, 'rhs' => 6 ),
  array( 'lhs' => 89, 'rhs' => 9 ),
  array( 'lhs' => 82, 'rhs' => 1 ),
  array( 'lhs' => 82, 'rhs' => 2 ),
  array( 'lhs' => 95, 'rhs' => 5 ),
  array( 'lhs' => 95, 'rhs' => 7 ),
  array( 'lhs' => 83, 'rhs' => 5 ),
  array( 'lhs' => 83, 'rhs' => 9 ),
  array( 'lhs' => 87, 'rhs' => 7 ),
  array( 'lhs' => 87, 'rhs' => 11 ),
  array( 'lhs' => 84, 'rhs' => 6 ),
  array( 'lhs' => 84, 'rhs' => 7 ),
  array( 'lhs' => 84, 'rhs' => 10 ),
  array( 'lhs' => 84, 'rhs' => 11 ),
  array( 'lhs' => 90, 'rhs' => 8 ),
  array( 'lhs' => 90, 'rhs' => 12 ),
  array( 'lhs' => 90, 'rhs' => 8 ),
  array( 'lhs' => 90, 'rhs' => 12 ),
  array( 'lhs' => 85, 'rhs' => 7 ),
  array( 'lhs' => 85, 'rhs' => 8 ),
  array( 'lhs' => 85, 'rhs' => 7 ),
  array( 'lhs' => 85, 'rhs' => 8 ),
  array( 'lhs' => 86, 'rhs' => 7 ),
  array( 'lhs' => 93, 'rhs' => 6 ),
  array( 'lhs' => 80, 'rhs' => 3 ),
  array( 'lhs' => 80, 'rhs' => 1 ),
  array( 'lhs' => 98, 'rhs' => 3 ),
  array( 'lhs' => 98, 'rhs' => 1 ),
  array( 'lhs' => 92, 'rhs' => 2 ),
  array( 'lhs' => 92, 'rhs' => 3 ),
  array( 'lhs' => 92, 'rhs' => 1 ),
  array( 'lhs' => 81, 'rhs' => 1 ),
  array( 'lhs' => 81, 'rhs' => 1 ),
  array( 'lhs' => 81, 'rhs' => 1 ),
  array( 'lhs' => 97, 'rhs' => 1 ),
  array( 'lhs' => 97, 'rhs' => 1 ),
  array( 'lhs' => 97, 'rhs' => 1 ),
  array( 'lhs' => 97, 'rhs' => 1 ),
  array( 'lhs' => 94, 'rhs' => 3 ),
  array( 'lhs' => 94, 'rhs' => 2 ),
  array( 'lhs' => 94, 'rhs' => 2 ),
  array( 'lhs' => 94, 'rhs' => 3 ),
  array( 'lhs' => 94, 'rhs' => 3 ),
  array( 'lhs' => 99, 'rhs' => 2 ),
  array( 'lhs' => 99, 'rhs' => 1 ),
  array( 'lhs' => 96, 'rhs' => 2 ),
  array( 'lhs' => 96, 'rhs' => 3 ),
  array( 'lhs' => 96, 'rhs' => 3 ),
  array( 'lhs' => 96, 'rhs' => 3 ),
  array( 'lhs' => 96, 'rhs' => 3 ),
  array( 'lhs' => 96, 'rhs' => 3 ),
  array( 'lhs' => 96, 'rhs' => 3 ),
  array( 'lhs' => 96, 'rhs' => 1 ),
  array( 'lhs' => 91, 'rhs' => 3 ),
  array( 'lhs' => 91, 'rhs' => 3 ),
  array( 'lhs' => 91, 'rhs' => 4 ),
  array( 'lhs' => 91, 'rhs' => 1 ),
  array( 'lhs' => 91, 'rhs' => 1 ),
    );

    /**
     * The following table contains a mapping of reduce action to method name
     * that handles the reduction.
     * 
     * If a rule is not set, it has no handler.
     */
    static public $yyReduceMap = array(
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        63 => 8,
        9 => 9,
        10 => 9,
        11 => 9,
        12 => 9,
        13 => 9,
        15 => 9,
        16 => 9,
        17 => 9,
        27 => 9,
        52 => 9,
        69 => 9,
        77 => 9,
        81 => 9,
        82 => 9,
        14 => 14,
        18 => 18,
        19 => 19,
        20 => 20,
        21 => 21,
        22 => 22,
        23 => 23,
        24 => 24,
        25 => 25,
        26 => 26,
        28 => 28,
        29 => 29,
        30 => 30,
        31 => 31,
        32 => 32,
        33 => 33,
        34 => 34,
        35 => 35,
        36 => 36,
        37 => 37,
        38 => 38,
        39 => 39,
        40 => 40,
        41 => 41,
        42 => 42,
        43 => 43,
        45 => 43,
        44 => 44,
        46 => 44,
        47 => 47,
        48 => 48,
        49 => 49,
        54 => 49,
        50 => 50,
        55 => 50,
        51 => 51,
        53 => 53,
        56 => 56,
        57 => 57,
        60 => 57,
        58 => 58,
        62 => 58,
        59 => 59,
        61 => 61,
        64 => 64,
        65 => 64,
        66 => 66,
        67 => 66,
        68 => 68,
        70 => 70,
        71 => 71,
        72 => 71,
        73 => 71,
        75 => 71,
        74 => 74,
        76 => 76,
        78 => 78,
        79 => 79,
        80 => 80,
    );
    /* Beginning here are the reduction cases.  A typical example
    ** follows:
    **  #line <lineno> <grammarfile>
    **   function yy_r0($yymsp){ ... }           // User supplied code
    **  #line <lineno> <thisfile>
    */
#line 79 "lib/Haanga/Compiler/Parser.y"
    function yy_r0(){ $this->body = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1598 "lib/Haanga/Compiler/Parser.php"
#line 81 "lib/Haanga/Compiler/Parser.y"
    function yy_r1(){ $this->_retvalue=$this->yystack[$this->yyidx + -1]->minor; $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1601 "lib/Haanga/Compiler/Parser.php"
#line 82 "lib/Haanga/Compiler/Parser.y"
    function yy_r2(){ $this->_retvalue = array();     }
#line 1604 "lib/Haanga/Compiler/Parser.php"
#line 85 "lib/Haanga/Compiler/Parser.y"
    function yy_r3(){ if (count($this->yystack[$this->yyidx + 0]->minor)) $this->yystack[$this->yyidx + 0]->minor['line'] = $this->lex->getLine();  $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1607 "lib/Haanga/Compiler/Parser.php"
#line 86 "lib/Haanga/Compiler/Parser.y"
    function yy_r4(){ $this->_retvalue = array('operation' => 'html', 'html' => $this->yystack[$this->yyidx + 0]->minor, 'line' => $this->lex->getLine() );     }
#line 1610 "lib/Haanga/Compiler/Parser.php"
#line 87 "lib/Haanga/Compiler/Parser.y"
    function yy_r5(){ $this->yystack[$this->yyidx + 0]->minor=rtrim($this->yystack[$this->yyidx + 0]->minor); $this->_retvalue = array('operation' => 'comment', 'comment' => substr($this->yystack[$this->yyidx + 0]->minor, 0, strlen($this->yystack[$this->yyidx + 0]->minor)-2));     }
#line 1613 "lib/Haanga/Compiler/Parser.php"
#line 88 "lib/Haanga/Compiler/Parser.y"
    function yy_r6(){ $this->_retvalue = array('operation' => 'print_var', 'variable' => $this->yystack[$this->yyidx + -1]->minor, 'line' => $this->lex->getLine() );     }
#line 1616 "lib/Haanga/Compiler/Parser.php"
#line 90 "lib/Haanga/Compiler/Parser.y"
    function yy_r7(){ $this->_retvalue = array('operation' => 'base', $this->yystack[$this->yyidx + -1]->minor);     }
#line 1619 "lib/Haanga/Compiler/Parser.php"
#line 91 "lib/Haanga/Compiler/Parser.y"
    function yy_r8(){ $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;     }
#line 1622 "lib/Haanga/Compiler/Parser.php"
#line 92 "lib/Haanga/Compiler/Parser.y"
    function yy_r9(){ $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1625 "lib/Haanga/Compiler/Parser.php"
#line 97 "lib/Haanga/Compiler/Parser.y"
    function yy_r14(){ $this->_retvalue = array('operation' => 'include', $this->yystack[$this->yyidx + -1]->minor);     }
#line 1628 "lib/Haanga/Compiler/Parser.php"
#line 101 "lib/Haanga/Compiler/Parser.y"
    function yy_r18(){ $this->_retvalue = array('operation' => 'autoescape', 'value' => strtolower(@$this->yystack[$this->yyidx + -5]->minor), 'body' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1631 "lib/Haanga/Compiler/Parser.php"
#line 106 "lib/Haanga/Compiler/Parser.y"
    function yy_r19(){ $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -1]->minor, 'list'=>array());     }
#line 1634 "lib/Haanga/Compiler/Parser.php"
#line 107 "lib/Haanga/Compiler/Parser.y"
    function yy_r20(){ $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -3]->minor, 'as' => $this->yystack[$this->yyidx + -1]->minor, 'list'=>array());     }
#line 1637 "lib/Haanga/Compiler/Parser.php"
#line 108 "lib/Haanga/Compiler/Parser.y"
    function yy_r21(){ $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -2]->minor, 'list' => $this->yystack[$this->yyidx + -1]->minor);     }
#line 1640 "lib/Haanga/Compiler/Parser.php"
#line 109 "lib/Haanga/Compiler/Parser.y"
    function yy_r22(){ $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -4]->minor, 'as' => $this->yystack[$this->yyidx + -1]->minor, 'list' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1643 "lib/Haanga/Compiler/Parser.php"
#line 111 "lib/Haanga/Compiler/Parser.y"
    function yy_r23(){ if ('end'.$this->yystack[$this->yyidx + -5]->minor != $this->yystack[$this->yyidx + -1]->minor) { $this->error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor); } $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor, 'list' => array());    }
#line 1646 "lib/Haanga/Compiler/Parser.php"
#line 112 "lib/Haanga/Compiler/Parser.y"
    function yy_r24(){ if ('endbuffer' != $this->yystack[$this->yyidx + -1]->minor) { $this->error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor); } $this->_retvalue = array('operation' => 'buffer', 'name' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);    }
#line 1649 "lib/Haanga/Compiler/Parser.php"
#line 113 "lib/Haanga/Compiler/Parser.y"
    function yy_r25(){ if ('endspacefull' != $this->yystack[$this->yyidx + -1]->minor) { $this->error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor); } $this->_retvalue = array('operation' => 'spacefull', 'body' => $this->yystack[$this->yyidx + -3]->minor);    }
#line 1652 "lib/Haanga/Compiler/Parser.php"
#line 116 "lib/Haanga/Compiler/Parser.y"
    function yy_r26(){ $this->_retvalue = array('operation' => 'alias', 'var' => $this->yystack[$this->yyidx + -7]->minor, 'as' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1655 "lib/Haanga/Compiler/Parser.php"
#line 120 "lib/Haanga/Compiler/Parser.y"
    function yy_r28(){
    if (!is_file($this->yystack[$this->yyidx + 0]->minor)) {
        $this->error($this->yystack[$this->yyidx + 0]->minor." is not a valid file"); 
    } 
    require_once $this->yystack[$this->yyidx + 0]->minor;
    }
#line 1663 "lib/Haanga/Compiler/Parser.php"
#line 128 "lib/Haanga/Compiler/Parser.y"
    function yy_r29(){
    /* Try to get the variable */
    $var = $this->compiler->get_context(is_array($this->yystack[$this->yyidx + -1]->minor[0]) ? $this->yystack[$this->yyidx + -1]->minor[0] : array($this->yystack[$this->yyidx + -1]->minor[0]));
    if (is_array($var)) {
        /* let's check if it is an object or array */
        $this->compiler->set_context($this->yystack[$this->yyidx + -3]->minor, current($var));
    }

    $this->_retvalue = array('operation' => 'loop', 'variable' => $this->yystack[$this->yyidx + -3]->minor, 'index' => NULL, 'array' => $this->yystack[$this->yyidx + -1]->minor);
    }
#line 1675 "lib/Haanga/Compiler/Parser.php"
#line 137 "lib/Haanga/Compiler/Parser.y"
    function yy_r30(){
    /* Try to get the variable */
    $var = $this->compiler->get_context(is_array($this->yystack[$this->yyidx + -1]->minor[0]) ? $this->yystack[$this->yyidx + -1]->minor[0] : array($this->yystack[$this->yyidx + -1]->minor[0]));
    if (is_array($var)) {
        /* let's check if it is an object or array */
        $this->compiler->set_context($this->yystack[$this->yyidx + -3]->minor, current($var));
    }
    $this->_retvalue = array('operation' => 'loop', 'variable' => $this->yystack[$this->yyidx + -3]->minor, 'index' => $this->yystack[$this->yyidx + -5]->minor, 'array' => $this->yystack[$this->yyidx + -1]->minor);
    }
#line 1686 "lib/Haanga/Compiler/Parser.php"
#line 146 "lib/Haanga/Compiler/Parser.y"
    function yy_r31(){ 
    $this->_retvalue = $this->yystack[$this->yyidx + -4]->minor;
    $this->_retvalue['body'] = $this->yystack[$this->yyidx + -3]->minor;
    }
#line 1692 "lib/Haanga/Compiler/Parser.php"
#line 151 "lib/Haanga/Compiler/Parser.y"
    function yy_r32(){ 
    $this->_retvalue = $this->yystack[$this->yyidx + -8]->minor;
    $this->_retvalue['body']  = $this->yystack[$this->yyidx + -7]->minor;
    $this->_retvalue['empty'] = $this->yystack[$this->yyidx + -3]->minor;
    }
#line 1699 "lib/Haanga/Compiler/Parser.php"
#line 157 "lib/Haanga/Compiler/Parser.y"
    function yy_r33(){ $this->_retvalue = array('operation' => 'if', 'expr' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1702 "lib/Haanga/Compiler/Parser.php"
#line 158 "lib/Haanga/Compiler/Parser.y"
    function yy_r34(){ $this->_retvalue = array('operation' => 'if', 'expr' => $this->yystack[$this->yyidx + -9]->minor, 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1705 "lib/Haanga/Compiler/Parser.php"
#line 161 "lib/Haanga/Compiler/Parser.y"
    function yy_r35(){ 
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1710 "lib/Haanga/Compiler/Parser.php"
#line 165 "lib/Haanga/Compiler/Parser.y"
    function yy_r36(){ 
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -3]->minor, 'check' => $this->yystack[$this->yyidx + -5]->minor);
    }
#line 1715 "lib/Haanga/Compiler/Parser.php"
#line 168 "lib/Haanga/Compiler/Parser.y"
    function yy_r37(){ 
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1720 "lib/Haanga/Compiler/Parser.php"
#line 172 "lib/Haanga/Compiler/Parser.y"
    function yy_r38(){ 
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -7]->minor, 'check' => $this->yystack[$this->yyidx + -9]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1725 "lib/Haanga/Compiler/Parser.php"
#line 177 "lib/Haanga/Compiler/Parser.y"
    function yy_r39(){  $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '==', 1 => $this->yystack[$this->yyidx + -6]->minor, 2 => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1728 "lib/Haanga/Compiler/Parser.php"
#line 178 "lib/Haanga/Compiler/Parser.y"
    function yy_r40(){  $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '==', 1 => $this->yystack[$this->yyidx + -10]->minor, 2 => $this->yystack[$this->yyidx + -9]->minor, 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1731 "lib/Haanga/Compiler/Parser.php"
#line 179 "lib/Haanga/Compiler/Parser.y"
    function yy_r41(){  $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '!=', 1 => $this->yystack[$this->yyidx + -6]->minor, 2 => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1734 "lib/Haanga/Compiler/Parser.php"
#line 180 "lib/Haanga/Compiler/Parser.y"
    function yy_r42(){  $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '!=', 1 => $this->yystack[$this->yyidx + -10]->minor, 2 => $this->yystack[$this->yyidx + -9]->minor, 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1737 "lib/Haanga/Compiler/Parser.php"
#line 184 "lib/Haanga/Compiler/Parser.y"
    function yy_r43(){ $this->_retvalue = array('operation' => 'block', 'name' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1740 "lib/Haanga/Compiler/Parser.php"
#line 186 "lib/Haanga/Compiler/Parser.y"
    function yy_r44(){ $this->_retvalue = array('operation' => 'block', 'name' => $this->yystack[$this->yyidx + -6]->minor, 'body' => $this->yystack[$this->yyidx + -4]->minor);     }
#line 1743 "lib/Haanga/Compiler/Parser.php"
#line 193 "lib/Haanga/Compiler/Parser.y"
    function yy_r47(){ $this->_retvalue = array('operation' => 'filter', 'functions' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1746 "lib/Haanga/Compiler/Parser.php"
#line 196 "lib/Haanga/Compiler/Parser.y"
    function yy_r48(){ $this->_retvalue=array('operation' => 'regroup', 'array' => $this->yystack[$this->yyidx + -4]->minor, 'row' => $this->yystack[$this->yyidx + -2]->minor, 'as' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1749 "lib/Haanga/Compiler/Parser.php"
#line 199 "lib/Haanga/Compiler/Parser.y"
    function yy_r49(){ $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor; $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1752 "lib/Haanga/Compiler/Parser.php"
#line 200 "lib/Haanga/Compiler/Parser.y"
    function yy_r50(){ $this->_retvalue = array($this->yystack[$this->yyidx + 0]->minor);     }
#line 1755 "lib/Haanga/Compiler/Parser.php"
#line 202 "lib/Haanga/Compiler/Parser.y"
    function yy_r51(){ $this->_retvalue = array($this->yystack[$this->yyidx + -2]->minor, 'args'=>array($this->yystack[$this->yyidx + 0]->minor));     }
#line 1758 "lib/Haanga/Compiler/Parser.php"
#line 206 "lib/Haanga/Compiler/Parser.y"
    function yy_r53(){ $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor; $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1761 "lib/Haanga/Compiler/Parser.php"
#line 212 "lib/Haanga/Compiler/Parser.y"
    function yy_r56(){ $this->_retvalue = array('var' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1764 "lib/Haanga/Compiler/Parser.php"
#line 213 "lib/Haanga/Compiler/Parser.y"
    function yy_r57(){ $this->_retvalue = array('number' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1767 "lib/Haanga/Compiler/Parser.php"
#line 214 "lib/Haanga/Compiler/Parser.y"
    function yy_r58(){ $this->_retvalue = array('string' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1770 "lib/Haanga/Compiler/Parser.php"
#line 216 "lib/Haanga/Compiler/Parser.y"
    function yy_r59(){ $this->_retvalue = array('var_filter' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1773 "lib/Haanga/Compiler/Parser.php"
#line 218 "lib/Haanga/Compiler/Parser.y"
    function yy_r61(){ $this->_retvalue = trim(@$this->yystack[$this->yyidx + 0]->minor);     }
#line 1776 "lib/Haanga/Compiler/Parser.php"
#line 223 "lib/Haanga/Compiler/Parser.y"
    function yy_r64(){  $this->_retvalue = "";     }
#line 1779 "lib/Haanga/Compiler/Parser.php"
#line 225 "lib/Haanga/Compiler/Parser.y"
    function yy_r66(){  $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;     }
#line 1782 "lib/Haanga/Compiler/Parser.php"
#line 227 "lib/Haanga/Compiler/Parser.y"
    function yy_r68(){ $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor.$this->yystack[$this->yyidx + 0]->minor;     }
#line 1785 "lib/Haanga/Compiler/Parser.php"
#line 231 "lib/Haanga/Compiler/Parser.y"
    function yy_r70(){ $this->_retvalue = array('op_expr' => 'not', $this->yystack[$this->yyidx + 0]->minor);     }
#line 1788 "lib/Haanga/Compiler/Parser.php"
#line 232 "lib/Haanga/Compiler/Parser.y"
    function yy_r71(){ $this->_retvalue = array('op_expr' => @$this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);     }
#line 1791 "lib/Haanga/Compiler/Parser.php"
#line 235 "lib/Haanga/Compiler/Parser.y"
    function yy_r74(){ $this->_retvalue = array('op_expr' => trim(@$this->yystack[$this->yyidx + -1]->minor), $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);     }
#line 1794 "lib/Haanga/Compiler/Parser.php"
#line 237 "lib/Haanga/Compiler/Parser.y"
    function yy_r76(){ $this->_retvalue = array('op_expr' => 'expr', $this->yystack[$this->yyidx + -1]->minor);     }
#line 1797 "lib/Haanga/Compiler/Parser.php"
#line 241 "lib/Haanga/Compiler/Parser.y"
    function yy_r78(){ if (!is_array($this->yystack[$this->yyidx + -2]->minor)) { $this->_retvalue = array($this->yystack[$this->yyidx + -2]->minor); } else { $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor; }  $this->_retvalue[]=array('object' => $this->yystack[$this->yyidx + 0]->minor);    }
#line 1800 "lib/Haanga/Compiler/Parser.php"
#line 242 "lib/Haanga/Compiler/Parser.y"
    function yy_r79(){ if (!is_array($this->yystack[$this->yyidx + -2]->minor)) { $this->_retvalue = array($this->yystack[$this->yyidx + -2]->minor); } else { $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor; } $this->_retvalue[] = ($this->compiler->var_is_object($this->_retvalue)) ? array('object' => $this->yystack[$this->yyidx + 0]->minor) : $this->yystack[$this->yyidx + 0]->minor;    }
#line 1803 "lib/Haanga/Compiler/Parser.php"
#line 243 "lib/Haanga/Compiler/Parser.y"
    function yy_r80(){ if (!is_array($this->yystack[$this->yyidx + -3]->minor)) { $this->_retvalue = array($this->yystack[$this->yyidx + -3]->minor); } else { $this->_retvalue = $this->yystack[$this->yyidx + -3]->minor; }  $this->_retvalue[]=$this->yystack[$this->yyidx + -1]->minor;    }
#line 1806 "lib/Haanga/Compiler/Parser.php"

    /**
     * placeholder for the left hand side in a reduce operation.
     * 
     * For a parser with a rule like this:
     * <pre>
     * rule(A) ::= B. { A = 1; }
     * </pre>
     * 
     * The parser will translate to something like:
     * 
     * <code>
     * function yy_r0(){$this->_retvalue = 1;}
     * </code>
     */
    private $_retvalue;

    /**
     * Perform a reduce action and the shift that must immediately
     * follow the reduce.
     * 
     * For a rule such as:
     * 
     * <pre>
     * A ::= B blah C. { dosomething(); }
     * </pre>
     * 
     * This function will first call the action, if any, ("dosomething();" in our
     * example), and then it will pop three states from the stack,
     * one for each entry on the right-hand side of the expression
     * (B, blah, and C in our example rule), and then push the result of the action
     * back on to the stack with the resulting state reduced to (as described in the .out
     * file)
     * @param int Number of the rule by which to reduce
     */
    function yy_reduce($yyruleno)
    {
        //int $yygoto;                     /* The next state */
        //int $yyact;                      /* The next action */
        //mixed $yygotominor;        /* The LHS of the rule reduced */
        //Haanga_yyStackEntry $yymsp;            /* The top of the parser's stack */
        //int $yysize;                     /* Amount to pop the stack */
        $yymsp = $this->yystack[$this->yyidx];
        if (self::$yyTraceFILE && $yyruleno >= 0 
              && $yyruleno < count(self::$yyRuleName)) {
            fprintf(self::$yyTraceFILE, "%sReduce (%d) [%s].\n",
                self::$yyTracePrompt, $yyruleno,
                self::$yyRuleName[$yyruleno]);
        }

        $this->_retvalue = $yy_lefthand_side = null;
        if (array_key_exists($yyruleno, self::$yyReduceMap)) {
            // call the action
            $this->_retvalue = null;
            $this->{'yy_r' . self::$yyReduceMap[$yyruleno]}();
            $yy_lefthand_side = $this->_retvalue;
        }
        $yygoto = self::$yyRuleInfo[$yyruleno]['lhs'];
        $yysize = self::$yyRuleInfo[$yyruleno]['rhs'];
        $this->yyidx -= $yysize;
        for($i = $yysize; $i; $i--) {
            // pop all of the right-hand side parameters
            array_pop($this->yystack);
        }
        $yyact = $this->yy_find_reduce_action($this->yystack[$this->yyidx]->stateno, $yygoto);
        if ($yyact < self::YYNSTATE) {
            /* If we are not debugging and the reduce action popped at least
            ** one element off the stack, then we can push the new element back
            ** onto the stack here, and skip the stack overflow test in yy_shift().
            ** That gives a significant speed improvement. */
            if (!self::$yyTraceFILE && $yysize) {
                $this->yyidx++;
                $x = new Haanga_yyStackEntry;
                $x->stateno = $yyact;
                $x->major = $yygoto;
                $x->minor = $yy_lefthand_side;
                $this->yystack[$this->yyidx] = $x;
            } else {
                $this->yy_shift($yyact, $yygoto, $yy_lefthand_side);
            }
        } elseif ($yyact == self::YYNSTATE + self::YYNRULE + 1) {
            $this->yy_accept();
        }
    }

    /**
     * The following code executes when the parse fails
     * 
     * Code from %parse_fail is inserted here
     */
    function yy_parse_failed()
    {
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sFail!\n", self::$yyTracePrompt);
        }
        while ($this->yyidx >= 0) {
            $this->yy_pop_parser_stack();
        }
        /* Here code is inserted which will be executed whenever the
        ** parser fails */
    }

    /**
     * The following code executes when a syntax error first occurs.
     * 
     * %syntax_error code is inserted here
     * @param int The major type of the error token
     * @param mixed The minor type of the error token
     */
    function yy_syntax_error($yymajor, $TOKEN)
    {
#line 70 "lib/Haanga/Compiler/Parser.y"

    $expect = array();
    foreach ($this->yy_get_expected_tokens($yymajor) as $token) {
        $expect[] = self::$yyTokenName[$token];
    }
    $this->Error('Unexpected ' . $this->tokenName($yymajor) . '(' . $TOKEN. '), expected one of: ' . implode(',', $expect));
#line 1926 "lib/Haanga/Compiler/Parser.php"
    }

    /**
     * The following is executed when the parser accepts
     * 
     * %parse_accept code is inserted here
     */
    function yy_accept()
    {
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sAccept!\n", self::$yyTracePrompt);
        }
        while ($this->yyidx >= 0) {
            $stack = $this->yy_pop_parser_stack();
        }
        /* Here code is inserted which will be executed whenever the
        ** parser accepts */
#line 57 "lib/Haanga/Compiler/Parser.y"

#line 1947 "lib/Haanga/Compiler/Parser.php"
    }

    /**
     * The main parser program.
     * 
     * The first argument is the major token number.  The second is
     * the token value string as scanned from the input.
     *
     * @param int the token number
     * @param mixed the token value
     * @param mixed any extra arguments that should be passed to handlers
     */
    function doParse($yymajor, $yytokenvalue)
    {
//        $yyact;            /* The parser action. */
//        $yyendofinput;     /* True if we are at the end of input */
        $yyerrorhit = 0;   /* True if yymajor has invoked an error */
        
        /* (re)initialize the parser, if necessary */
        if ($this->yyidx === null || $this->yyidx < 0) {
            /* if ($yymajor == 0) return; // not sure why this was here... */
            $this->yyidx = 0;
            $this->yyerrcnt = -1;
            $x = new Haanga_yyStackEntry;
            $x->stateno = 0;
            $x->major = 0;
            $this->yystack = array();
            array_push($this->yystack, $x);
        }
        $yyendofinput = ($yymajor==0);
        
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sInput %s\n",
                self::$yyTracePrompt, self::$yyTokenName[$yymajor]);
        }
        
        do {
            $yyact = $this->yy_find_shift_action($yymajor);
            if ($yymajor < self::YYERRORSYMBOL &&
                  !$this->yy_is_expected_token($yymajor)) {
                // force a syntax error
                $yyact = self::YY_ERROR_ACTION;
            }
            if ($yyact < self::YYNSTATE) {
                $this->yy_shift($yyact, $yymajor, $yytokenvalue);
                $this->yyerrcnt--;
                if ($yyendofinput && $this->yyidx >= 0) {
                    $yymajor = 0;
                } else {
                    $yymajor = self::YYNOCODE;
                }
            } elseif ($yyact < self::YYNSTATE + self::YYNRULE) {
                $this->yy_reduce($yyact - self::YYNSTATE);
            } elseif ($yyact == self::YY_ERROR_ACTION) {
                if (self::$yyTraceFILE) {
                    fprintf(self::$yyTraceFILE, "%sSyntax Error!\n",
                        self::$yyTracePrompt);
                }
                if (self::YYERRORSYMBOL) {
                    /* A syntax error has occurred.
                    ** The response to an error depends upon whether or not the
                    ** grammar defines an error token "ERROR".  
                    **
                    ** This is what we do if the grammar does define ERROR:
                    **
                    **  * Call the %syntax_error function.
                    **
                    **  * Begin popping the stack until we enter a state where
                    **    it is legal to shift the error symbol, then shift
                    **    the error symbol.
                    **
                    **  * Set the error count to three.
                    **
                    **  * Begin accepting and shifting new tokens.  No new error
                    **    processing will occur until three tokens have been
                    **    shifted successfully.
                    **
                    */
                    if ($this->yyerrcnt < 0) {
                        $this->yy_syntax_error($yymajor, $yytokenvalue);
                    }
                    $yymx = $this->yystack[$this->yyidx]->major;
                    if ($yymx == self::YYERRORSYMBOL || $yyerrorhit ){
                        if (self::$yyTraceFILE) {
                            fprintf(self::$yyTraceFILE, "%sDiscard input token %s\n",
                                self::$yyTracePrompt, self::$yyTokenName[$yymajor]);
                        }
                        $this->yy_destructor($yymajor, $yytokenvalue);
                        $yymajor = self::YYNOCODE;
                    } else {
                        while ($this->yyidx >= 0 &&
                                 $yymx != self::YYERRORSYMBOL &&
        ($yyact = $this->yy_find_shift_action(self::YYERRORSYMBOL)) >= self::YYNSTATE
                              ){
                            $this->yy_pop_parser_stack();
                        }
                        if ($this->yyidx < 0 || $yymajor==0) {
                            $this->yy_destructor($yymajor, $yytokenvalue);
                            $this->yy_parse_failed();
                            $yymajor = self::YYNOCODE;
                        } elseif ($yymx != self::YYERRORSYMBOL) {
                            $u2 = 0;
                            $this->yy_shift($yyact, self::YYERRORSYMBOL, $u2);
                        }
                    }
                    $this->yyerrcnt = 3;
                    $yyerrorhit = 1;
                } else {
                    /* YYERRORSYMBOL is not defined */
                    /* This is what we do if the grammar does not define ERROR:
                    **
                    **  * Report an error message, and throw away the input token.
                    **
                    **  * If the input token is $, then fail the parse.
                    **
                    ** As before, subsequent error messages are suppressed until
                    ** three input tokens have been successfully shifted.
                    */
                    if ($this->yyerrcnt <= 0) {
                        $this->yy_syntax_error($yymajor, $yytokenvalue);
                    }
                    $this->yyerrcnt = 3;
                    $this->yy_destructor($yymajor, $yytokenvalue);
                    if ($yyendofinput) {
                        $this->yy_parse_failed();
                    }
                    $yymajor = self::YYNOCODE;
                }
            } else {
                $this->yy_accept();
                $yymajor = self::YYNOCODE;
            }            
        } while ($yymajor != self::YYNOCODE && $this->yyidx >= 0);
    }
}