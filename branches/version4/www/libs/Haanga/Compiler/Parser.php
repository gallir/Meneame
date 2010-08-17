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
    const T_CUSTOM_END                   = 26;
    const T_CUSTOM_TAG                   = 27;
    const T_AS                           = 28;
    const T_CUSTOM_BLOCK                 = 29;
    const T_SPACEFULL                    = 30;
    const T_WITH                         = 31;
    const T_LOAD                         = 32;
    const T_FOR                          = 33;
    const T_COMMA                        = 34;
    const T_EMPTY                        = 35;
    const T_IF                           = 36;
    const T_ELSE                         = 37;
    const T_IFCHANGED                    = 38;
    const T_IFEQUAL                      = 39;
    const T_IFNOTEQUAL                   = 40;
    const T_BLOCK                        = 41;
    const T_NUMERIC                      = 42;
    const T_FILTER                       = 43;
    const T_REGROUP                      = 44;
    const T_BY                           = 45;
    const T_PIPE                         = 46;
    const T_COLON                        = 47;
    const T_TRUE                         = 48;
    const T_FALSE                        = 49;
    const T_STRING                       = 50;
    const T_INTL                         = 51;
    const T_RPARENT                      = 52;
    const T_LPARENT                      = 53;
    const T_OBJ                          = 54;
    const T_ALPHA                        = 55;
    const T_DOT                          = 56;
    const T_BRACKETS_OPEN                = 57;
    const T_BRACKETS_CLOSE               = 58;
    const YY_NO_ACTION = 318;
    const YY_ACCEPT_ACTION = 317;
    const YY_ERROR_ACTION = 316;

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
    const YY_SZ_ACTTAB = 1032;
static public $yy_action = array(
 /*     0 */    40,  183,   44,   72,  141,   34,  234,   35,  164,   59,
 /*    10 */    83,   64,   95,  236,   26,  138,   36,   30,   31,   51,
 /*    20 */   210,   48,   49,   40,  114,   44,   72,  153,   34,   50,
 /*    30 */    35,  164,   59,   83,   64,   50,  155,   26,   50,   36,
 /*    40 */    30,   31,   51,   50,   48,   49,   40,   76,   44,   72,
 /*    50 */   134,   34,   91,   35,  164,   59,   83,   64,  317,   61,
 /*    60 */    26,  131,   36,   30,   31,   51,  205,   48,   49,   40,
 /*    70 */    58,   44,   72,  146,   34,   87,   35,  164,   59,   83,
 /*    80 */    64,  185,  143,   26,  148,   36,   30,   31,   51,  208,
 /*    90 */    48,   49,   40,  175,   44,   72,  158,   34,  203,   35,
 /*   100 */   164,   59,   83,   64,   75,   50,   26,  167,   36,   30,
 /*   110 */    31,   51,  179,   48,   49,   40,  223,   44,   72,  161,
 /*   120 */    34,  119,   35,  164,   59,   83,   64,  204,  209,   26,
 /*   130 */   162,   36,   30,   31,   51,   92,   48,   49,   40,  192,
 /*   140 */    44,   72,  133,   34,  222,   35,  164,   59,   83,   64,
 /*   150 */   197,  169,   26,   86,   36,   30,   31,   51,  181,   48,
 /*   160 */    49,   40,  187,   44,   72,   52,   34,  198,   35,  164,
 /*   170 */    59,   83,   64,  213,  229,   26,  194,   36,   30,   31,
 /*   180 */    51,  231,   48,   49,   40,   56,   44,   72,  149,   34,
 /*   190 */   225,   35,  164,   59,   83,   64,  132,   94,   26,  196,
 /*   200 */    36,   30,   31,   51,  189,   48,   49,   40,  122,   44,
 /*   210 */    72,  156,   34,  217,   35,  164,   59,   83,   64,   50,
 /*   220 */   102,   26,  186,   36,   30,   31,   51,  200,   48,   49,
 /*   230 */    40,  182,   44,   72,  165,   34,  227,   35,  164,   59,
 /*   240 */    83,   64,  195,   96,   26,   84,   36,   30,   31,   51,
 /*   250 */    90,   48,   49,   40,  112,   44,   72,  150,   34,   99,
 /*   260 */    35,  164,   59,   83,   64,  212,  111,   26,  120,   36,
 /*   270 */    30,   31,   51,   67,   48,   49,   40,   74,   44,   72,
 /*   280 */   163,   34,   66,   35,  164,   59,   83,   64,  164,   73,
 /*   290 */    26,   53,   36,   30,   31,   51,   81,   48,   49,   40,
 /*   300 */    78,   44,   72,  151,   34,  164,   35,  164,   59,   83,
 /*   310 */    64,   65,  117,   26,  164,   36,   30,   31,   51,  115,
 /*   320 */    48,   49,   40,  110,   44,   72,  160,   34,  116,   35,
 /*   330 */   164,   59,   83,   64,  113,  118,   26,   70,   36,   30,
 /*   340 */    31,   51,   54,   48,   49,   40,   57,   44,   72,  144,
 /*   350 */    34,   63,   35,  164,   59,   83,   64,   68,   77,   26,
 /*   360 */    62,   36,   30,   31,   51,   79,   48,   49,   40,   71,
 /*   370 */    44,   72,  140,   34,  164,   35,  164,   59,   83,   64,
 /*   380 */   164,  164,   26,  164,   36,   30,   31,   51,  164,   48,
 /*   390 */    49,   40,  164,   44,   72,  139,   34,  164,   35,  164,
 /*   400 */    59,   83,   64,  164,  164,   26,  164,   36,   30,   31,
 /*   410 */    51,  164,   48,   49,   40,  164,   44,   72,  137,   34,
 /*   420 */   164,   35,  164,   59,   83,   64,  164,  164,   26,  164,
 /*   430 */    36,   30,   31,   51,  164,   48,   49,   40,  164,   44,
 /*   440 */    72,  128,   34,  164,   35,  164,   59,   83,   64,  164,
 /*   450 */   164,   26,  164,   36,   30,   31,   51,  164,   48,   49,
 /*   460 */    40,  164,   44,   72,  164,   34,  101,   35,  164,   59,
 /*   470 */    83,   64,  164,  164,   26,  164,   36,   30,   31,   51,
 /*   480 */   164,   48,   49,   28,   29,   22,   22,   22,   22,   22,
 /*   490 */    22,   22,   24,   24,   25,   25,   25,  166,  164,  157,
 /*   500 */    41,  164,  166,   98,  157,   41,   47,   28,   29,   22,
 /*   510 */    22,   22,   22,   22,   22,   22,   24,   24,   25,   25,
 /*   520 */    25,   28,   29,   22,   22,   22,   22,   22,   22,   22,
 /*   530 */    24,   24,   25,   25,   25,   29,   22,   22,   22,   22,
 /*   540 */    22,   22,   22,   24,   24,   25,   25,   25,  226,  166,
 /*   550 */   174,  157,   41,  164,  180,  164,  177,  154,  221,  220,
 /*   560 */   216,  218,  219,  193,  235,  232,  164,  215,  211,  159,
 /*   570 */    93,  184,   82,  184,   25,   25,   25,  164,   43,  166,
 /*   580 */    42,  157,   41,  164,  100,  184,  190,  166,  164,  157,
 /*   590 */    41,  164,  191,  191,  185,  143,  164,   80,  164,  173,
 /*   600 */    22,   22,   22,   22,   22,   22,   22,   24,   24,   25,
 /*   610 */    25,   25,   27,    8,  164,  166,  228,  157,   41,  164,
 /*   620 */   184,   69,  184,  166,  164,  157,   41,  127,  164,  224,
 /*   630 */   168,  164,   46,  164,  184,  190,  135,  184,  119,  184,
 /*   640 */   164,  191,  191,  185,  143,  207,  164,  119,  173,  164,
 /*   650 */   170,  184,  171,  130,  207,  164,   89,  164,  172,  172,
 /*   660 */   185,  143,  164,   23,  135,  173,   85,  164,  129,  184,
 /*   670 */   184,  184,  184,  164,  164,  119,  164,   43,  170,  119,
 /*   680 */   106,  176,  207,  184,  184,  190,  207,  166,  164,  157,
 /*   690 */    41,  191,  191,  185,  143,  126,   88,  173,  173,  233,
 /*   700 */   184,  125,  184,  184,  230,  184,  119,   43,  164,  164,
 /*   710 */   164,  164,  119,  207,  184,  190,  124,  184,  164,  207,
 /*   720 */    60,  191,  191,  185,  143,  135,   97,  119,  173,  164,
 /*   730 */   184,  173,  184,  164,  207,  166,  119,  157,   41,  170,
 /*   740 */   164,  109,  176,  207,  184,  190,  166,  164,  157,   41,
 /*   750 */   135,  191,  191,  185,  143,  164,  103,  164,  173,  135,
 /*   760 */   184,  119,  184,   45,  170,  164,  105,  176,  207,  164,
 /*   770 */   119,  164,  164,  170,  184,  190,  145,  207,  164,  164,
 /*   780 */   135,  191,  191,  185,  143,  164,   55,  184,  173,  184,
 /*   790 */   184,  119,  184,  164,  170,  164,  108,  176,  207,  164,
 /*   800 */   164,  184,  152,  164,  184,  190,  166,  164,  157,   41,
 /*   810 */   135,  191,  191,  185,  143,  173,  164,  164,  173,  135,
 /*   820 */   184,  119,  184,  164,  170,  164,  107,  176,  207,  164,
 /*   830 */   119,  164,  164,  170,  184,  171,   32,  207,  164,  135,
 /*   840 */   164,  172,  172,  185,  143,  164,  164,  135,  173,  164,
 /*   850 */   119,  164,    7,  170,  164,  123,  176,  207,  119,  135,
 /*   860 */   164,  170,  164,  104,  176,  207,  164,  135,  224,  168,
 /*   870 */   119,   46,   12,  170,  164,  178,  176,  207,  119,   18,
 /*   880 */   164,  170,  164,    6,   33,  207,  164,    2,  224,  168,
 /*   890 */   164,   46,  214,    4,  164,  224,  168,   21,   46,  224,
 /*   900 */   168,   17,   46,  224,  168,    3,   46,  164,  164,  224,
 /*   910 */   168,   13,   46,  224,  168,   14,   46,  224,  168,   10,
 /*   920 */    46,  224,  168,  166,   46,  157,   41,  224,  168,    1,
 /*   930 */    46,  224,  168,   19,   46,  224,  168,   16,   46,  164,
 /*   940 */   164,  164,  164,  164,  164,  224,  168,    9,   46,  224,
 /*   950 */   168,   11,   46,  224,  168,  164,   46,  164,  164,  164,
 /*   960 */   164,  164,  199,  224,  168,  164,   46,  224,  168,  164,
 /*   970 */    46,  199,  121,   37,   15,  188,  199,  164,   20,  164,
 /*   980 */   164,  121,   38,    5,  188,  164,  121,   39,  201,  188,
 /*   990 */   224,  168,  164,   46,  224,  168,  202,   46,  121,  224,
 /*  1000 */   168,  188,   46,  136,  206,  164,  121,  164,  164,  188,
 /*  1010 */   164,  164,  142,  121,  121,  164,  188,  188,  147,  164,
 /*  1020 */   164,  164,  121,  164,  164,  188,  164,  164,  121,  164,
 /*  1030 */   164,  188,
    );
    static public $yy_lookahead = array(
 /*     0 */    22,   55,   24,   25,   26,   27,   23,   29,   30,   31,
 /*    10 */    32,   33,   23,   21,   36,   37,   38,   39,   40,   41,
 /*    20 */    23,   43,   44,   22,   75,   24,   25,   26,   27,   46,
 /*    30 */    29,   30,   31,   32,   33,   46,   35,   36,   46,   38,
 /*    40 */    39,   40,   41,   46,   43,   44,   22,   61,   24,   25,
 /*    50 */    26,   27,   23,   29,   30,   31,   32,   33,   60,   61,
 /*    60 */    36,   37,   38,   39,   40,   41,   23,   43,   44,   22,
 /*    70 */    61,   24,   25,   26,   27,   23,   29,   30,   31,   32,
 /*    80 */    33,   50,   51,   36,   37,   38,   39,   40,   41,   23,
 /*    90 */    43,   44,   22,   55,   24,   25,   26,   27,   23,   29,
 /*   100 */    30,   31,   32,   33,   45,   46,   36,   37,   38,   39,
 /*   110 */    40,   41,   23,   43,   44,   22,   19,   24,   25,   26,
 /*   120 */    27,   75,   29,   30,   31,   32,   33,   23,   82,   36,
 /*   130 */    37,   38,   39,   40,   41,   23,   43,   44,   22,   58,
 /*   140 */    24,   25,   26,   27,   23,   29,   30,   31,   32,   33,
 /*   150 */    23,   23,   36,   23,   38,   39,   40,   41,   23,   43,
 /*   160 */    44,   22,   52,   24,   25,   26,   27,   23,   29,   30,
 /*   170 */    31,   32,   33,   23,   23,   36,   23,   38,   39,   40,
 /*   180 */    41,   23,   43,   44,   22,   61,   24,   25,   26,   27,
 /*   190 */    23,   29,   30,   31,   32,   33,   50,   23,   36,   23,
 /*   200 */    38,   39,   40,   41,   23,   43,   44,   22,   75,   24,
 /*   210 */    25,   26,   27,   23,   29,   30,   31,   32,   33,   46,
 /*   220 */    23,   36,   23,   38,   39,   40,   41,   23,   43,   44,
 /*   230 */    22,   62,   24,   25,   26,   27,   23,   29,   30,   31,
 /*   240 */    32,   33,   23,   23,   36,   23,   38,   39,   40,   41,
 /*   250 */    23,   43,   44,   22,   75,   24,   25,   26,   27,   23,
 /*   260 */    29,   30,   31,   32,   33,   78,   75,   36,   75,   38,
 /*   270 */    39,   40,   41,   61,   43,   44,   22,   61,   24,   25,
 /*   280 */    26,   27,   61,   29,   30,   31,   32,   33,   83,   61,
 /*   290 */    36,   61,   38,   39,   40,   41,   61,   43,   44,   22,
 /*   300 */    61,   24,   25,   26,   27,   83,   29,   30,   31,   32,
 /*   310 */    33,   61,   75,   36,   83,   38,   39,   40,   41,   75,
 /*   320 */    43,   44,   22,   75,   24,   25,   26,   27,   75,   29,
 /*   330 */    30,   31,   32,   33,   75,   75,   36,   61,   38,   39,
 /*   340 */    40,   41,   61,   43,   44,   22,   61,   24,   25,   26,
 /*   350 */    27,   61,   29,   30,   31,   32,   33,   61,   61,   36,
 /*   360 */    61,   38,   39,   40,   41,   61,   43,   44,   22,   61,
 /*   370 */    24,   25,   26,   27,   83,   29,   30,   31,   32,   33,
 /*   380 */    83,   83,   36,   83,   38,   39,   40,   41,   83,   43,
 /*   390 */    44,   22,   83,   24,   25,   26,   27,   83,   29,   30,
 /*   400 */    31,   32,   33,   83,   83,   36,   83,   38,   39,   40,
 /*   410 */    41,   83,   43,   44,   22,   83,   24,   25,   26,   27,
 /*   420 */    83,   29,   30,   31,   32,   33,   83,   83,   36,   83,
 /*   430 */    38,   39,   40,   41,   83,   43,   44,   22,   83,   24,
 /*   440 */    25,   26,   27,   83,   29,   30,   31,   32,   33,   83,
 /*   450 */    83,   36,   83,   38,   39,   40,   41,   83,   43,   44,
 /*   460 */    22,   83,   24,   25,   83,   27,   23,   29,   30,   31,
 /*   470 */    32,   33,   83,   83,   36,   83,   38,   39,   40,   41,
 /*   480 */    83,   43,   44,    3,    4,    5,    6,    7,    8,    9,
 /*   490 */    10,   11,   12,   13,   14,   15,   16,   54,   83,   56,
 /*   500 */    57,   83,   54,   23,   56,   57,   11,    3,    4,    5,
 /*   510 */     6,    7,    8,    9,   10,   11,   12,   13,   14,   15,
 /*   520 */    16,    3,    4,    5,    6,    7,    8,    9,   10,   11,
 /*   530 */    12,   13,   14,   15,   16,    4,    5,    6,    7,    8,
 /*   540 */     9,   10,   11,   12,   13,   14,   15,   16,   23,   54,
 /*   550 */    23,   56,   57,   83,   63,   83,   52,   66,   67,   68,
 /*   560 */    69,   70,   71,   72,   73,   74,   83,   23,   77,   42,
 /*   570 */    79,   27,   28,   29,   14,   15,   16,   83,   34,   54,
 /*   580 */    47,   56,   57,   83,   23,   41,   42,   54,   83,   56,
 /*   590 */    57,   83,   48,   49,   50,   51,   83,   28,   83,   55,
 /*   600 */     5,    6,    7,    8,    9,   10,   11,   12,   13,   14,
 /*   610 */    15,   16,    2,    1,   83,   54,   23,   56,   57,   83,
 /*   620 */    27,   28,   29,   54,   83,   56,   57,   64,   83,   17,
 /*   630 */    18,   83,   20,   83,   41,   42,   64,   27,   75,   29,
 /*   640 */    83,   48,   49,   50,   51,   82,   83,   75,   55,   83,
 /*   650 */    78,   41,   42,   81,   82,   83,   23,   83,   48,   49,
 /*   660 */    50,   51,   83,   53,   64,   55,   23,   83,   64,   27,
 /*   670 */    27,   29,   29,   83,   83,   75,   83,   34,   78,   75,
 /*   680 */    80,   81,   82,   41,   41,   42,   82,   54,   83,   56,
 /*   690 */    57,   48,   49,   50,   51,   64,   23,   55,   55,   23,
 /*   700 */    27,   64,   29,   27,   23,   29,   75,   34,   83,   83,
 /*   710 */    83,   83,   75,   82,   41,   42,   64,   41,   83,   82,
 /*   720 */    28,   48,   49,   50,   51,   64,   23,   75,   55,   83,
 /*   730 */    27,   55,   29,   83,   82,   54,   75,   56,   57,   78,
 /*   740 */    83,   80,   81,   82,   41,   42,   54,   83,   56,   57,
 /*   750 */    64,   48,   49,   50,   51,   83,   23,   83,   55,   64,
 /*   760 */    27,   75,   29,   11,   78,   83,   80,   81,   82,   83,
 /*   770 */    75,   83,   83,   78,   41,   42,   81,   82,   83,   83,
 /*   780 */    64,   48,   49,   50,   51,   83,   34,   27,   55,   29,
 /*   790 */    27,   75,   29,   83,   78,   83,   80,   81,   82,   83,
 /*   800 */    83,   41,   42,   83,   41,   42,   54,   83,   56,   57,
 /*   810 */    64,   48,   49,   50,   51,   55,   83,   83,   55,   64,
 /*   820 */    27,   75,   29,   83,   78,   83,   80,   81,   82,   83,
 /*   830 */    75,   83,   83,   78,   41,   42,   81,   82,   83,   64,
 /*   840 */    83,   48,   49,   50,   51,   83,   83,   64,   55,   83,
 /*   850 */    75,   83,    1,   78,   83,   80,   81,   82,   75,   64,
 /*   860 */    83,   78,   83,   80,   81,   82,   83,   64,   17,   18,
 /*   870 */    75,   20,    1,   78,   83,   80,   81,   82,   75,    1,
 /*   880 */    83,   78,   83,    1,   81,   82,   83,    1,   17,   18,
 /*   890 */    83,   20,   23,    1,   83,   17,   18,    1,   20,   17,
 /*   900 */    18,    1,   20,   17,   18,    1,   20,   83,   83,   17,
 /*   910 */    18,    1,   20,   17,   18,    1,   20,   17,   18,    1,
 /*   920 */    20,   17,   18,   54,   20,   56,   57,   17,   18,    1,
 /*   930 */    20,   17,   18,    1,   20,   17,   18,    1,   20,   83,
 /*   940 */    83,   83,   83,   83,   83,   17,   18,    1,   20,   17,
 /*   950 */    18,    1,   20,   17,   18,   83,   20,   83,   83,   83,
 /*   960 */    83,   83,   65,   17,   18,   83,   20,   17,   18,   83,
 /*   970 */    20,   65,   75,   76,    1,   78,   65,   83,    1,   83,
 /*   980 */    83,   75,   76,    1,   78,   83,   75,   76,   65,   78,
 /*   990 */    17,   18,   83,   20,   17,   18,   65,   20,   75,   17,
 /*  1000 */    18,   78,   20,   65,   65,   83,   75,   83,   83,   78,
 /*  1010 */    83,   83,   65,   75,   75,   83,   78,   78,   65,   83,
 /*  1020 */    83,   83,   75,   83,   83,   78,   83,   83,   75,   83,
 /*  1030 */    83,   78,
);
    const YY_SHIFT_USE_DFLT = -55;
    const YY_SHIFT_MAX = 168;
    static public $yy_shift_ofst = array(
 /*     0 */   -55,   93,   47,  -22,   70,    1,   24,  415,  392,  277,
 /*    10 */   185,  162,  139,  369,  116,  231,  300,  254,  323,  346,
 /*    20 */   208,  438,  610,  610,  610,  610,  610,  610,  610,  610,
 /*    30 */   793,  793,  793,  793,  593,  733,  703,  544,  673,  643,
 /*    40 */   763,  763,  763,  763,  763,  642,  642,  642,  642,  642,
 /*    50 */   642,  760,  676,  946,  950,  642,  973,  982,  977,  642,
 /*    60 */   642,  896,  892,  886,  642,  900,  904,  882,  910,  642,
 /*    70 */   612,  878,  642,  851,  871,  642,  936,  928,  918,  914,
 /*    80 */   642,  932,  642,   31,  -55,  -55,  -55,  -55,  -55,  -55,
 /*    90 */   -55,  -55,  -55,  -55,  -55,  -55,  -55,  -55,  -55,  -55,
 /*   100 */   -55,  -55,  -55,  -55,  480,  504,  518,  531,  595,  595,
 /*   110 */   752,  633,  569,  681,  692,  561,  443,  525,  495,  533,
 /*   120 */   869,  448,  448,  560,   59,  -17,  -11,   -3,  527,   -8,
 /*   130 */   227,  222,  110,  204,  199,  173,  167,  158,  174,  190,
 /*   140 */   181,  151,   81,  146,  213,  220,  150,   89,  236,  104,
 /*   150 */    75,   43,   52,   66,  121,  130,  135,   38,  127,  128,
 /*   160 */   153,  144,  112,  219,  197,  176,  -54,   29,   97,
);
    const YY_REDUCE_USE_DFLT = -52;
    const YY_REDUCE_MAX = 103;
    static public $yy_reduce_ofst = array(
 /*     0 */    -2,  491,  491,  491,  491,  491,  491,  491,  491,  491,
 /*    10 */   491,  491,  491,  491,  491,  491,  491,  491,  491,  491,
 /*    20 */   491,  491,  716,  686,  775,  795,  783,  600,  746,  661,
 /*    30 */   755,  803,  695,  572,  897,  911,  906,  923,  923,  923,
 /*    40 */   953,  947,  939,  931,  938,  563,  604,  637,  631,  652,
 /*    50 */    46,  191,  237,  169,  169,  260,  169,  169,  169,  -51,
 /*    60 */   253,  169,  169,  169,  248,  169,  169,  169,  169,  259,
 /*    70 */   169,  169,  244,  169,  169,  179,  169,  169,  169,  169,
 /*    80 */   133,  169,  193,  187,  235,  239,  230,  228,  212,  216,
 /*    90 */   221,  250,  304,  285,  276,  296,  297,  299,  290,  281,
 /*   100 */   308,  124,    9,  -14,
);
    static public $yyExpectedTokens = array(
        /* 0 */ array(),
        /* 1 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 37, 38, 39, 40, 41, 43, 44, ),
        /* 2 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 37, 38, 39, 40, 41, 43, 44, ),
        /* 3 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 37, 38, 39, 40, 41, 43, 44, ),
        /* 4 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 37, 38, 39, 40, 41, 43, 44, ),
        /* 5 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 35, 36, 38, 39, 40, 41, 43, 44, ),
        /* 6 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 37, 38, 39, 40, 41, 43, 44, ),
        /* 7 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 8 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 9 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 10 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 11 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 12 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 13 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 14 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 15 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 16 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 17 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 18 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 19 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 20 */ array(22, 24, 25, 26, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 21 */ array(22, 24, 25, 27, 29, 30, 31, 32, 33, 36, 38, 39, 40, 41, 43, 44, ),
        /* 22 */ array(2, 27, 29, 41, 42, 48, 49, 50, 51, 53, 55, ),
        /* 23 */ array(2, 27, 29, 41, 42, 48, 49, 50, 51, 53, 55, ),
        /* 24 */ array(2, 27, 29, 41, 42, 48, 49, 50, 51, 53, 55, ),
        /* 25 */ array(2, 27, 29, 41, 42, 48, 49, 50, 51, 53, 55, ),
        /* 26 */ array(2, 27, 29, 41, 42, 48, 49, 50, 51, 53, 55, ),
        /* 27 */ array(2, 27, 29, 41, 42, 48, 49, 50, 51, 53, 55, ),
        /* 28 */ array(2, 27, 29, 41, 42, 48, 49, 50, 51, 53, 55, ),
        /* 29 */ array(2, 27, 29, 41, 42, 48, 49, 50, 51, 53, 55, ),
        /* 30 */ array(27, 29, 41, 42, 48, 49, 50, 51, 55, ),
        /* 31 */ array(27, 29, 41, 42, 48, 49, 50, 51, 55, ),
        /* 32 */ array(27, 29, 41, 42, 48, 49, 50, 51, 55, ),
        /* 33 */ array(27, 29, 41, 42, 48, 49, 50, 51, 55, ),
        /* 34 */ array(23, 27, 28, 29, 41, 42, 48, 49, 50, 51, 55, ),
        /* 35 */ array(23, 27, 29, 41, 42, 48, 49, 50, 51, 55, ),
        /* 36 */ array(23, 27, 29, 41, 42, 48, 49, 50, 51, 55, ),
        /* 37 */ array(23, 27, 28, 29, 34, 41, 42, 48, 49, 50, 51, 55, ),
        /* 38 */ array(23, 27, 29, 34, 41, 42, 48, 49, 50, 51, 55, ),
        /* 39 */ array(23, 27, 29, 34, 41, 42, 48, 49, 50, 51, 55, ),
        /* 40 */ array(27, 29, 41, 42, 48, 49, 50, 51, 55, ),
        /* 41 */ array(27, 29, 41, 42, 48, 49, 50, 51, 55, ),
        /* 42 */ array(27, 29, 41, 42, 48, 49, 50, 51, 55, ),
        /* 43 */ array(27, 29, 41, 42, 48, 49, 50, 51, 55, ),
        /* 44 */ array(27, 29, 41, 42, 48, 49, 50, 51, 55, ),
        /* 45 */ array(27, 29, 41, 55, ),
        /* 46 */ array(27, 29, 41, 55, ),
        /* 47 */ array(27, 29, 41, 55, ),
        /* 48 */ array(27, 29, 41, 55, ),
        /* 49 */ array(27, 29, 41, 55, ),
        /* 50 */ array(27, 29, 41, 55, ),
        /* 51 */ array(27, 29, 41, 42, 55, ),
        /* 52 */ array(23, 27, 29, 41, 55, ),
        /* 53 */ array(1, 17, 18, 20, ),
        /* 54 */ array(1, 17, 18, 20, ),
        /* 55 */ array(27, 29, 41, 55, ),
        /* 56 */ array(1, 17, 18, 20, ),
        /* 57 */ array(1, 17, 18, 20, ),
        /* 58 */ array(1, 17, 18, 20, ),
        /* 59 */ array(27, 29, 41, 55, ),
        /* 60 */ array(27, 29, 41, 55, ),
        /* 61 */ array(1, 17, 18, 20, ),
        /* 62 */ array(1, 17, 18, 20, ),
        /* 63 */ array(1, 17, 18, 20, ),
        /* 64 */ array(27, 29, 41, 55, ),
        /* 65 */ array(1, 17, 18, 20, ),
        /* 66 */ array(1, 17, 18, 20, ),
        /* 67 */ array(1, 17, 18, 20, ),
        /* 68 */ array(1, 17, 18, 20, ),
        /* 69 */ array(27, 29, 41, 55, ),
        /* 70 */ array(1, 17, 18, 20, ),
        /* 71 */ array(1, 17, 18, 20, ),
        /* 72 */ array(27, 29, 41, 55, ),
        /* 73 */ array(1, 17, 18, 20, ),
        /* 74 */ array(1, 17, 18, 20, ),
        /* 75 */ array(27, 29, 41, 55, ),
        /* 76 */ array(1, 17, 18, 20, ),
        /* 77 */ array(1, 17, 18, 20, ),
        /* 78 */ array(1, 17, 18, 20, ),
        /* 79 */ array(1, 17, 18, 20, ),
        /* 80 */ array(27, 29, 41, 55, ),
        /* 81 */ array(1, 17, 18, 20, ),
        /* 82 */ array(27, 29, 41, 55, ),
        /* 83 */ array(50, 51, ),
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
        /* 104 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 23, ),
        /* 105 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 52, ),
        /* 106 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 107 */ array(4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 108 */ array(5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 109 */ array(5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 110 */ array(11, 34, 54, 56, 57, ),
        /* 111 */ array(23, 54, 56, 57, ),
        /* 112 */ array(28, 54, 56, 57, ),
        /* 113 */ array(23, 54, 56, 57, ),
        /* 114 */ array(28, 54, 56, 57, ),
        /* 115 */ array(23, 54, 56, 57, ),
        /* 116 */ array(23, 54, 56, 57, ),
        /* 117 */ array(23, 54, 56, 57, ),
        /* 118 */ array(11, 54, 56, 57, ),
        /* 119 */ array(47, 54, 56, 57, ),
        /* 120 */ array(23, 54, 56, 57, ),
        /* 121 */ array(54, 56, 57, ),
        /* 122 */ array(54, 56, 57, ),
        /* 123 */ array(14, 15, 16, ),
        /* 124 */ array(45, 46, ),
        /* 125 */ array(23, 46, ),
        /* 126 */ array(23, 46, ),
        /* 127 */ array(23, 46, ),
        /* 128 */ array(23, 42, ),
        /* 129 */ array(21, 46, ),
        /* 130 */ array(23, ),
        /* 131 */ array(23, ),
        /* 132 */ array(52, ),
        /* 133 */ array(23, ),
        /* 134 */ array(23, ),
        /* 135 */ array(46, ),
        /* 136 */ array(23, ),
        /* 137 */ array(23, ),
        /* 138 */ array(23, ),
        /* 139 */ array(23, ),
        /* 140 */ array(23, ),
        /* 141 */ array(23, ),
        /* 142 */ array(58, ),
        /* 143 */ array(50, ),
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
        /* 157 */ array(55, ),
        /* 158 */ array(23, ),
        /* 159 */ array(23, ),
        /* 160 */ array(23, ),
        /* 161 */ array(23, ),
        /* 162 */ array(23, ),
        /* 163 */ array(23, ),
        /* 164 */ array(23, ),
        /* 165 */ array(23, ),
        /* 166 */ array(55, ),
        /* 167 */ array(23, ),
        /* 168 */ array(19, ),
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
        /* 236 */ array(),
);
    static public $yy_default = array(
 /*     0 */   239,  316,  316,  316,  316,  316,  316,  316,  316,  316,
 /*    10 */   316,  316,  316,  316,  316,  316,  316,  316,  316,  316,
 /*    20 */   316,  316,  316,  316,  316,  316,  316,  316,  316,  316,
 /*    30 */   316,  316,  316,  316,  316,  316,  316,  316,  316,  316,
 /*    40 */   316,  316,  316,  316,  316,  316,  316,  316,  316,  316,
 /*    50 */   316,  316,  316,  316,  316,  316,  316,  316,  316,  316,
 /*    60 */   316,  237,  316,  316,  316,  316,  316,  316,  316,  316,
 /*    70 */   316,  316,  316,  316,  316,  316,  316,  316,  316,  316,
 /*    80 */   316,  316,  316,  316,  239,  239,  239,  239,  239,  239,
 /*    90 */   239,  239,  239,  239,  239,  239,  239,  239,  239,  239,
 /*   100 */   239,  239,  239,  239,  316,  316,  303,  304,  307,  305,
 /*   110 */   316,  316,  316,  316,  316,  316,  316,  316,  316,  289,
 /*   120 */   316,  293,  285,  306,  316,  316,  316,  316,  316,  316,
 /*   130 */   316,  316,  316,  316,  316,  297,  316,  316,  316,  316,
 /*   140 */   316,  316,  316,  316,  316,  316,  316,  316,  316,  316,
 /*   150 */   316,  316,  316,  316,  316,  316,  316,  316,  316,  316,
 /*   160 */   316,  316,  316,  316,  316,  316,  316,  316,  316,  283,
 /*   170 */   300,  298,  299,  314,  282,  312,  310,  309,  308,  244,
 /*   180 */   240,  261,  238,  311,  315,  301,  273,  302,  296,  275,
 /*   190 */   294,  295,  313,  252,  260,  274,  262,  272,  276,  292,
 /*   200 */   277,  290,  291,  263,  271,  269,  288,  287,  268,  286,
 /*   210 */   266,  264,  265,  270,  259,  258,  248,  284,  249,  250,
 /*   220 */   247,  246,  245,  242,  241,  251,  281,  255,  256,  278,
 /*   230 */   257,  279,  254,  280,  267,  253,  243,
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
    const YYNOCODE = 84;
    const YYSTACKDEPTH = 100;
    const YYNSTATE = 237;
    const YYNRULE = 79;
    const YYERRORSYMBOL = 59;
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
  'T_INCLUDE',     'T_AUTOESCAPE',  'T_CUSTOM_END',  'T_CUSTOM_TAG',
  'T_AS',          'T_CUSTOM_BLOCK',  'T_SPACEFULL',   'T_WITH',      
  'T_LOAD',        'T_FOR',         'T_COMMA',       'T_EMPTY',     
  'T_IF',          'T_ELSE',        'T_IFCHANGED',   'T_IFEQUAL',   
  'T_IFNOTEQUAL',  'T_BLOCK',       'T_NUMERIC',     'T_FILTER',    
  'T_REGROUP',     'T_BY',          'T_PIPE',        'T_COLON',     
  'T_TRUE',        'T_FALSE',       'T_STRING',      'T_INTL',      
  'T_RPARENT',     'T_LPARENT',     'T_OBJ',         'T_ALPHA',     
  'T_DOT',         'T_BRACKETS_OPEN',  'T_BRACKETS_CLOSE',  'error',       
  'start',         'body',          'code',          'stmts',       
  'filtered_var',  'var_or_string',  'stmt',          'for_stmt',    
  'ifchanged_stmt',  'block_stmt',    'filter_stmt',   'if_stmt',     
  'custom_tag',    'alias',         'ifequal',       'varname',     
  'params',        'regroup',       'string',        'for_def',     
  'expr',          'fvar_or_string',  'varname_args',
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
  array( 'lhs' => 60, 'rhs' => 1 ),
  array( 'lhs' => 61, 'rhs' => 2 ),
  array( 'lhs' => 61, 'rhs' => 0 ),
  array( 'lhs' => 62, 'rhs' => 2 ),
  array( 'lhs' => 62, 'rhs' => 1 ),
  array( 'lhs' => 62, 'rhs' => 2 ),
  array( 'lhs' => 62, 'rhs' => 3 ),
  array( 'lhs' => 63, 'rhs' => 3 ),
  array( 'lhs' => 63, 'rhs' => 2 ),
  array( 'lhs' => 63, 'rhs' => 1 ),
  array( 'lhs' => 63, 'rhs' => 1 ),
  array( 'lhs' => 63, 'rhs' => 1 ),
  array( 'lhs' => 63, 'rhs' => 1 ),
  array( 'lhs' => 63, 'rhs' => 1 ),
  array( 'lhs' => 63, 'rhs' => 3 ),
  array( 'lhs' => 63, 'rhs' => 1 ),
  array( 'lhs' => 63, 'rhs' => 1 ),
  array( 'lhs' => 63, 'rhs' => 1 ),
  array( 'lhs' => 63, 'rhs' => 7 ),
  array( 'lhs' => 72, 'rhs' => 2 ),
  array( 'lhs' => 72, 'rhs' => 4 ),
  array( 'lhs' => 72, 'rhs' => 3 ),
  array( 'lhs' => 72, 'rhs' => 5 ),
  array( 'lhs' => 72, 'rhs' => 6 ),
  array( 'lhs' => 72, 'rhs' => 7 ),
  array( 'lhs' => 72, 'rhs' => 6 ),
  array( 'lhs' => 73, 'rhs' => 9 ),
  array( 'lhs' => 66, 'rhs' => 1 ),
  array( 'lhs' => 66, 'rhs' => 2 ),
  array( 'lhs' => 79, 'rhs' => 5 ),
  array( 'lhs' => 79, 'rhs' => 7 ),
  array( 'lhs' => 67, 'rhs' => 5 ),
  array( 'lhs' => 67, 'rhs' => 9 ),
  array( 'lhs' => 71, 'rhs' => 7 ),
  array( 'lhs' => 71, 'rhs' => 11 ),
  array( 'lhs' => 68, 'rhs' => 6 ),
  array( 'lhs' => 68, 'rhs' => 7 ),
  array( 'lhs' => 68, 'rhs' => 10 ),
  array( 'lhs' => 68, 'rhs' => 11 ),
  array( 'lhs' => 74, 'rhs' => 8 ),
  array( 'lhs' => 74, 'rhs' => 12 ),
  array( 'lhs' => 74, 'rhs' => 8 ),
  array( 'lhs' => 74, 'rhs' => 12 ),
  array( 'lhs' => 69, 'rhs' => 7 ),
  array( 'lhs' => 69, 'rhs' => 8 ),
  array( 'lhs' => 69, 'rhs' => 7 ),
  array( 'lhs' => 69, 'rhs' => 8 ),
  array( 'lhs' => 70, 'rhs' => 7 ),
  array( 'lhs' => 77, 'rhs' => 6 ),
  array( 'lhs' => 64, 'rhs' => 3 ),
  array( 'lhs' => 64, 'rhs' => 1 ),
  array( 'lhs' => 82, 'rhs' => 3 ),
  array( 'lhs' => 82, 'rhs' => 1 ),
  array( 'lhs' => 76, 'rhs' => 2 ),
  array( 'lhs' => 76, 'rhs' => 3 ),
  array( 'lhs' => 76, 'rhs' => 1 ),
  array( 'lhs' => 65, 'rhs' => 1 ),
  array( 'lhs' => 65, 'rhs' => 1 ),
  array( 'lhs' => 65, 'rhs' => 1 ),
  array( 'lhs' => 65, 'rhs' => 1 ),
  array( 'lhs' => 81, 'rhs' => 1 ),
  array( 'lhs' => 81, 'rhs' => 1 ),
  array( 'lhs' => 81, 'rhs' => 1 ),
  array( 'lhs' => 81, 'rhs' => 1 ),
  array( 'lhs' => 78, 'rhs' => 1 ),
  array( 'lhs' => 78, 'rhs' => 3 ),
  array( 'lhs' => 80, 'rhs' => 2 ),
  array( 'lhs' => 80, 'rhs' => 3 ),
  array( 'lhs' => 80, 'rhs' => 3 ),
  array( 'lhs' => 80, 'rhs' => 3 ),
  array( 'lhs' => 80, 'rhs' => 3 ),
  array( 'lhs' => 80, 'rhs' => 3 ),
  array( 'lhs' => 80, 'rhs' => 3 ),
  array( 'lhs' => 80, 'rhs' => 1 ),
  array( 'lhs' => 75, 'rhs' => 3 ),
  array( 'lhs' => 75, 'rhs' => 3 ),
  array( 'lhs' => 75, 'rhs' => 4 ),
  array( 'lhs' => 75, 'rhs' => 1 ),
  array( 'lhs' => 75, 'rhs' => 1 ),
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
#line 1549 "lib/Haanga/Compiler/Parser.php"
#line 81 "lib/Haanga/Compiler/Parser.y"
    function yy_r1(){ $this->_retvalue=$this->yystack[$this->yyidx + -1]->minor; $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1552 "lib/Haanga/Compiler/Parser.php"
#line 82 "lib/Haanga/Compiler/Parser.y"
    function yy_r2(){ $this->_retvalue = array();     }
#line 1555 "lib/Haanga/Compiler/Parser.php"
#line 85 "lib/Haanga/Compiler/Parser.y"
    function yy_r3(){ if (count($this->yystack[$this->yyidx + 0]->minor)) $this->yystack[$this->yyidx + 0]->minor['line'] = $this->lex->getLine();  $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1558 "lib/Haanga/Compiler/Parser.php"
#line 86 "lib/Haanga/Compiler/Parser.y"
    function yy_r4(){
    $this->_retvalue = array('operation' => 'html', 'html' => $this->yystack[$this->yyidx + 0]->minor, 'line' => $this->lex->getLine() ); 
    }
#line 1563 "lib/Haanga/Compiler/Parser.php"
#line 89 "lib/Haanga/Compiler/Parser.y"
    function yy_r5(){
    $this->yystack[$this->yyidx + 0]->minor=rtrim($this->yystack[$this->yyidx + 0]->minor); $this->_retvalue = array('operation' => 'comment', 'comment' => substr($this->yystack[$this->yyidx + 0]->minor, 0, strlen($this->yystack[$this->yyidx + 0]->minor)-2)); 
    }
#line 1568 "lib/Haanga/Compiler/Parser.php"
#line 92 "lib/Haanga/Compiler/Parser.y"
    function yy_r6(){
    $this->_retvalue = array('operation' => 'print_var', 'variable' => $this->yystack[$this->yyidx + -1]->minor, 'line' => $this->lex->getLine() ); 
    }
#line 1573 "lib/Haanga/Compiler/Parser.php"
#line 96 "lib/Haanga/Compiler/Parser.y"
    function yy_r7(){ $this->_retvalue = array('operation' => 'base', $this->yystack[$this->yyidx + -1]->minor);     }
#line 1576 "lib/Haanga/Compiler/Parser.php"
#line 97 "lib/Haanga/Compiler/Parser.y"
    function yy_r8(){ $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;     }
#line 1579 "lib/Haanga/Compiler/Parser.php"
#line 98 "lib/Haanga/Compiler/Parser.y"
    function yy_r9(){ $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1582 "lib/Haanga/Compiler/Parser.php"
#line 103 "lib/Haanga/Compiler/Parser.y"
    function yy_r14(){ $this->_retvalue = array('operation' => 'include', $this->yystack[$this->yyidx + -1]->minor);     }
#line 1585 "lib/Haanga/Compiler/Parser.php"
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
#line 1597 "lib/Haanga/Compiler/Parser.php"
#line 121 "lib/Haanga/Compiler/Parser.y"
    function yy_r19(){
    $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -1]->minor, 'list'=>array()); 
    }
#line 1602 "lib/Haanga/Compiler/Parser.php"
#line 124 "lib/Haanga/Compiler/Parser.y"
    function yy_r20(){
    $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -3]->minor, 'as' => $this->yystack[$this->yyidx + -1]->minor, 'list'=>array()); 
    }
#line 1607 "lib/Haanga/Compiler/Parser.php"
#line 127 "lib/Haanga/Compiler/Parser.y"
    function yy_r21(){ 
    $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -2]->minor, 'list' => $this->yystack[$this->yyidx + -1]->minor); 
    }
#line 1612 "lib/Haanga/Compiler/Parser.php"
#line 130 "lib/Haanga/Compiler/Parser.y"
    function yy_r22(){
    $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -4]->minor, 'as' => $this->yystack[$this->yyidx + -1]->minor, 'list' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1617 "lib/Haanga/Compiler/Parser.php"
#line 135 "lib/Haanga/Compiler/Parser.y"
    function yy_r23(){
    if ('end'.$this->yystack[$this->yyidx + -5]->minor != $this->yystack[$this->yyidx + -1]->minor) { 
        $this->error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor); 
    } 
    $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor, 'list' => array());
    }
#line 1625 "lib/Haanga/Compiler/Parser.php"
#line 141 "lib/Haanga/Compiler/Parser.y"
    function yy_r24(){
    if ('end'.$this->yystack[$this->yyidx + -6]->minor != $this->yystack[$this->yyidx + -1]->minor) { 
        $this->error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor); 
    } 
    $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -6]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor, 'list' => $this->yystack[$this->yyidx + -5]->minor);
    }
#line 1633 "lib/Haanga/Compiler/Parser.php"
#line 149 "lib/Haanga/Compiler/Parser.y"
    function yy_r25(){
    if ('endspacefull' != $this->yystack[$this->yyidx + -1]->minor) {
        $this->error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor);
    } 
    $this->_retvalue = array('operation' => 'spacefull', 'body' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1641 "lib/Haanga/Compiler/Parser.php"
#line 157 "lib/Haanga/Compiler/Parser.y"
    function yy_r26(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endwith") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endwith");
    }
    $this->_retvalue = array('operation' => 'alias', 'var' => $this->yystack[$this->yyidx + -7]->minor, 'as' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1649 "lib/Haanga/Compiler/Parser.php"
#line 166 "lib/Haanga/Compiler/Parser.y"
    function yy_r28(){
    if (!is_file($this->yystack[$this->yyidx + 0]->minor) || !Haanga_Compiler::getOption('enable_load')) {
        $this->error($this->yystack[$this->yyidx + 0]->minor." is not a valid file"); 
    } 
    require_once $this->yystack[$this->yyidx + 0]->minor;
    }
#line 1657 "lib/Haanga/Compiler/Parser.php"
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
#line 1669 "lib/Haanga/Compiler/Parser.php"
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
#line 1680 "lib/Haanga/Compiler/Parser.php"
#line 192 "lib/Haanga/Compiler/Parser.y"
    function yy_r31(){ 
    if ($this->yystack[$this->yyidx + -1]->minor != "endfor") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endfor");
    }
    $this->_retvalue = $this->yystack[$this->yyidx + -4]->minor;
    $this->_retvalue['body'] = $this->yystack[$this->yyidx + -3]->minor;
    }
#line 1689 "lib/Haanga/Compiler/Parser.php"
#line 200 "lib/Haanga/Compiler/Parser.y"
    function yy_r32(){ 
    if ($this->yystack[$this->yyidx + -1]->minor != "endfor") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endfor");
    }
    $this->_retvalue = $this->yystack[$this->yyidx + -8]->minor;
    $this->_retvalue['body']  = $this->yystack[$this->yyidx + -7]->minor;
    $this->_retvalue['empty'] = $this->yystack[$this->yyidx + -3]->minor;
    }
#line 1699 "lib/Haanga/Compiler/Parser.php"
#line 209 "lib/Haanga/Compiler/Parser.y"
    function yy_r33(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endif") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endif");
    }
    $this->_retvalue = array('operation' => 'if', 'expr' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1707 "lib/Haanga/Compiler/Parser.php"
#line 215 "lib/Haanga/Compiler/Parser.y"
    function yy_r34(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endif") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endif");
    }
    $this->_retvalue = array('operation' => 'if', 'expr' => $this->yystack[$this->yyidx + -9]->minor, 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1715 "lib/Haanga/Compiler/Parser.php"
#line 223 "lib/Haanga/Compiler/Parser.y"
    function yy_r35(){ 
    if ($this->yystack[$this->yyidx + -1]->minor != "endifchanged") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifchanged");
    }
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1723 "lib/Haanga/Compiler/Parser.php"
#line 230 "lib/Haanga/Compiler/Parser.y"
    function yy_r36(){ 
    if ($this->yystack[$this->yyidx + -1]->minor != "endifchanged") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifchanged");
    }
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -3]->minor, 'check' => $this->yystack[$this->yyidx + -5]->minor);
    }
#line 1731 "lib/Haanga/Compiler/Parser.php"
#line 236 "lib/Haanga/Compiler/Parser.y"
    function yy_r37(){ 
    if ($this->yystack[$this->yyidx + -1]->minor != "endifchanged") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifchanged");
    }
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1739 "lib/Haanga/Compiler/Parser.php"
#line 243 "lib/Haanga/Compiler/Parser.y"
    function yy_r38(){ 
    if ($this->yystack[$this->yyidx + -1]->minor != "endifchanged") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifchanged");
    }
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -7]->minor, 'check' => $this->yystack[$this->yyidx + -9]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1747 "lib/Haanga/Compiler/Parser.php"
#line 251 "lib/Haanga/Compiler/Parser.y"
    function yy_r39(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endifequal") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifequal");
    }
    $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '==', 1 => $this->yystack[$this->yyidx + -6]->minor, 2 => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1755 "lib/Haanga/Compiler/Parser.php"
#line 257 "lib/Haanga/Compiler/Parser.y"
    function yy_r40(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endifequal") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifequal");
    }
    $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '==', 1 => $this->yystack[$this->yyidx + -10]->minor, 2 => $this->yystack[$this->yyidx + -9]->minor, 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1763 "lib/Haanga/Compiler/Parser.php"
#line 263 "lib/Haanga/Compiler/Parser.y"
    function yy_r41(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endifnotequal") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifnotequal");
    }
    $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '!=', 1 => $this->yystack[$this->yyidx + -6]->minor, 2 => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1771 "lib/Haanga/Compiler/Parser.php"
#line 269 "lib/Haanga/Compiler/Parser.y"
    function yy_r42(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endifnotequal") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endifnotequal");
    }
    $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '!=', 1 => $this->yystack[$this->yyidx + -10]->minor, 2 => $this->yystack[$this->yyidx + -9]->minor, 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1779 "lib/Haanga/Compiler/Parser.php"
#line 277 "lib/Haanga/Compiler/Parser.y"
    function yy_r43(){ 
    if ($this->yystack[$this->yyidx + -1]->minor != "endblock") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endblock");
    }
    $this->_retvalue = array('operation' => 'block', 'name' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1787 "lib/Haanga/Compiler/Parser.php"
#line 284 "lib/Haanga/Compiler/Parser.y"
    function yy_r44(){
    if ($this->yystack[$this->yyidx + -2]->minor != "endblock") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -2]->minor.", expecting endblock");
    }
    $this->_retvalue = array('operation' => 'block', 'name' => $this->yystack[$this->yyidx + -6]->minor, 'body' => $this->yystack[$this->yyidx + -4]->minor); 
    }
#line 1795 "lib/Haanga/Compiler/Parser.php"
#line 291 "lib/Haanga/Compiler/Parser.y"
    function yy_r45(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endblock") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endblock");
    }
    $this->_retvalue = array('operation' => 'block', 'name' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1803 "lib/Haanga/Compiler/Parser.php"
#line 306 "lib/Haanga/Compiler/Parser.y"
    function yy_r47(){
    if ($this->yystack[$this->yyidx + -1]->minor != "endfilter") {
        $this->Error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor.", expecting endfilter");
    }
    $this->_retvalue = array('operation' => 'filter', 'functions' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1811 "lib/Haanga/Compiler/Parser.php"
#line 314 "lib/Haanga/Compiler/Parser.y"
    function yy_r48(){ $this->_retvalue=array('operation' => 'regroup', 'array' => $this->yystack[$this->yyidx + -4]->minor, 'row' => $this->yystack[$this->yyidx + -2]->minor, 'as' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1814 "lib/Haanga/Compiler/Parser.php"
#line 317 "lib/Haanga/Compiler/Parser.y"
    function yy_r49(){ $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor; $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1817 "lib/Haanga/Compiler/Parser.php"
#line 318 "lib/Haanga/Compiler/Parser.y"
    function yy_r50(){ $this->_retvalue = array($this->yystack[$this->yyidx + 0]->minor);     }
#line 1820 "lib/Haanga/Compiler/Parser.php"
#line 320 "lib/Haanga/Compiler/Parser.y"
    function yy_r51(){ $this->_retvalue = array($this->yystack[$this->yyidx + -2]->minor, 'args'=>array($this->yystack[$this->yyidx + 0]->minor));     }
#line 1823 "lib/Haanga/Compiler/Parser.php"
#line 324 "lib/Haanga/Compiler/Parser.y"
    function yy_r53(){ $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor; $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1826 "lib/Haanga/Compiler/Parser.php"
#line 330 "lib/Haanga/Compiler/Parser.y"
    function yy_r56(){ $this->_retvalue = array('var' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1829 "lib/Haanga/Compiler/Parser.php"
#line 331 "lib/Haanga/Compiler/Parser.y"
    function yy_r57(){ $this->_retvalue = array('number' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1832 "lib/Haanga/Compiler/Parser.php"
#line 332 "lib/Haanga/Compiler/Parser.y"
    function yy_r58(){ $this->_retvalue = trim(@$this->yystack[$this->yyidx + 0]->minor);     }
#line 1835 "lib/Haanga/Compiler/Parser.php"
#line 333 "lib/Haanga/Compiler/Parser.y"
    function yy_r59(){ $this->_retvalue = array('string' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1838 "lib/Haanga/Compiler/Parser.php"
#line 336 "lib/Haanga/Compiler/Parser.y"
    function yy_r60(){ $this->_retvalue = array('var_filter' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1841 "lib/Haanga/Compiler/Parser.php"
#line 346 "lib/Haanga/Compiler/Parser.y"
    function yy_r66(){ $this->_retvalue = array('op_expr' => 'not', $this->yystack[$this->yyidx + 0]->minor);     }
#line 1844 "lib/Haanga/Compiler/Parser.php"
#line 347 "lib/Haanga/Compiler/Parser.y"
    function yy_r67(){ $this->_retvalue = array('op_expr' => @$this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);     }
#line 1847 "lib/Haanga/Compiler/Parser.php"
#line 350 "lib/Haanga/Compiler/Parser.y"
    function yy_r70(){ $this->_retvalue = array('op_expr' => trim(@$this->yystack[$this->yyidx + -1]->minor), $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);     }
#line 1850 "lib/Haanga/Compiler/Parser.php"
#line 352 "lib/Haanga/Compiler/Parser.y"
    function yy_r72(){ $this->_retvalue = array('op_expr' => 'expr', $this->yystack[$this->yyidx + -1]->minor);     }
#line 1853 "lib/Haanga/Compiler/Parser.php"
#line 356 "lib/Haanga/Compiler/Parser.y"
    function yy_r74(){ if (!is_array($this->yystack[$this->yyidx + -2]->minor)) { $this->_retvalue = array($this->yystack[$this->yyidx + -2]->minor); } else { $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor; }  $this->_retvalue[]=array('object' => $this->yystack[$this->yyidx + 0]->minor);    }
#line 1856 "lib/Haanga/Compiler/Parser.php"
#line 357 "lib/Haanga/Compiler/Parser.y"
    function yy_r75(){ if (!is_array($this->yystack[$this->yyidx + -2]->minor)) { $this->_retvalue = array($this->yystack[$this->yyidx + -2]->minor); } else { $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor; } $this->_retvalue[] = ($this->compiler->var_is_object($this->_retvalue)) ? array('object' => $this->yystack[$this->yyidx + 0]->minor) : $this->yystack[$this->yyidx + 0]->minor;    }
#line 1859 "lib/Haanga/Compiler/Parser.php"
#line 358 "lib/Haanga/Compiler/Parser.y"
    function yy_r76(){ if (!is_array($this->yystack[$this->yyidx + -3]->minor)) { $this->_retvalue = array($this->yystack[$this->yyidx + -3]->minor); } else { $this->_retvalue = $this->yystack[$this->yyidx + -3]->minor; }  $this->_retvalue[]=$this->yystack[$this->yyidx + -1]->minor;    }
#line 1862 "lib/Haanga/Compiler/Parser.php"

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
#line 1982 "lib/Haanga/Compiler/Parser.php"
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

#line 2003 "lib/Haanga/Compiler/Parser.php"
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