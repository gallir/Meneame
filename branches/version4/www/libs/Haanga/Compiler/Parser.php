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
    const T_COMMENT                      = 18;
    const T_PRINT_OPEN                   = 19;
    const T_PRINT_CLOSE                  = 20;
    const T_EXTENDS                      = 21;
    const T_CLOSE_TAG                    = 22;
    const T_INCLUDE                      = 23;
    const T_AUTOESCAPE                   = 24;
    const T_CUSTOM_END                   = 25;
    const T_CUSTOM_TAG                   = 26;
    const T_AS                           = 27;
    const T_CUSTOM_BLOCK                 = 28;
    const T_SPACEFULL                    = 29;
    const T_WITH                         = 30;
    const T_LOAD                         = 31;
    const T_FOR                          = 32;
    const T_COMMA                        = 33;
    const T_EMPTY                        = 34;
    const T_IF                           = 35;
    const T_ELSE                         = 36;
    const T_IFCHANGED                    = 37;
    const T_IFEQUAL                      = 38;
    const T_IFNOTEQUAL                   = 39;
    const T_BLOCK                        = 40;
    const T_NUMERIC                      = 41;
    const T_FILTER                       = 42;
    const T_REGROUP                      = 43;
    const T_BY                           = 44;
    const T_PIPE                         = 45;
    const T_COLON                        = 46;
    const T_TRUE                         = 47;
    const T_FALSE                        = 48;
    const T_STRING                       = 49;
    const T_INTL                         = 50;
    const T_RPARENT                      = 51;
    const T_LPARENT                      = 52;
    const T_OBJ                          = 53;
    const T_ALPHA                        = 54;
    const T_DOT                          = 55;
    const T_BRACKETS_OPEN                = 56;
    const T_BRACKETS_CLOSE               = 57;
    const YY_NO_ACTION = 317;
    const YY_ACCEPT_ACTION = 316;
    const YY_ERROR_ACTION = 315;

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
    const YY_SZ_ACTTAB = 1054;
static public $yy_action = array(
 /*     0 */    40,  181,   44,   72,  135,   34,  176,   35,  152,   59,
 /*    10 */    83,   64,  127,  111,   26,  136,   36,   30,   31,   51,
 /*    20 */   208,   48,   49,  111,   40,  140,   44,   72,  167,   34,
 /*    30 */   206,   35,  152,   59,   83,   64,  124,  151,   26,   96,
 /*    40 */    36,   30,   31,   51,  190,   48,   49,  111,   40,   71,
 /*    50 */    44,   72,  145,   34,  206,   35,  152,   59,   83,   64,
 /*    60 */   125,  226,   26,  149,   36,   30,   31,   51,  182,   48,
 /*    70 */    49,  111,   40,   58,   44,   72,  134,   34,  206,   35,
 /*    80 */   152,   59,   83,   64,  235,   95,   26,  156,   36,   30,
 /*    90 */    31,   51,   92,   48,   49,  196,   40,   76,   44,   72,
 /*   100 */   154,   34,  185,   35,  152,   59,   83,   64,   50,   50,
 /*   110 */    26,  155,   36,   30,   31,   51,  224,   48,   49,  216,
 /*   120 */    40,   50,   44,   72,  160,   34,   87,   35,  152,   59,
 /*   130 */    83,   64,   75,   50,   26,  164,   36,   30,   31,   51,
 /*   140 */   102,   48,   49,   86,   40,  189,   44,   72,   52,   34,
 /*   150 */    94,   35,  152,   59,   83,   64,  174,  234,   26,  207,
 /*   160 */    36,   30,   31,   51,  199,   48,   49,   56,   40,  146,
 /*   170 */    44,   72,  150,   34,  195,   35,  152,   59,   83,   64,
 /*   180 */    50,  221,   26,   84,   36,   30,   31,   51,   99,   48,
 /*   190 */    49,  203,   40,  169,   44,   72,  132,   34,  204,   35,
 /*   200 */   152,   59,   83,   64,  316,   61,   26,  230,   36,   30,
 /*   210 */    31,   51,  194,   48,   49,  197,   40,  113,   44,   72,
 /*   220 */   158,   34,   91,   35,  152,   59,   83,   64,  188,  209,
 /*   230 */    26,  202,   36,   30,   31,   51,  212,   48,   49,   90,
 /*   240 */    40,   67,   44,   72,  165,   34,  193,   35,  152,   59,
 /*   250 */    83,   64,   50,  178,   26,  228,   36,   30,   31,   51,
 /*   260 */   121,   48,   49,   73,   40,   74,   44,   72,  161,   34,
 /*   270 */    66,   35,  152,   59,   83,   64,  184,  131,   26,  187,
 /*   280 */    36,   30,   31,   51,  163,   48,   49,  116,   40,   53,
 /*   290 */    44,   72,  143,   34,   78,   35,  152,   59,   83,   64,
 /*   300 */   120,   81,   26,  163,   36,   30,   31,   51,  211,   48,
 /*   310 */    49,  118,   40,  110,   44,   72,  159,   34,   65,   35,
 /*   320 */   152,   59,   83,   64,   62,  112,   26,  115,   36,   30,
 /*   330 */    31,   51,   79,   48,   49,   63,   40,   54,   44,   72,
 /*   340 */   162,   34,  117,   35,  152,   59,   83,   64,   77,   70,
 /*   350 */    26,  163,   36,   30,   31,   51,  119,   48,   49,   68,
 /*   360 */    40,   57,   44,   72,  147,   34,  114,   35,  152,   59,
 /*   370 */    83,   64,  163,  163,   26,  163,   36,   30,   31,   51,
 /*   380 */   163,   48,   49,  163,   40,  163,   44,   72,  137,   34,
 /*   390 */   163,   35,  152,   59,   83,   64,  163,  163,   26,  163,
 /*   400 */    36,   30,   31,   51,  163,   48,   49,  163,   40,  163,
 /*   410 */    44,   72,  163,   34,  163,   35,  152,   59,   83,   64,
 /*   420 */   163,  163,   26,  163,   36,   30,   31,   51,  163,   48,
 /*   430 */    49,  163,   40,  163,   44,   72,  130,   34,  163,   35,
 /*   440 */   152,   59,   83,   64,  163,  163,   26,  163,   36,   30,
 /*   450 */    31,   51,  163,   48,   49,  163,   40,  163,   44,   72,
 /*   460 */   126,   34,  163,   35,  152,   59,   83,   64,  163,  163,
 /*   470 */    26,  201,   36,   30,   31,   51,  163,   48,   49,  163,
 /*   480 */    40,  123,   44,   72,  180,   34,  163,   35,  152,   59,
 /*   490 */    83,   64,   60,  163,   26,  163,   36,   30,   31,   51,
 /*   500 */   163,   48,   49,   28,   29,   22,   22,   22,   22,   22,
 /*   510 */    22,   22,   24,   24,   25,   25,   25,  163,  157,  163,
 /*   520 */   166,   41,   98,   25,   25,   25,   28,   29,   22,   22,
 /*   530 */    22,   22,   22,   22,   22,   24,   24,   25,   25,   25,
 /*   540 */    28,   29,   22,   22,   22,   22,   22,   22,   22,   24,
 /*   550 */    24,   25,   25,   25,   29,   22,   22,   22,   22,   22,
 /*   560 */    22,   22,   24,   24,   25,   25,   25,  214,  163,  148,
 /*   570 */   139,  179,   82,  179,  171,  163,  163,  163,   43,  123,
 /*   580 */    18,  111,  180,  163,  191,  179,  186,  153,  206,  163,
 /*   590 */   163,  163,  183,  183,  184,  131,  223,  222,   46,  177,
 /*   600 */    22,   22,   22,   22,   22,   22,   22,   24,   24,   25,
 /*   610 */    25,   25,   27,  175,    8,  163,  142,  220,  219,  215,
 /*   620 */   217,  218,  233,  192,  231,  163,  139,  210,  163,   93,
 /*   630 */   223,  222,   46,  163,  163,  139,  179,  111,  179,  163,
 /*   640 */   191,  163,  109,  168,  206,   80,  111,  163,  163,  191,
 /*   650 */   179,  170,  133,  206,  179,  163,  179,  173,  173,  184,
 /*   660 */   131,   85,   23,  163,  177,  179,  163,  179,  179,  170,
 /*   670 */   163,  157,   43,  166,   41,  173,  173,  184,  131,  179,
 /*   680 */   186,  163,  177,  179,  163,  179,  183,  183,  184,  131,
 /*   690 */   227,  163,  163,  177,  179,   69,  179,  179,  186,  179,
 /*   700 */    89,  179,  163,  163,  183,  183,  184,  131,  179,  186,
 /*   710 */   129,  177,  163,  179,  141,  183,  183,  184,  131,   88,
 /*   720 */    45,  111,  177,  179,  163,  179,  163,  177,  206,  163,
 /*   730 */    43,  157,  163,  166,   41,  163,  163,  179,  186,  139,
 /*   740 */   163,  163,   55,  163,  183,  183,  184,  131,  101,  163,
 /*   750 */   111,  177,  179,  191,  179,  105,  168,  206,  163,  163,
 /*   760 */   163,  163,  157,  163,  166,   41,  179,  186,  139,   47,
 /*   770 */   163,   14,  163,  183,  183,  184,  131,   97,  163,  111,
 /*   780 */   177,  179,  191,  179,  172,  168,  206,  223,  222,   46,
 /*   790 */    10,  163,  163,  163,  163,  179,  186,  163,  163,  163,
 /*   800 */   163,  139,  183,  183,  184,  131,  223,  222,   46,  177,
 /*   810 */   139,  157,  111,  166,   41,  191,  163,  122,  168,  206,
 /*   820 */   139,  111,  163,  163,  191,  163,  108,  168,  206,  163,
 /*   830 */   139,  111,  163,  163,  191,  163,  106,  168,  206,  139,
 /*   840 */   179,  111,  179,  163,  191,  163,  104,  168,  206,  139,
 /*   850 */   111,  198,  163,  191,  179,  107,  168,  206,  163,  139,
 /*   860 */   111,  123,   39,  191,  180,  163,   33,  206,  177,  128,
 /*   870 */   111,  163,    7,  191,  163,   12,   32,  206,  232,  163,
 /*   880 */   111,  163,  179,  179,  179,  179,    3,  206,  223,  222,
 /*   890 */    46,  223,  222,   46,  163,  163,  179,  179,    2,  163,
 /*   900 */   163,  163,  223,  222,   46,    4,  163,  163,  163,  163,
 /*   910 */   177,  177,   21,  163,  223,  222,   46,   17,  163,  163,
 /*   920 */   229,  223,  222,   46,   13,  225,  163,  163,  223,  222,
 /*   930 */    46,    6,  163,  223,  222,   46,   42,  103,  163,  163,
 /*   940 */   223,  222,   46,  157,    1,  166,   41,  223,  222,   46,
 /*   950 */   163,  157,  163,  166,   41,  163,  157,  100,  166,   41,
 /*   960 */   223,  222,   46,   19,  213,  163,   16,  163,  157,  163,
 /*   970 */   166,   41,  163,  163,  163,  157,  198,  166,   41,  223,
 /*   980 */   222,   46,  223,  222,   46,   11,  123,   37,  157,  180,
 /*   990 */   166,   41,    9,  163,  163,  157,  163,  166,   41,    5,
 /*  1000 */   163,  223,  222,   46,   15,  163,  163,  163,  223,  222,
 /*  1010 */    46,   20,  163,  198,  163,  223,  222,   46,  163,  163,
 /*  1020 */   223,  222,   46,  123,   38,  205,  180,  223,  222,   46,
 /*  1030 */   163,  200,  163,  138,  163,  123,  163,  163,  180,  163,
 /*  1040 */   144,  123,  163,  123,  180,  163,  180,  163,  163,  163,
 /*  1050 */   123,  163,  163,  180,
    );
    static public $yy_lookahead = array(
 /*     0 */    21,   54,   23,   24,   25,   26,   22,   28,   29,   30,
 /*    10 */    31,   32,   63,   74,   35,   36,   37,   38,   39,   40,
 /*    20 */    81,   42,   43,   74,   21,   41,   23,   24,   25,   26,
 /*    30 */    81,   28,   29,   30,   31,   32,   63,   34,   35,   22,
 /*    40 */    37,   38,   39,   40,   22,   42,   43,   74,   21,   60,
 /*    50 */    23,   24,   25,   26,   81,   28,   29,   30,   31,   32,
 /*    60 */    63,   22,   35,   36,   37,   38,   39,   40,   22,   42,
 /*    70 */    43,   74,   21,   60,   23,   24,   25,   26,   81,   28,
 /*    80 */    29,   30,   31,   32,   20,   22,   35,   36,   37,   38,
 /*    90 */    39,   40,   22,   42,   43,   22,   21,   60,   23,   24,
 /*   100 */    25,   26,   22,   28,   29,   30,   31,   32,   45,   45,
 /*   110 */    35,   36,   37,   38,   39,   40,   22,   42,   43,   22,
 /*   120 */    21,   45,   23,   24,   25,   26,   22,   28,   29,   30,
 /*   130 */    31,   32,   44,   45,   35,   36,   37,   38,   39,   40,
 /*   140 */    22,   42,   43,   22,   21,   54,   23,   24,   25,   26,
 /*   150 */    22,   28,   29,   30,   31,   32,   22,   22,   35,   22,
 /*   160 */    37,   38,   39,   40,   22,   42,   43,   60,   21,   49,
 /*   170 */    23,   24,   25,   26,   22,   28,   29,   30,   31,   32,
 /*   180 */    45,   22,   35,   22,   37,   38,   39,   40,   22,   42,
 /*   190 */    43,   22,   21,   61,   23,   24,   25,   26,   22,   28,
 /*   200 */    29,   30,   31,   32,   59,   60,   35,   22,   37,   38,
 /*   210 */    39,   40,   22,   42,   43,   22,   21,   74,   23,   24,
 /*   220 */    25,   26,   22,   28,   29,   30,   31,   32,   22,   22,
 /*   230 */    35,   22,   37,   38,   39,   40,   22,   42,   43,   22,
 /*   240 */    21,   60,   23,   24,   25,   26,   22,   28,   29,   30,
 /*   250 */    31,   32,   45,   51,   35,   22,   37,   38,   39,   40,
 /*   260 */    74,   42,   43,   60,   21,   60,   23,   24,   25,   26,
 /*   270 */    60,   28,   29,   30,   31,   32,   49,   50,   35,   57,
 /*   280 */    37,   38,   39,   40,   82,   42,   43,   74,   21,   60,
 /*   290 */    23,   24,   25,   26,   60,   28,   29,   30,   31,   32,
 /*   300 */    74,   60,   35,   82,   37,   38,   39,   40,   77,   42,
 /*   310 */    43,   74,   21,   74,   23,   24,   25,   26,   60,   28,
 /*   320 */    29,   30,   31,   32,   60,   74,   35,   74,   37,   38,
 /*   330 */    39,   40,   60,   42,   43,   60,   21,   60,   23,   24,
 /*   340 */    25,   26,   74,   28,   29,   30,   31,   32,   60,   60,
 /*   350 */    35,   82,   37,   38,   39,   40,   74,   42,   43,   60,
 /*   360 */    21,   60,   23,   24,   25,   26,   74,   28,   29,   30,
 /*   370 */    31,   32,   82,   82,   35,   82,   37,   38,   39,   40,
 /*   380 */    82,   42,   43,   82,   21,   82,   23,   24,   25,   26,
 /*   390 */    82,   28,   29,   30,   31,   32,   82,   82,   35,   82,
 /*   400 */    37,   38,   39,   40,   82,   42,   43,   82,   21,   82,
 /*   410 */    23,   24,   25,   26,   82,   28,   29,   30,   31,   32,
 /*   420 */    82,   82,   35,   82,   37,   38,   39,   40,   82,   42,
 /*   430 */    43,   82,   21,   82,   23,   24,   25,   26,   82,   28,
 /*   440 */    29,   30,   31,   32,   82,   82,   35,   82,   37,   38,
 /*   450 */    39,   40,   82,   42,   43,   82,   21,   82,   23,   24,
 /*   460 */    25,   26,   82,   28,   29,   30,   31,   32,   82,   82,
 /*   470 */    35,   64,   37,   38,   39,   40,   82,   42,   43,   82,
 /*   480 */    21,   74,   23,   24,   77,   26,   82,   28,   29,   30,
 /*   490 */    31,   32,   27,   82,   35,   82,   37,   38,   39,   40,
 /*   500 */    82,   42,   43,    3,    4,    5,    6,    7,    8,    9,
 /*   510 */    10,   11,   12,   13,   14,   15,   16,   82,   53,   82,
 /*   520 */    55,   56,   22,   14,   15,   16,    3,    4,    5,    6,
 /*   530 */     7,    8,    9,   10,   11,   12,   13,   14,   15,   16,
 /*   540 */     3,    4,    5,    6,    7,    8,    9,   10,   11,   12,
 /*   550 */    13,   14,   15,   16,    4,    5,    6,    7,    8,    9,
 /*   560 */    10,   11,   12,   13,   14,   15,   16,   22,   82,   64,
 /*   570 */    63,   26,   27,   28,   51,   82,   82,   82,   33,   74,
 /*   580 */     1,   74,   77,   82,   77,   40,   41,   80,   81,   82,
 /*   590 */    82,   82,   47,   48,   49,   50,   17,   18,   19,   54,
 /*   600 */     5,    6,    7,    8,    9,   10,   11,   12,   13,   14,
 /*   610 */    15,   16,    2,   62,    1,   82,   65,   66,   67,   68,
 /*   620 */    69,   70,   71,   72,   73,   82,   63,   76,   82,   78,
 /*   630 */    17,   18,   19,   82,   82,   63,   26,   74,   28,   82,
 /*   640 */    77,   82,   79,   80,   81,   27,   74,   82,   82,   77,
 /*   650 */    40,   41,   80,   81,   26,   82,   28,   47,   48,   49,
 /*   660 */    50,   22,   52,   82,   54,   26,   82,   28,   40,   41,
 /*   670 */    82,   53,   33,   55,   56,   47,   48,   49,   50,   40,
 /*   680 */    41,   82,   54,   26,   82,   28,   47,   48,   49,   50,
 /*   690 */    22,   82,   82,   54,   26,   27,   28,   40,   41,   26,
 /*   700 */    22,   28,   82,   82,   47,   48,   49,   50,   40,   41,
 /*   710 */    63,   54,   82,   40,   41,   47,   48,   49,   50,   22,
 /*   720 */    11,   74,   54,   26,   82,   28,   82,   54,   81,   82,
 /*   730 */    33,   53,   82,   55,   56,   82,   82,   40,   41,   63,
 /*   740 */    82,   82,   33,   82,   47,   48,   49,   50,   22,   82,
 /*   750 */    74,   54,   26,   77,   28,   79,   80,   81,   82,   82,
 /*   760 */    82,   82,   53,   82,   55,   56,   40,   41,   63,   11,
 /*   770 */    82,    1,   82,   47,   48,   49,   50,   22,   82,   74,
 /*   780 */    54,   26,   77,   28,   79,   80,   81,   17,   18,   19,
 /*   790 */     1,   82,   82,   82,   82,   40,   41,   82,   82,   82,
 /*   800 */    82,   63,   47,   48,   49,   50,   17,   18,   19,   54,
 /*   810 */    63,   53,   74,   55,   56,   77,   82,   79,   80,   81,
 /*   820 */    63,   74,   82,   82,   77,   82,   79,   80,   81,   82,
 /*   830 */    63,   74,   82,   82,   77,   82,   79,   80,   81,   63,
 /*   840 */    26,   74,   28,   82,   77,   82,   79,   80,   81,   63,
 /*   850 */    74,   64,   82,   77,   40,   79,   80,   81,   82,   63,
 /*   860 */    74,   74,   75,   77,   77,   82,   80,   81,   54,   63,
 /*   870 */    74,   82,    1,   77,   82,    1,   80,   81,   22,   82,
 /*   880 */    74,   82,   26,   26,   28,   28,    1,   81,   17,   18,
 /*   890 */    19,   17,   18,   19,   82,   82,   40,   40,    1,   82,
 /*   900 */    82,   82,   17,   18,   19,    1,   82,   82,   82,   82,
 /*   910 */    54,   54,    1,   82,   17,   18,   19,    1,   82,   82,
 /*   920 */    22,   17,   18,   19,    1,   22,   82,   82,   17,   18,
 /*   930 */    19,    1,   82,   17,   18,   19,   46,   22,   82,   82,
 /*   940 */    17,   18,   19,   53,    1,   55,   56,   17,   18,   19,
 /*   950 */    82,   53,   82,   55,   56,   82,   53,   22,   55,   56,
 /*   960 */    17,   18,   19,    1,   22,   82,    1,   82,   53,   82,
 /*   970 */    55,   56,   82,   82,   82,   53,   64,   55,   56,   17,
 /*   980 */    18,   19,   17,   18,   19,    1,   74,   75,   53,   77,
 /*   990 */    55,   56,    1,   82,   82,   53,   82,   55,   56,    1,
 /*  1000 */    82,   17,   18,   19,    1,   82,   82,   82,   17,   18,
 /*  1010 */    19,    1,   82,   64,   82,   17,   18,   19,   82,   82,
 /*  1020 */    17,   18,   19,   74,   75,   64,   77,   17,   18,   19,
 /*  1030 */    82,   64,   82,   64,   82,   74,   82,   82,   77,   82,
 /*  1040 */    64,   74,   82,   74,   77,   82,   77,   82,   82,   82,
 /*  1050 */    74,   82,   82,   77,
);
    const YY_SHIFT_USE_DFLT = -54;
    const YY_SHIFT_MAX = 167;
    static public $yy_shift_ofst = array(
 /*     0 */   -54,   99,   51,  -21,   75,    3,   27,  435,  411,  339,
 /*    10 */   219,  171,  123,  267,  315,  387,  243,  291,  363,  147,
 /*    20 */   195,  459,  610,  610,  610,  610,  610,  610,  610,  610,
 /*    30 */   628,  628,  628,  628,  668,  726,  755,  545,  697,  639,
 /*    40 */   657,  657,  657,  657,  657,  814,  814,  814,  814,  814,
 /*    50 */   814,  673,  856,  991,  984,  814, 1003,  998, 1010,  814,
 /*    60 */   814,  911,  904,  897,  857,  916,  885,  930,  923,  857,
 /*    70 */   613,  579,  857,  871,  874,  857,  965,  943,  789,  770,
 /*    80 */   814,  962,  857,  227,  -54,  -54,  -54,  -54,  -54,  -54,
 /*    90 */   -54,  -54,  -54,  -54,  -54,  -54,  -54,  -54,  -54,  -54,
 /*   100 */   -54,  -54,  -54,  -54,  523,  500,  537,  550,  595,  595,
 /*   110 */   709,  890,  758,  898,  465,  678,  618,  903,  915,  935,
 /*   120 */   942,  922,  509,  922,   88,  207,  -16,  135,   63,   64,
 /*   130 */   185,  120,  169,  217,  214,  233,  128,   39,   22,   76,
 /*   140 */   134,  104,  159,   97,   94,  206,  202,  176,  222,  161,
 /*   150 */    46,  121,  118,   17,   73,  200,  166,   91,  152,  190,
 /*   160 */   193,  224,  142,  209,   70,   80,  -53,  137,
);
    const YY_REDUCE_USE_DFLT = -62;
    const YY_REDUCE_MAX = 103;
    static public $yy_reduce_ofst = array(
 /*     0 */   145,  551,  551,  551,  551,  551,  551,  551,  551,  551,
 /*    10 */   551,  551,  551,  551,  551,  551,  551,  551,  551,  551,
 /*    20 */   551,  551,  747,  767,  738,  705,  676,  757,  776,  563,
 /*    30 */   796,  786,  507,  572,  912,  787,  949,  967,  967,  967,
 /*    40 */   969,  505,  961,  407,  976,   -3,  647,  -51,  806,  -27,
 /*    50 */   -61,  253,  268,  132,  132,  251,  132,  132,  132,  292,
 /*    60 */   282,  132,  132,  132,  239,  132,  132,  132,  132,  143,
 /*    70 */   132,  132,  237,  132,  132,  213,  132,  132,  132,  132,
 /*    80 */   186,  132,  226,  231,  241,  234,  229,  203,  181,  205,
 /*    90 */   210,  258,  272,  301,  289,  299,  288,  264,  275,  277,
 /*   100 */   107,   37,   13,  -11,
);
    static public $yyExpectedTokens = array(
        /* 0 */ array(),
        /* 1 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 36, 37, 38, 39, 40, 42, 43, ),
        /* 2 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 36, 37, 38, 39, 40, 42, 43, ),
        /* 3 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 36, 37, 38, 39, 40, 42, 43, ),
        /* 4 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 36, 37, 38, 39, 40, 42, 43, ),
        /* 5 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 34, 35, 37, 38, 39, 40, 42, 43, ),
        /* 6 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 36, 37, 38, 39, 40, 42, 43, ),
        /* 7 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 8 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 9 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 10 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 11 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 12 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 13 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 14 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 15 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 16 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 17 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 18 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 19 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 20 */ array(21, 23, 24, 25, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 21 */ array(21, 23, 24, 26, 28, 29, 30, 31, 32, 35, 37, 38, 39, 40, 42, 43, ),
        /* 22 */ array(2, 26, 28, 40, 41, 47, 48, 49, 50, 52, 54, ),
        /* 23 */ array(2, 26, 28, 40, 41, 47, 48, 49, 50, 52, 54, ),
        /* 24 */ array(2, 26, 28, 40, 41, 47, 48, 49, 50, 52, 54, ),
        /* 25 */ array(2, 26, 28, 40, 41, 47, 48, 49, 50, 52, 54, ),
        /* 26 */ array(2, 26, 28, 40, 41, 47, 48, 49, 50, 52, 54, ),
        /* 27 */ array(2, 26, 28, 40, 41, 47, 48, 49, 50, 52, 54, ),
        /* 28 */ array(2, 26, 28, 40, 41, 47, 48, 49, 50, 52, 54, ),
        /* 29 */ array(2, 26, 28, 40, 41, 47, 48, 49, 50, 52, 54, ),
        /* 30 */ array(26, 28, 40, 41, 47, 48, 49, 50, 54, ),
        /* 31 */ array(26, 28, 40, 41, 47, 48, 49, 50, 54, ),
        /* 32 */ array(26, 28, 40, 41, 47, 48, 49, 50, 54, ),
        /* 33 */ array(26, 28, 40, 41, 47, 48, 49, 50, 54, ),
        /* 34 */ array(22, 26, 27, 28, 40, 41, 47, 48, 49, 50, 54, ),
        /* 35 */ array(22, 26, 28, 40, 41, 47, 48, 49, 50, 54, ),
        /* 36 */ array(22, 26, 28, 40, 41, 47, 48, 49, 50, 54, ),
        /* 37 */ array(22, 26, 27, 28, 33, 40, 41, 47, 48, 49, 50, 54, ),
        /* 38 */ array(22, 26, 28, 33, 40, 41, 47, 48, 49, 50, 54, ),
        /* 39 */ array(22, 26, 28, 33, 40, 41, 47, 48, 49, 50, 54, ),
        /* 40 */ array(26, 28, 40, 41, 47, 48, 49, 50, 54, ),
        /* 41 */ array(26, 28, 40, 41, 47, 48, 49, 50, 54, ),
        /* 42 */ array(26, 28, 40, 41, 47, 48, 49, 50, 54, ),
        /* 43 */ array(26, 28, 40, 41, 47, 48, 49, 50, 54, ),
        /* 44 */ array(26, 28, 40, 41, 47, 48, 49, 50, 54, ),
        /* 45 */ array(26, 28, 40, 54, ),
        /* 46 */ array(26, 28, 40, 54, ),
        /* 47 */ array(26, 28, 40, 54, ),
        /* 48 */ array(26, 28, 40, 54, ),
        /* 49 */ array(26, 28, 40, 54, ),
        /* 50 */ array(26, 28, 40, 54, ),
        /* 51 */ array(26, 28, 40, 41, 54, ),
        /* 52 */ array(22, 26, 28, 40, 54, ),
        /* 53 */ array(1, 17, 18, 19, ),
        /* 54 */ array(1, 17, 18, 19, ),
        /* 55 */ array(26, 28, 40, 54, ),
        /* 56 */ array(1, 17, 18, 19, ),
        /* 57 */ array(1, 17, 18, 19, ),
        /* 58 */ array(1, 17, 18, 19, ),
        /* 59 */ array(26, 28, 40, 54, ),
        /* 60 */ array(26, 28, 40, 54, ),
        /* 61 */ array(1, 17, 18, 19, ),
        /* 62 */ array(1, 17, 18, 19, ),
        /* 63 */ array(1, 17, 18, 19, ),
        /* 64 */ array(26, 28, 40, 54, ),
        /* 65 */ array(1, 17, 18, 19, ),
        /* 66 */ array(1, 17, 18, 19, ),
        /* 67 */ array(1, 17, 18, 19, ),
        /* 68 */ array(1, 17, 18, 19, ),
        /* 69 */ array(26, 28, 40, 54, ),
        /* 70 */ array(1, 17, 18, 19, ),
        /* 71 */ array(1, 17, 18, 19, ),
        /* 72 */ array(26, 28, 40, 54, ),
        /* 73 */ array(1, 17, 18, 19, ),
        /* 74 */ array(1, 17, 18, 19, ),
        /* 75 */ array(26, 28, 40, 54, ),
        /* 76 */ array(1, 17, 18, 19, ),
        /* 77 */ array(1, 17, 18, 19, ),
        /* 78 */ array(1, 17, 18, 19, ),
        /* 79 */ array(1, 17, 18, 19, ),
        /* 80 */ array(26, 28, 40, 54, ),
        /* 81 */ array(1, 17, 18, 19, ),
        /* 82 */ array(26, 28, 40, 54, ),
        /* 83 */ array(49, 50, ),
        /* 84 */ array(),
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
        /* 104 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 51, ),
        /* 105 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 22, ),
        /* 106 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 107 */ array(4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 108 */ array(5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 109 */ array(5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 110 */ array(11, 33, 53, 55, 56, ),
        /* 111 */ array(46, 53, 55, 56, ),
        /* 112 */ array(11, 53, 55, 56, ),
        /* 113 */ array(22, 53, 55, 56, ),
        /* 114 */ array(27, 53, 55, 56, ),
        /* 115 */ array(22, 53, 55, 56, ),
        /* 116 */ array(27, 53, 55, 56, ),
        /* 117 */ array(22, 53, 55, 56, ),
        /* 118 */ array(22, 53, 55, 56, ),
        /* 119 */ array(22, 53, 55, 56, ),
        /* 120 */ array(22, 53, 55, 56, ),
        /* 121 */ array(53, 55, 56, ),
        /* 122 */ array(14, 15, 16, ),
        /* 123 */ array(53, 55, 56, ),
        /* 124 */ array(44, 45, ),
        /* 125 */ array(22, 45, ),
        /* 126 */ array(22, 41, ),
        /* 127 */ array(22, 45, ),
        /* 128 */ array(22, 45, ),
        /* 129 */ array(20, 45, ),
        /* 130 */ array(22, ),
        /* 131 */ array(49, ),
        /* 132 */ array(22, ),
        /* 133 */ array(22, ),
        /* 134 */ array(22, ),
        /* 135 */ array(22, ),
        /* 136 */ array(22, ),
        /* 137 */ array(22, ),
        /* 138 */ array(22, ),
        /* 139 */ array(45, ),
        /* 140 */ array(22, ),
        /* 141 */ array(22, ),
        /* 142 */ array(22, ),
        /* 143 */ array(22, ),
        /* 144 */ array(22, ),
        /* 145 */ array(22, ),
        /* 146 */ array(51, ),
        /* 147 */ array(22, ),
        /* 148 */ array(57, ),
        /* 149 */ array(22, ),
        /* 150 */ array(22, ),
        /* 151 */ array(22, ),
        /* 152 */ array(22, ),
        /* 153 */ array(22, ),
        /* 154 */ array(22, ),
        /* 155 */ array(22, ),
        /* 156 */ array(22, ),
        /* 157 */ array(54, ),
        /* 158 */ array(22, ),
        /* 159 */ array(22, ),
        /* 160 */ array(22, ),
        /* 161 */ array(22, ),
        /* 162 */ array(22, ),
        /* 163 */ array(22, ),
        /* 164 */ array(22, ),
        /* 165 */ array(22, ),
        /* 166 */ array(54, ),
        /* 167 */ array(22, ),
        /* 168 */ array(),
        /* 169 */ array(),
        /* 170 */ array(),
        /* 171 */ array(),
        /* 172 */ array(),
        /* 173 */ array(),
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
);
    static public $yy_default = array(
 /*     0 */   238,  315,  315,  315,  315,  315,  315,  315,  315,  315,
 /*    10 */   315,  315,  315,  315,  315,  315,  315,  315,  315,  315,
 /*    20 */   315,  315,  315,  315,  315,  315,  315,  315,  315,  315,
 /*    30 */   315,  315,  315,  315,  315,  315,  315,  315,  315,  315,
 /*    40 */   315,  315,  315,  315,  315,  315,  315,  315,  315,  315,
 /*    50 */   315,  315,  315,  315,  315,  315,  315,  315,  315,  315,
 /*    60 */   315,  236,  315,  315,  315,  315,  315,  315,  315,  315,
 /*    70 */   315,  315,  315,  315,  315,  315,  315,  315,  315,  315,
 /*    80 */   315,  315,  315,  315,  238,  238,  238,  238,  238,  238,
 /*    90 */   238,  238,  238,  238,  238,  238,  238,  238,  238,  238,
 /*   100 */   238,  238,  238,  238,  315,  315,  302,  303,  306,  304,
 /*   110 */   315,  288,  315,  315,  315,  315,  315,  315,  315,  315,
 /*   120 */   315,  284,  305,  292,  315,  315,  315,  315,  315,  315,
 /*   130 */   315,  315,  315,  315,  315,  315,  315,  315,  315,  296,
 /*   140 */   315,  315,  315,  315,  315,  315,  315,  315,  315,  315,
 /*   150 */   315,  315,  315,  315,  315,  315,  315,  315,  315,  315,
 /*   160 */   315,  315,  315,  315,  315,  315,  315,  315,  309,  237,
 /*   170 */   297,  308,  307,  298,  282,  239,  281,  313,  301,  314,
 /*   180 */   295,  311,  274,  294,  300,  260,  293,  312,  272,  310,
 /*   190 */   243,  299,  252,  259,  273,  261,  271,  275,  291,  276,
 /*   200 */   289,  290,  262,  270,  268,  287,  286,  267,  285,  265,
 /*   210 */   263,  264,  269,  258,  257,  247,  283,  248,  249,  246,
 /*   220 */   245,  244,  241,  240,  250,  280,  254,  255,  277,  256,
 /*   230 */   278,  253,  279,  251,  266,  242,
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
    const YYNOCODE = 83;
    const YYSTACKDEPTH = 100;
    const YYNSTATE = 236;
    const YYNRULE = 79;
    const YYERRORSYMBOL = 58;
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
  'T_MOD',         'T_HTML',        'T_COMMENT',     'T_PRINT_OPEN',
  'T_PRINT_CLOSE',  'T_EXTENDS',     'T_CLOSE_TAG',   'T_INCLUDE',   
  'T_AUTOESCAPE',  'T_CUSTOM_END',  'T_CUSTOM_TAG',  'T_AS',        
  'T_CUSTOM_BLOCK',  'T_SPACEFULL',   'T_WITH',        'T_LOAD',      
  'T_FOR',         'T_COMMA',       'T_EMPTY',       'T_IF',        
  'T_ELSE',        'T_IFCHANGED',   'T_IFEQUAL',     'T_IFNOTEQUAL',
  'T_BLOCK',       'T_NUMERIC',     'T_FILTER',      'T_REGROUP',   
  'T_BY',          'T_PIPE',        'T_COLON',       'T_TRUE',      
  'T_FALSE',       'T_STRING',      'T_INTL',        'T_RPARENT',   
  'T_LPARENT',     'T_OBJ',         'T_ALPHA',       'T_DOT',       
  'T_BRACKETS_OPEN',  'T_BRACKETS_CLOSE',  'error',         'start',       
  'body',          'code',          'stmts',         'filtered_var',
  'var_or_string',  'stmt',          'for_stmt',      'ifchanged_stmt',
  'block_stmt',    'filter_stmt',   'if_stmt',       'custom_tag',  
  'alias',         'ifequal',       'varname',       'params',      
  'regroup',       'string',        'for_def',       'expr',        
  'fvar_or_string',  'varname_args',
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
 /*   5 */ "code ::= T_COMMENT",
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
 /*  18 */ "stmts ::= T_AUTOESCAPE varname T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  19 */ "custom_tag ::= T_CUSTOM_TAG T_CLOSE_TAG",
 /*  20 */ "custom_tag ::= T_CUSTOM_TAG T_AS varname T_CLOSE_TAG",
 /*  21 */ "custom_tag ::= T_CUSTOM_TAG params T_CLOSE_TAG",
 /*  22 */ "custom_tag ::= T_CUSTOM_TAG params T_AS varname T_CLOSE_TAG",
 /*  23 */ "custom_tag ::= T_CUSTOM_BLOCK T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  24 */ "custom_tag ::= T_CUSTOM_BLOCK params T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  25 */ "custom_tag ::= T_SPACEFULL T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  26 */ "alias ::= T_WITH varname T_AS varname T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  27 */ "stmt ::= regroup",
 /*  28 */ "stmt ::= T_LOAD string",
 /*  29 */ "for_def ::= T_FOR varname T_IN filtered_var T_CLOSE_TAG",
 /*  30 */ "for_def ::= T_FOR varname T_COMMA varname T_IN filtered_var T_CLOSE_TAG",
 /*  31 */ "for_stmt ::= for_def body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  32 */ "for_stmt ::= for_def body T_OPEN_TAG T_EMPTY T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  33 */ "if_stmt ::= T_IF expr T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  34 */ "if_stmt ::= T_IF expr T_CLOSE_TAG body T_OPEN_TAG T_ELSE T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  35 */ "ifchanged_stmt ::= T_IFCHANGED T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  36 */ "ifchanged_stmt ::= T_IFCHANGED params T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  37 */ "ifchanged_stmt ::= T_IFCHANGED T_CLOSE_TAG body T_OPEN_TAG T_ELSE T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  38 */ "ifchanged_stmt ::= T_IFCHANGED params T_CLOSE_TAG body T_OPEN_TAG T_ELSE T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  39 */ "ifequal ::= T_IFEQUAL fvar_or_string fvar_or_string T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  40 */ "ifequal ::= T_IFEQUAL fvar_or_string fvar_or_string T_CLOSE_TAG body T_OPEN_TAG T_ELSE T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  41 */ "ifequal ::= T_IFNOTEQUAL fvar_or_string fvar_or_string T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  42 */ "ifequal ::= T_IFNOTEQUAL fvar_or_string fvar_or_string T_CLOSE_TAG body T_OPEN_TAG T_ELSE T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  43 */ "block_stmt ::= T_BLOCK varname T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  44 */ "block_stmt ::= T_BLOCK varname T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END varname T_CLOSE_TAG",
 /*  45 */ "block_stmt ::= T_BLOCK T_NUMERIC T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  46 */ "block_stmt ::= T_BLOCK T_NUMERIC T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_NUMERIC T_CLOSE_TAG",
 /*  47 */ "filter_stmt ::= T_FILTER filtered_var T_CLOSE_TAG body T_OPEN_TAG T_CUSTOM_END T_CLOSE_TAG",
 /*  48 */ "regroup ::= T_REGROUP filtered_var T_BY varname T_AS varname",
 /*  49 */ "filtered_var ::= filtered_var T_PIPE varname_args",
 /*  50 */ "filtered_var ::= varname_args",
 /*  51 */ "varname_args ::= varname T_COLON var_or_string",
 /*  52 */ "varname_args ::= varname",
 /*  53 */ "params ::= params var_or_string",
 /*  54 */ "params ::= params T_COMMA var_or_string",
 /*  55 */ "params ::= var_or_string",
 /*  56 */ "var_or_string ::= varname",
 /*  57 */ "var_or_string ::= T_NUMERIC",
 /*  58 */ "var_or_string ::= T_TRUE|T_FALSE",
 /*  59 */ "var_or_string ::= string",
 /*  60 */ "fvar_or_string ::= filtered_var",
 /*  61 */ "fvar_or_string ::= T_NUMERIC",
 /*  62 */ "fvar_or_string ::= T_TRUE|T_FALSE",
 /*  63 */ "fvar_or_string ::= string",
 /*  64 */ "string ::= T_STRING",
 /*  65 */ "string ::= T_INTL T_STRING T_RPARENT",
 /*  66 */ "expr ::= T_NOT expr",
 /*  67 */ "expr ::= expr T_AND expr",
 /*  68 */ "expr ::= expr T_OR expr",
 /*  69 */ "expr ::= expr T_PLUS|T_MINUS expr",
 /*  70 */ "expr ::= expr T_EQ|T_NE|T_GT|T_GE|T_LT|T_LE|T_IN expr",
 /*  71 */ "expr ::= expr T_TIMES|T_DIV|T_MOD expr",
 /*  72 */ "expr ::= T_LPARENT expr T_RPARENT",
 /*  73 */ "expr ::= fvar_or_string",
 /*  74 */ "varname ::= varname T_OBJ T_ALPHA",
 /*  75 */ "varname ::= varname T_DOT T_ALPHA",
 /*  76 */ "varname ::= varname T_BRACKETS_OPEN var_or_string T_BRACKETS_CLOSE",
 /*  77 */ "varname ::= T_ALPHA",
 /*  78 */ "varname ::= T_BLOCK|T_CUSTOM_TAG|T_CUSTOM_BLOCK",
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
  array( 'lhs' => 59, 'rhs' => 1 ),
  array( 'lhs' => 60, 'rhs' => 2 ),
  array( 'lhs' => 60, 'rhs' => 0 ),
  array( 'lhs' => 61, 'rhs' => 2 ),
  array( 'lhs' => 61, 'rhs' => 1 ),
  array( 'lhs' => 61, 'rhs' => 1 ),
  array( 'lhs' => 61, 'rhs' => 3 ),
  array( 'lhs' => 62, 'rhs' => 3 ),
  array( 'lhs' => 62, 'rhs' => 2 ),
  array( 'lhs' => 62, 'rhs' => 1 ),
  array( 'lhs' => 62, 'rhs' => 1 ),
  array( 'lhs' => 62, 'rhs' => 1 ),
  array( 'lhs' => 62, 'rhs' => 1 ),
  array( 'lhs' => 62, 'rhs' => 1 ),
  array( 'lhs' => 62, 'rhs' => 3 ),
  array( 'lhs' => 62, 'rhs' => 1 ),
  array( 'lhs' => 62, 'rhs' => 1 ),
  array( 'lhs' => 62, 'rhs' => 1 ),
  array( 'lhs' => 62, 'rhs' => 7 ),
  array( 'lhs' => 71, 'rhs' => 2 ),
  array( 'lhs' => 71, 'rhs' => 4 ),
  array( 'lhs' => 71, 'rhs' => 3 ),
  array( 'lhs' => 71, 'rhs' => 5 ),
  array( 'lhs' => 71, 'rhs' => 6 ),
  array( 'lhs' => 71, 'rhs' => 7 ),
  array( 'lhs' => 71, 'rhs' => 6 ),
  array( 'lhs' => 72, 'rhs' => 9 ),
  array( 'lhs' => 65, 'rhs' => 1 ),
  array( 'lhs' => 65, 'rhs' => 2 ),
  array( 'lhs' => 78, 'rhs' => 5 ),
  array( 'lhs' => 78, 'rhs' => 7 ),
  array( 'lhs' => 66, 'rhs' => 5 ),
  array( 'lhs' => 66, 'rhs' => 9 ),
  array( 'lhs' => 70, 'rhs' => 7 ),
  array( 'lhs' => 70, 'rhs' => 11 ),
  array( 'lhs' => 67, 'rhs' => 6 ),
  array( 'lhs' => 67, 'rhs' => 7 ),
  array( 'lhs' => 67, 'rhs' => 10 ),
  array( 'lhs' => 67, 'rhs' => 11 ),
  array( 'lhs' => 73, 'rhs' => 8 ),
  array( 'lhs' => 73, 'rhs' => 12 ),
  array( 'lhs' => 73, 'rhs' => 8 ),
  array( 'lhs' => 73, 'rhs' => 12 ),
  array( 'lhs' => 68, 'rhs' => 7 ),
  array( 'lhs' => 68, 'rhs' => 8 ),
  array( 'lhs' => 68, 'rhs' => 7 ),
  array( 'lhs' => 68, 'rhs' => 8 ),
  array( 'lhs' => 69, 'rhs' => 7 ),
  array( 'lhs' => 76, 'rhs' => 6 ),
  array( 'lhs' => 63, 'rhs' => 3 ),
  array( 'lhs' => 63, 'rhs' => 1 ),
  array( 'lhs' => 81, 'rhs' => 3 ),
  array( 'lhs' => 81, 'rhs' => 1 ),
  array( 'lhs' => 75, 'rhs' => 2 ),
  array( 'lhs' => 75, 'rhs' => 3 ),
  array( 'lhs' => 75, 'rhs' => 1 ),
  array( 'lhs' => 64, 'rhs' => 1 ),
  array( 'lhs' => 64, 'rhs' => 1 ),
  array( 'lhs' => 64, 'rhs' => 1 ),
  array( 'lhs' => 64, 'rhs' => 1 ),
  array( 'lhs' => 80, 'rhs' => 1 ),
  array( 'lhs' => 80, 'rhs' => 1 ),
  array( 'lhs' => 80, 'rhs' => 1 ),
  array( 'lhs' => 80, 'rhs' => 1 ),
  array( 'lhs' => 77, 'rhs' => 1 ),
  array( 'lhs' => 77, 'rhs' => 3 ),
  array( 'lhs' => 79, 'rhs' => 2 ),
  array( 'lhs' => 79, 'rhs' => 3 ),
  array( 'lhs' => 79, 'rhs' => 3 ),
  array( 'lhs' => 79, 'rhs' => 3 ),
  array( 'lhs' => 79, 'rhs' => 3 ),
  array( 'lhs' => 79, 'rhs' => 3 ),
  array( 'lhs' => 79, 'rhs' => 3 ),
  array( 'lhs' => 79, 'rhs' => 1 ),
  array( 'lhs' => 74, 'rhs' => 3 ),
  array( 'lhs' => 74, 'rhs' => 3 ),
  array( 'lhs' => 74, 'rhs' => 4 ),
  array( 'lhs' => 74, 'rhs' => 1 ),
  array( 'lhs' => 74, 'rhs' => 1 ),
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
        65 => 8,
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
        64 => 9,
        73 => 9,
        77 => 9,
        78 => 9,
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
        44 => 44,
        46 => 44,
        45 => 45,
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
        61 => 57,
        58 => 58,
        62 => 58,
        59 => 59,
        63 => 59,
        60 => 60,
        66 => 66,
        67 => 67,
        68 => 67,
        69 => 67,
        71 => 67,
        70 => 70,
        72 => 72,
        74 => 74,
        75 => 75,
        76 => 76,
    );
    /* Beginning here are the reduction cases.  A typical example
    ** follows:
    **  #line <lineno> <grammarfile>
    **   function yy_r0($yymsp){ ... }           // User supplied code
    **  #line <lineno> <thisfile>
    */
#line 79 "lib/Haanga/Compiler/Parser.y"
    function yy_r0(){ $this->body = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1551 "lib/Haanga/Compiler/Parser.php"
#line 81 "lib/Haanga/Compiler/Parser.y"
    function yy_r1(){ $this->_retvalue=$this->yystack[$this->yyidx + -1]->minor; $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1554 "lib/Haanga/Compiler/Parser.php"
#line 82 "lib/Haanga/Compiler/Parser.y"
    function yy_r2(){ $this->_retvalue = array();     }
#line 1557 "lib/Haanga/Compiler/Parser.php"
#line 85 "lib/Haanga/Compiler/Parser.y"
    function yy_r3(){ if (count($this->yystack[$this->yyidx + 0]->minor)) $this->yystack[$this->yyidx + 0]->minor['line'] = $this->lex->getLine();  $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1560 "lib/Haanga/Compiler/Parser.php"
#line 86 "lib/Haanga/Compiler/Parser.y"
    function yy_r4(){
    $this->_retvalue = array('operation' => 'html', 'html' => $this->yystack[$this->yyidx + 0]->minor, 'line' => $this->lex->getLine() ); 
    }
#line 1565 "lib/Haanga/Compiler/Parser.php"
#line 89 "lib/Haanga/Compiler/Parser.y"
    function yy_r5(){
    $this->yystack[$this->yyidx + 0]->minor=rtrim($this->yystack[$this->yyidx + 0]->minor); $this->_retvalue = array('operation' => 'comment', 'comment' => $this->yystack[$this->yyidx + 0]->minor); 
    }
#line 1570 "lib/Haanga/Compiler/Parser.php"
#line 92 "lib/Haanga/Compiler/Parser.y"
    function yy_r6(){
    $this->_retvalue = array('operation' => 'print_var', 'variable' => $this->yystack[$this->yyidx + -1]->minor, 'line' => $this->lex->getLine() ); 
    }
#line 1575 "lib/Haanga/Compiler/Parser.php"
#line 96 "lib/Haanga/Compiler/Parser.y"
    function yy_r7(){ $this->_retvalue = array('operation' => 'base', $this->yystack[$this->yyidx + -1]->minor);     }
#line 1578 "lib/Haanga/Compiler/Parser.php"
#line 97 "lib/Haanga/Compiler/Parser.y"
    function yy_r8(){ $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;     }
#line 1581 "lib/Haanga/Compiler/Parser.php"
#line 98 "lib/Haanga/Compiler/Parser.y"
    function yy_r9(){ $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1584 "lib/Haanga/Compiler/Parser.php"
#line 103 "lib/Haanga/Compiler/Parser.y"
    function yy_r14(){ $this->_retvalue = array('operation' => 'include', $this->yystack[$this->yyidx + -1]->minor);     }
#line 1587 "lib/Haanga/Compiler/Parser.php"
#line 107 "lib/Haanga/Compiler/Parser.y"
    function yy_r18(){ 
    $this->yystack[$this->yyidx + -5]->minor = strtolower($this->yystack[$this->yyidx + -5]->minor);
    if ($this->yystack[$this->yyidx + -5]->minor != 'on' && $this->yystack[$this->yyidx + -5]->minor != 'off') {
        $this->Error("Invalid autoescape param (".$this->yystack[$this->yyidx + -5]->minor."), it must be on or off");
    }
    if ($this->yystack[$this->yyidx + -1]->minor != "endautoescape") {
        $this->Error("Invalid close tag ".$this->yystack[$this->yyidx + -1]->minor.", it must be endautoescape");
    }
    $this->_retvalue = array('operation' => 'autoescape', 'value' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1599 "lib/Haanga/Compiler/Parser.php"
#line 121 "lib/Haanga/Compiler/Parser.y"
    function yy_r19(){
    $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -1]->minor, 'list'=>array()); 
    }
#line 1604 "lib/Haanga/Compiler/Parser.php"
#line 124 "lib/Haanga/Compiler/Parser.y"
    function yy_r20(){
    $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -3]->minor, 'as' => $this->yystack[$this->yyidx + -1]->minor, 'list'=>array()); 
    }
#line 1609 "lib/Haanga/Compiler/Parser.php"
#line 127 "lib/Haanga/Compiler/Parser.y"
    function yy_r21(){ 
    $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -2]->minor, 'list' => $this->yystack[$this->yyidx + -1]->minor); 
    }
#line 1614 "lib/Haanga/Compiler/Parser.php"
#line 130 "lib/Haanga/Compiler/Parser.y"
    function yy_r22(){
    $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -4]->minor, 'as' => $this->yystack[$this->yyidx + -1]->minor, 'list' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1619 "lib/Haanga/Compiler/Parser.php"
#line 135 "lib/Haanga/Compiler/Parser.y"
    function yy_r23(){
    if ('end'.$this->yystack[$this->yyidx + -5]->minor != $this->yystack[$this->yyidx + -1]->minor) { 
        $this->error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor); 
    } 
    $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor, 'list' => array());
    }
#line 1627 "lib/Haanga/Compiler/Parser.php"
#line 141 "lib/Haanga/Compiler/Parser.y"
    function yy_r24(){
    if ('end'.$this->yystack[$this->yyidx + -6]->minor != $this->yystack[$this->yyidx + -1]->minor) { 
        $this->error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor); 
    } 
    $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -6]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor, 'list' => $this->yystack[$this->yyidx + -5]->minor);
    }
#line 1635 "lib/Haanga/Compiler/Parser.php"
#line 149 "lib/Haanga/Compiler/Parser.y"
    function yy_r25(){
    if ('endspacefull' != $this->yystack[$this->yyidx + -1]->minor) {
        $this->error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor);
    } 
    $this->_retvalue = array('operation' => 'spacefull', 'body' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1643 "lib/Haanga/Compiler/Parser.php"
#line 157 "lib/Haanga/Compiler/Parser.y"
    function yy_r26(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endwith") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endwith");
    }
    $this->_retvalue = array('operation' => 'alias', 'var' => $this->yystack[$this->yyidx + -7]->minor, 'as' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1651 "lib/Haanga/Compiler/Parser.php"
#line 166 "lib/Haanga/Compiler/Parser.y"
    function yy_r28(){
    if (!is_file($this->yystack[$this->yyidx + 0]->minor) || !Haanga_Compiler::getOption('enable_load')) {
        $this->error($this->yystack[$this->yyidx + 0]->minor." is not a valid file"); 
    } 
    require_once $this->yystack[$this->yyidx + 0]->minor;
    }
#line 1659 "lib/Haanga/Compiler/Parser.php"
#line 174 "lib/Haanga/Compiler/Parser.y"
    function yy_r29(){
    /* Try to get the variable */
    $var = $this->compiler->get_context(is_array($this->yystack[$this->yyidx + -1]->minor[0]) ? $this->yystack[$this->yyidx + -1]->minor[0] : array($this->yystack[$this->yyidx + -1]->minor[0]));
    if (is_array($var)) {
        /* let's check if it is an object or array */
        $this->compiler->set_context($this->yystack[$this->yyidx + -3]->minor, current($var));
    }

    $this->_retvalue = array('operation' => 'loop', 'variable' => $this->yystack[$this->yyidx + -3]->minor, 'index' => NULL, 'array' => $this->yystack[$this->yyidx + -1]->minor);
    }
#line 1671 "lib/Haanga/Compiler/Parser.php"
#line 183 "lib/Haanga/Compiler/Parser.y"
    function yy_r30(){
    /* Try to get the variable */
    $var = $this->compiler->get_context(is_array($this->yystack[$this->yyidx + -1]->minor[0]) ? $this->yystack[$this->yyidx + -1]->minor[0] : array($this->yystack[$this->yyidx + -1]->minor[0]));
    if (is_array($var)) {
        /* let's check if it is an object or array */
        $this->compiler->set_context($this->yystack[$this->yyidx + -3]->minor, current($var));
    }
    $this->_retvalue = array('operation' => 'loop', 'variable' => $this->yystack[$this->yyidx + -3]->minor, 'index' => $this->yystack[$this->yyidx + -5]->minor, 'array' => $this->yystack[$this->yyidx + -1]->minor);
    }
#line 1682 "lib/Haanga/Compiler/Parser.php"
#line 192 "lib/Haanga/Compiler/Parser.y"
    function yy_r31(){ 
    if ($this->yystack[$this->yyidx + -1]->minor != "endfor") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endfor");
    }
    $this->_retvalue = $this->yystack[$this->yyidx + -4]->minor;
    $this->_retvalue['body'] = $this->yystack[$this->yyidx + -3]->minor;
    }
#line 1691 "lib/Haanga/Compiler/Parser.php"
#line 200 "lib/Haanga/Compiler/Parser.y"
    function yy_r32(){ 
    if ($this->yystack[$this->yyidx + -1]->minor != "endfor") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endfor");
    }
    $this->_retvalue = $this->yystack[$this->yyidx + -8]->minor;
    $this->_retvalue['body']  = $this->yystack[$this->yyidx + -7]->minor;
    $this->_retvalue['empty'] = $this->yystack[$this->yyidx + -3]->minor;
    }
#line 1701 "lib/Haanga/Compiler/Parser.php"
#line 209 "lib/Haanga/Compiler/Parser.y"
    function yy_r33(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endif") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endif");
    }
    $this->_retvalue = array('operation' => 'if', 'expr' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1709 "lib/Haanga/Compiler/Parser.php"
#line 215 "lib/Haanga/Compiler/Parser.y"
    function yy_r34(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endif") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endif");
    }
    $this->_retvalue = array('operation' => 'if', 'expr' => $this->yystack[$this->yyidx + -9]->minor, 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1717 "lib/Haanga/Compiler/Parser.php"
#line 223 "lib/Haanga/Compiler/Parser.y"
    function yy_r35(){ 
    if ($this->yystack[$this->yyidx + -1]->minor != "endifchanged") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifchanged");
    }
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1725 "lib/Haanga/Compiler/Parser.php"
#line 230 "lib/Haanga/Compiler/Parser.y"
    function yy_r36(){ 
    if ($this->yystack[$this->yyidx + -1]->minor != "endifchanged") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifchanged");
    }
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -3]->minor, 'check' => $this->yystack[$this->yyidx + -5]->minor);
    }
#line 1733 "lib/Haanga/Compiler/Parser.php"
#line 236 "lib/Haanga/Compiler/Parser.y"
    function yy_r37(){ 
    if ($this->yystack[$this->yyidx + -1]->minor != "endifchanged") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifchanged");
    }
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1741 "lib/Haanga/Compiler/Parser.php"
#line 243 "lib/Haanga/Compiler/Parser.y"
    function yy_r38(){ 
    if ($this->yystack[$this->yyidx + -1]->minor != "endifchanged") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifchanged");
    }
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -7]->minor, 'check' => $this->yystack[$this->yyidx + -9]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1749 "lib/Haanga/Compiler/Parser.php"
#line 251 "lib/Haanga/Compiler/Parser.y"
    function yy_r39(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endifequal") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifequal");
    }
    $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '==', 1 => $this->yystack[$this->yyidx + -6]->minor, 2 => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1757 "lib/Haanga/Compiler/Parser.php"
#line 257 "lib/Haanga/Compiler/Parser.y"
    function yy_r40(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endifequal") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifequal");
    }
    $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '==', 1 => $this->yystack[$this->yyidx + -10]->minor, 2 => $this->yystack[$this->yyidx + -9]->minor, 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1765 "lib/Haanga/Compiler/Parser.php"
#line 263 "lib/Haanga/Compiler/Parser.y"
    function yy_r41(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endifnotequal") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifnotequal");
    }
    $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '!=', 1 => $this->yystack[$this->yyidx + -6]->minor, 2 => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1773 "lib/Haanga/Compiler/Parser.php"
#line 269 "lib/Haanga/Compiler/Parser.y"
    function yy_r42(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endifnotequal") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifnotequal");
    }
    $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '!=', 1 => $this->yystack[$this->yyidx + -10]->minor, 2 => $this->yystack[$this->yyidx + -9]->minor, 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1781 "lib/Haanga/Compiler/Parser.php"
#line 277 "lib/Haanga/Compiler/Parser.y"
    function yy_r43(){ 
    if ($this->yystack[$this->yyidx + -1]->minor != "endblock") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endblock");
    }
    $this->_retvalue = array('operation' => 'block', 'name' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1789 "lib/Haanga/Compiler/Parser.php"
#line 284 "lib/Haanga/Compiler/Parser.y"
    function yy_r44(){
    if ($this->yystack[$this->yyidx + -2]->minor != "endblock") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -2]->minor.", expecting endblock");
    }
    $this->_retvalue = array('operation' => 'block', 'name' => $this->yystack[$this->yyidx + -6]->minor, 'body' => $this->yystack[$this->yyidx + -4]->minor); 
    }
#line 1797 "lib/Haanga/Compiler/Parser.php"
#line 291 "lib/Haanga/Compiler/Parser.y"
    function yy_r45(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endblock") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endblock");
    }
    $this->_retvalue = array('operation' => 'block', 'name' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1805 "lib/Haanga/Compiler/Parser.php"
#line 306 "lib/Haanga/Compiler/Parser.y"
    function yy_r47(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endfilter") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endfilter");
    }
    $this->_retvalue = array('operation' => 'filter', 'functions' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1813 "lib/Haanga/Compiler/Parser.php"
#line 314 "lib/Haanga/Compiler/Parser.y"
    function yy_r48(){ $this->_retvalue=array('operation' => 'regroup', 'array' => $this->yystack[$this->yyidx + -4]->minor, 'row' => $this->yystack[$this->yyidx + -2]->minor, 'as' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1816 "lib/Haanga/Compiler/Parser.php"
#line 317 "lib/Haanga/Compiler/Parser.y"
    function yy_r49(){ $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor; $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1819 "lib/Haanga/Compiler/Parser.php"
#line 318 "lib/Haanga/Compiler/Parser.y"
    function yy_r50(){ $this->_retvalue = array($this->yystack[$this->yyidx + 0]->minor);     }
#line 1822 "lib/Haanga/Compiler/Parser.php"
#line 320 "lib/Haanga/Compiler/Parser.y"
    function yy_r51(){ $this->_retvalue = array($this->yystack[$this->yyidx + -2]->minor, 'args'=>array($this->yystack[$this->yyidx + 0]->minor));     }
#line 1825 "lib/Haanga/Compiler/Parser.php"
#line 324 "lib/Haanga/Compiler/Parser.y"
    function yy_r53(){ $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor; $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1828 "lib/Haanga/Compiler/Parser.php"
#line 330 "lib/Haanga/Compiler/Parser.y"
    function yy_r56(){ $this->_retvalue = array('var' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1831 "lib/Haanga/Compiler/Parser.php"
#line 331 "lib/Haanga/Compiler/Parser.y"
    function yy_r57(){ $this->_retvalue = array('number' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1834 "lib/Haanga/Compiler/Parser.php"
#line 332 "lib/Haanga/Compiler/Parser.y"
    function yy_r58(){ $this->_retvalue = trim(@$this->yystack[$this->yyidx + 0]->minor);     }
#line 1837 "lib/Haanga/Compiler/Parser.php"
#line 333 "lib/Haanga/Compiler/Parser.y"
    function yy_r59(){ $this->_retvalue = array('string' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1840 "lib/Haanga/Compiler/Parser.php"
#line 336 "lib/Haanga/Compiler/Parser.y"
    function yy_r60(){ $this->_retvalue = array('var_filter' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1843 "lib/Haanga/Compiler/Parser.php"
#line 346 "lib/Haanga/Compiler/Parser.y"
    function yy_r66(){ $this->_retvalue = array('op_expr' => 'not', $this->yystack[$this->yyidx + 0]->minor);     }
#line 1846 "lib/Haanga/Compiler/Parser.php"
#line 347 "lib/Haanga/Compiler/Parser.y"
    function yy_r67(){ $this->_retvalue = array('op_expr' => @$this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);     }
#line 1849 "lib/Haanga/Compiler/Parser.php"
#line 350 "lib/Haanga/Compiler/Parser.y"
    function yy_r70(){ $this->_retvalue = array('op_expr' => trim(@$this->yystack[$this->yyidx + -1]->minor), $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);     }
#line 1852 "lib/Haanga/Compiler/Parser.php"
#line 352 "lib/Haanga/Compiler/Parser.y"
    function yy_r72(){ $this->_retvalue = array('op_expr' => 'expr', $this->yystack[$this->yyidx + -1]->minor);     }
#line 1855 "lib/Haanga/Compiler/Parser.php"
#line 356 "lib/Haanga/Compiler/Parser.y"
    function yy_r74(){ if (!is_array($this->yystack[$this->yyidx + -2]->minor)) { $this->_retvalue = array($this->yystack[$this->yyidx + -2]->minor); } else { $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor; }  $this->_retvalue[]=array('object' => $this->yystack[$this->yyidx + 0]->minor);    }
#line 1858 "lib/Haanga/Compiler/Parser.php"
#line 357 "lib/Haanga/Compiler/Parser.y"
    function yy_r75(){ if (!is_array($this->yystack[$this->yyidx + -2]->minor)) { $this->_retvalue = array($this->yystack[$this->yyidx + -2]->minor); } else { $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor; } $this->_retvalue[] = ($this->compiler->var_is_object($this->_retvalue)) ? array('object' => $this->yystack[$this->yyidx + 0]->minor) : $this->yystack[$this->yyidx + 0]->minor;    }
#line 1861 "lib/Haanga/Compiler/Parser.php"
#line 358 "lib/Haanga/Compiler/Parser.y"
    function yy_r76(){ if (!is_array($this->yystack[$this->yyidx + -3]->minor)) { $this->_retvalue = array($this->yystack[$this->yyidx + -3]->minor); } else { $this->_retvalue = $this->yystack[$this->yyidx + -3]->minor; }  $this->_retvalue[]=$this->yystack[$this->yyidx + -1]->minor;    }
#line 1864 "lib/Haanga/Compiler/Parser.php"

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
#line 1984 "lib/Haanga/Compiler/Parser.php"
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

#line 2005 "lib/Haanga/Compiler/Parser.php"
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