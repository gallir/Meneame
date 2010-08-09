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
    const T_INTL                         = 60;
    const T_RPARENT                      = 61;
    const T_STRING_SINGLE_INIT           = 62;
    const T_STRING_SINGLE_END            = 63;
    const T_STRING_DOUBLE_INIT           = 64;
    const T_STRING_DOUBLE_END            = 65;
    const T_STRING_CONTENT               = 66;
    const T_LPARENT                      = 67;
    const T_OBJ                          = 68;
    const T_ALPHA                        = 69;
    const T_DOT                          = 70;
    const T_BRACKETS_OPEN                = 71;
    const T_BRACKETS_CLOSE               = 72;
    const YY_NO_ACTION = 329;
    const YY_ACCEPT_ACTION = 328;
    const YY_ERROR_ACTION = 327;

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
    const YY_SZ_ACTTAB = 1014;
static public $yy_action = array(
 /*     0 */    42,  199,   38,  131,  142,  178,   74,   34,   84,  167,
 /*    10 */    83,   81,  154,   77,  123,   76,   72,  223,  158,  157,
 /*    20 */    26,  209,  150,   35,  115,   31,   50,   33,  198,   54,
 /*    30 */   198,  210,   45,  115,   43,   42,  181,   38,  131,   30,
 /*    40 */   217,  240,   34,   98,  167,  100,   81,  154,   77,  224,
 /*    50 */    76,   72,  143,  244,   41,   26,   48,  134,   35,   15,
 /*    60 */    31,  136,   33,  137,   54,  168,   40,   45,  193,   43,
 /*    70 */    42,  188,   38,  131,  196,  230,  145,   34,   44,  167,
 /*    80 */    48,   81,  154,   77,   48,   76,   72,   55,  104,  182,
 /*    90 */    26,  179,  148,   35,  138,   31,   85,   33,  137,   54,
 /*   100 */   168,   40,   45,  185,   43,   42,  207,   38,  131,   28,
 /*   110 */    28,   28,   34,  127,  167,  192,   81,  154,   77,  139,
 /*   120 */    76,   72,  164,  164,  115,   26,  173,  152,   35,    3,
 /*   130 */    31,  217,   33,  137,   54,  168,   40,   45,  177,   43,
 /*   140 */    42,  238,   38,  131,   90,  230,  145,   34,   44,  167,
 /*   150 */    52,   81,  154,   77,  205,   76,   72,  243,  102,  219,
 /*   160 */    26,  237,  140,   35,    2,   31,  211,   33,  144,   54,
 /*   170 */   195,  196,   45,  183,   43,   42,  123,   38,  131,  223,
 /*   180 */   230,  145,   34,   44,  167,  233,   81,  154,   77,  236,
 /*   190 */    76,   72,  194,  207,  198,   26,  198,  170,   35,  169,
 /*   200 */    31,  162,   33,  137,   54,  168,   40,   45,  239,   43,
 /*   210 */    42,  123,   38,  131,  223,  222,   46,   34,  133,  167,
 /*   220 */    97,   81,  154,   77,  242,   76,   72,  103,  231,  115,
 /*   230 */    26,   86,  150,   35,  193,   31,  217,   33,  123,   54,
 /*   240 */   129,  223,   45,  115,   43,   42,  181,   38,  131,  146,
 /*   250 */   217,  130,   34,  132,  167,  215,   81,  154,   77,  220,
 /*   260 */    76,   72,  115,  214,  115,   26,  172,  150,   35,  217,
 /*   270 */    31,  217,   33,  137,   54,  168,   40,   45,  115,   43,
 /*   280 */    42,  181,   38,  131,   32,  217,  228,   34,   87,  167,
 /*   290 */   159,   81,  154,   77,  234,   76,   72,   89,  216,  225,
 /*   300 */    26,  218,   10,   35,  198,   31,  198,   33,  137,   54,
 /*   310 */   168,   40,   45,   42,   43,   38,  131,  203,  230,  145,
 /*   320 */    34,   44,  167,  160,   81,  154,   77,   75,   76,   72,
 /*   330 */    92,   48,  128,   26,  101,   11,   35,  202,   31,  198,
 /*   340 */    33,  198,   54,  115,  193,   45,   42,   43,   38,  131,
 /*   350 */   217,  230,  145,   34,   44,  167,  241,   81,  154,   77,
 /*   360 */    94,   76,   72,  187,  166,  137,   26,  168,   40,   35,
 /*   370 */    74,   31,   84,   33,   83,   54,   82,   48,   45,  193,
 /*   380 */    43,   42,  111,   38,  131,  229,   14,  227,   34,   95,
 /*   390 */   167,   48,   81,  154,   77,  123,   76,   72,  223,  328,
 /*   400 */    57,   26,  230,  145,   35,   44,   31,  163,   33,  198,
 /*   410 */    54,  198,  186,   45,  135,   43,   42,  123,   38,  131,
 /*   420 */   223,  226,  165,   34,  235,  167,   58,   81,  154,   77,
 /*   430 */    65,   76,   72,  221,  123,   36,   26,  223,  114,   35,
 /*   440 */    74,   31,   84,   33,   83,   54,   60,  126,   45,  193,
 /*   450 */    43,   42,   66,   38,  131,  125,   21,   49,   34,   69,
 /*   460 */   167,  155,   81,  154,   77,   62,   76,   72,  112,  182,
 /*   470 */    70,   26,  230,  145,   35,   44,   31,   68,   33,  137,
 /*   480 */    54,  168,   40,   45,   42,   43,   38,  131,   59,   67,
 /*   490 */   113,   34,   61,  167,  117,   81,  154,   77,  153,   76,
 /*   500 */    72,   51,  232,  182,   26,   53,  150,   35,  212,   31,
 /*   510 */   116,   33,  141,   54,  124,   63,   45,  115,   43,   42,
 /*   520 */   181,   38,  131,  149,  217,   71,   34,  120,  167,   64,
 /*   530 */    81,  154,   77,  119,   76,   72,  118,  121,  182,   26,
 /*   540 */   182,  182,   35,  171,   31,  182,   33,  137,   54,  168,
 /*   550 */    40,   45,  182,   43,   42,  182,   38,  131,  182,   18,
 /*   560 */   182,   34,  182,  167,  182,   81,  154,   77,  182,   76,
 /*   570 */    72,  182,   88,  182,   26,  230,  145,   35,   44,   31,
 /*   580 */   182,   33,  182,   54,   56,  182,   45,  182,   43,   42,
 /*   590 */   182,   38,  131,  182,   16,  182,   34,  182,  167,  182,
 /*   600 */    81,  154,   77,  182,   76,   72,  182,  182,  182,   26,
 /*   610 */   230,  145,   35,   44,   31,  151,   33,  137,   54,  168,
 /*   620 */    40,   45,  182,   43,   42,  182,   38,  131,  182,   13,
 /*   630 */   182,   34,  182,  167,  182,   81,  154,   77,  156,   76,
 /*   640 */    72,  182,  182,  182,   26,  230,  145,   35,   44,   31,
 /*   650 */   182,   33,  182,   54,  235,  182,   45,  182,   43,   42,
 /*   660 */   182,   38,  131,  182,  123,   37,   34,  223,  167,  182,
 /*   670 */    81,  154,   77,  182,   76,   72,  182,  182,  182,   26,
 /*   680 */   182,  182,   35,  147,   31,  182,   33,  182,   54,  182,
 /*   690 */   182,   45,  182,   43,   42,  182,   38,  131,  182,   20,
 /*   700 */   182,   34,  182,  167,  182,   81,  154,   77,   17,   76,
 /*   710 */    72,  182,  182,  182,   26,  230,  145,   35,   44,   31,
 /*   720 */   182,   33,   79,   54,  230,  145,   45,   44,   43,  182,
 /*   730 */    24,   23,   25,   25,   25,   25,   25,   25,   25,   22,
 /*   740 */    22,   28,   28,   28,   24,   23,   25,   25,   25,   25,
 /*   750 */    25,   25,   25,   22,   22,   28,   28,   28,  182,  182,
 /*   760 */   137,  182,  168,   40,   96,  182,  182,  182,   24,   23,
 /*   770 */    25,   25,   25,   25,   25,   25,   25,   22,   22,   28,
 /*   780 */    28,   28,  182,  182,  182,  182,  182,  182,  191,  182,
 /*   790 */    47,  182,  182,   23,   25,   25,   25,   25,   25,   25,
 /*   800 */    25,   22,   22,   28,   28,   28,  184,  182,  182,  161,
 /*   810 */   206,  204,  201,  190,  189,  176,  174,  175,   73,  182,
 /*   820 */   213,  182,   99,   25,   25,   25,   25,   25,   25,   25,
 /*   830 */    22,   22,   28,   28,   28,  208,  182,   27,  200,  182,
 /*   840 */     9,  198,   78,  198,  198,   80,  198,  137,  182,  168,
 /*   850 */    40,   39,   93,  182,  182,   91,  230,  145,  198,   44,
 /*   860 */   198,  198,  182,  198,  198,  221,  198,  182,  221,  182,
 /*   870 */   182,   39,   74,  182,   84,   74,   83,   84,  182,   83,
 /*   880 */   182,  193,  221,  182,  193,  221,  182,  182,  187,   74,
 /*   890 */     7,   84,   74,   83,   84,   74,   83,   84,  193,   83,
 /*   900 */   182,  193,   29,  150,  193,  182,  230,  145,  182,   44,
 /*   910 */   182,  150,  182,  182,  115,  182,   12,  181,  182,  105,
 /*   920 */   180,  217,  115,  150,  182,  181,  182,  197,  180,  217,
 /*   930 */   182,  150,  230,  145,  115,   44,    4,  181,  182,  106,
 /*   940 */   180,  217,  115,  150,  182,  181,  182,  110,  180,  217,
 /*   950 */   182,  150,  230,  145,  115,   44,    1,  181,  182,  122,
 /*   960 */   180,  217,  115,  150,  182,  181,  182,  109,  180,  217,
 /*   970 */   182,  150,  230,  145,  115,   44,    5,  181,  182,  108,
 /*   980 */   180,  217,  115,   19,  182,  181,  182,  107,  180,  217,
 /*   990 */     6,  182,  230,  145,    8,   44,  182,  182,  182,  230,
 /*  1000 */   145,  182,   44,  182,  182,  182,  230,  145,  182,   44,
 /*  1010 */   230,  145,  182,   44,
    );
    static public $yy_lookahead = array(
 /*     0 */    22,   69,   24,   25,   79,   23,   60,   29,   62,   31,
 /*    10 */    64,   33,   34,   35,   89,   37,   38,   92,   40,   41,
 /*    20 */    42,   23,   78,   45,   89,   47,   75,   49,   29,   51,
 /*    30 */    31,   96,   54,   89,   56,   22,   92,   24,   25,   95,
 /*    40 */    96,   23,   29,   23,   31,   23,   33,   34,   35,   23,
 /*    50 */    37,   38,   53,   23,   59,   42,   58,   44,   45,    1,
 /*    60 */    47,   48,   49,   68,   51,   70,   71,   54,   69,   56,
 /*    70 */    22,   63,   24,   25,   66,   17,   18,   29,   20,   31,
 /*    80 */    58,   33,   34,   35,   58,   37,   38,   75,   23,   23,
 /*    90 */    42,   23,   44,   45,   46,   47,   23,   49,   68,   51,
 /*   100 */    70,   71,   54,   63,   56,   22,   66,   24,   25,   14,
 /*   110 */    15,   16,   29,   78,   31,   72,   33,   34,   35,   53,
 /*   120 */    37,   38,   26,   27,   89,   42,   43,   44,   45,    1,
 /*   130 */    47,   96,   49,   68,   51,   70,   71,   54,   23,   56,
 /*   140 */    22,   69,   24,   25,   23,   17,   18,   29,   20,   31,
 /*   150 */    75,   33,   34,   35,   23,   37,   38,   23,   23,   23,
 /*   160 */    42,   23,   44,   45,    1,   47,   79,   49,   50,   51,
 /*   170 */    65,   66,   54,   23,   56,   22,   89,   24,   25,   92,
 /*   180 */    17,   18,   29,   20,   31,   23,   33,   34,   35,   23,
 /*   190 */    37,   38,   65,   66,   29,   42,   31,   44,   45,   46,
 /*   200 */    47,   79,   49,   68,   51,   70,   71,   54,   23,   56,
 /*   210 */    22,   89,   24,   25,   92,   23,   11,   29,   78,   31,
 /*   220 */    23,   33,   34,   35,   23,   37,   38,   23,   79,   89,
 /*   230 */    42,   23,   78,   45,   69,   47,   96,   49,   89,   51,
 /*   240 */    52,   92,   54,   89,   56,   22,   92,   24,   25,   95,
 /*   250 */    96,   78,   29,   78,   31,   23,   33,   34,   35,   23,
 /*   260 */    37,   38,   89,   23,   89,   42,   43,   78,   45,   96,
 /*   270 */    47,   96,   49,   68,   51,   70,   71,   54,   89,   56,
 /*   280 */    22,   92,   24,   25,   95,   96,   19,   29,   23,   31,
 /*   290 */    32,   33,   34,   35,   21,   37,   38,   23,   23,   23,
 /*   300 */    42,   23,    1,   45,   29,   47,   31,   49,   68,   51,
 /*   310 */    70,   71,   54,   22,   56,   24,   25,   23,   17,   18,
 /*   320 */    29,   20,   31,   32,   33,   34,   35,   30,   37,   38,
 /*   330 */    23,   58,   78,   42,   23,    1,   45,   76,   47,   29,
 /*   340 */    49,   31,   51,   89,   69,   54,   22,   56,   24,   25,
 /*   350 */    96,   17,   18,   29,   20,   31,   61,   33,   34,   35,
 /*   360 */    23,   37,   38,   53,   40,   68,   42,   70,   71,   45,
 /*   370 */    60,   47,   62,   49,   64,   51,   57,   58,   54,   69,
 /*   380 */    56,   22,   89,   24,   25,   79,    1,   23,   29,   23,
 /*   390 */    31,   58,   33,   34,   35,   89,   37,   38,   92,   74,
 /*   400 */    75,   42,   17,   18,   45,   20,   47,   79,   49,   29,
 /*   410 */    51,   31,   23,   54,   55,   56,   22,   89,   24,   25,
 /*   420 */    92,   23,   28,   29,   79,   31,   75,   33,   34,   35,
 /*   430 */    75,   37,   38,   53,   89,   90,   42,   92,   89,   45,
 /*   440 */    60,   47,   62,   49,   64,   51,   75,   97,   54,   69,
 /*   450 */    56,   22,   75,   24,   25,   97,    1,   75,   29,   75,
 /*   460 */    31,   32,   33,   34,   35,   75,   37,   38,   89,   98,
 /*   470 */    75,   42,   17,   18,   45,   20,   47,   75,   49,   68,
 /*   480 */    51,   70,   71,   54,   22,   56,   24,   25,   75,   75,
 /*   490 */    89,   29,   75,   31,   89,   33,   34,   35,   92,   37,
 /*   500 */    38,   75,   23,   98,   42,   75,   78,   45,   92,   47,
 /*   510 */    89,   49,   50,   51,   89,   75,   54,   89,   56,   22,
 /*   520 */    92,   24,   25,   95,   96,   75,   29,   89,   31,   75,
 /*   530 */    33,   34,   35,   89,   37,   38,   89,   89,   98,   42,
 /*   540 */    98,   98,   45,   46,   47,   98,   49,   68,   51,   70,
 /*   550 */    71,   54,   98,   56,   22,   98,   24,   25,   98,    1,
 /*   560 */    98,   29,   98,   31,   98,   33,   34,   35,   98,   37,
 /*   570 */    38,   98,   23,   98,   42,   17,   18,   45,   20,   47,
 /*   580 */    98,   49,   98,   51,   52,   98,   54,   98,   56,   22,
 /*   590 */    98,   24,   25,   98,    1,   98,   29,   98,   31,   98,
 /*   600 */    33,   34,   35,   98,   37,   38,   98,   98,   98,   42,
 /*   610 */    17,   18,   45,   20,   47,   48,   49,   68,   51,   70,
 /*   620 */    71,   54,   98,   56,   22,   98,   24,   25,   98,    1,
 /*   630 */    98,   29,   98,   31,   98,   33,   34,   35,   36,   37,
 /*   640 */    38,   98,   98,   98,   42,   17,   18,   45,   20,   47,
 /*   650 */    98,   49,   98,   51,   79,   98,   54,   98,   56,   22,
 /*   660 */    98,   24,   25,   98,   89,   90,   29,   92,   31,   98,
 /*   670 */    33,   34,   35,   98,   37,   38,   98,   98,   98,   42,
 /*   680 */    98,   98,   45,   46,   47,   98,   49,   98,   51,   98,
 /*   690 */    98,   54,   98,   56,   22,   98,   24,   25,   98,    1,
 /*   700 */    98,   29,   98,   31,   98,   33,   34,   35,    1,   37,
 /*   710 */    38,   98,   98,   98,   42,   17,   18,   45,   20,   47,
 /*   720 */    98,   49,   30,   51,   17,   18,   54,   20,   56,   98,
 /*   730 */     3,    4,    5,    6,    7,    8,    9,   10,   11,   12,
 /*   740 */    13,   14,   15,   16,    3,    4,    5,    6,    7,    8,
 /*   750 */     9,   10,   11,   12,   13,   14,   15,   16,   98,   98,
 /*   760 */    68,   98,   70,   71,   23,   98,   98,   98,    3,    4,
 /*   770 */     5,    6,    7,    8,    9,   10,   11,   12,   13,   14,
 /*   780 */    15,   16,   98,   98,   98,   98,   98,   98,   61,   98,
 /*   790 */    11,   98,   98,    4,    5,    6,    7,    8,    9,   10,
 /*   800 */    11,   12,   13,   14,   15,   16,   77,   98,   98,   80,
 /*   810 */    81,   82,   83,   84,   85,   86,   87,   88,   39,   98,
 /*   820 */    91,   98,   93,    5,    6,    7,    8,    9,   10,   11,
 /*   830 */    12,   13,   14,   15,   16,   23,   98,    2,   23,   98,
 /*   840 */     1,   29,   30,   31,   29,   30,   31,   68,   98,   70,
 /*   850 */    71,   39,   23,   98,   98,   23,   17,   18,   29,   20,
 /*   860 */    31,   29,   98,   31,   29,   53,   31,   98,   53,   98,
 /*   870 */    98,   39,   60,   98,   62,   60,   64,   62,   98,   64,
 /*   880 */    98,   69,   53,   98,   69,   53,   98,   98,   53,   60,
 /*   890 */     1,   62,   60,   64,   62,   60,   64,   62,   69,   64,
 /*   900 */    98,   69,   67,   78,   69,   98,   17,   18,   98,   20,
 /*   910 */    98,   78,   98,   98,   89,   98,    1,   92,   98,   94,
 /*   920 */    95,   96,   89,   78,   98,   92,   98,   94,   95,   96,
 /*   930 */    98,   78,   17,   18,   89,   20,    1,   92,   98,   94,
 /*   940 */    95,   96,   89,   78,   98,   92,   98,   94,   95,   96,
 /*   950 */    98,   78,   17,   18,   89,   20,    1,   92,   98,   94,
 /*   960 */    95,   96,   89,   78,   98,   92,   98,   94,   95,   96,
 /*   970 */    98,   78,   17,   18,   89,   20,    1,   92,   98,   94,
 /*   980 */    95,   96,   89,    1,   98,   92,   98,   94,   95,   96,
 /*   990 */     1,   98,   17,   18,    1,   20,   98,   98,   98,   17,
 /*  1000 */    18,   98,   20,   98,   98,   98,   17,   18,   98,   20,
 /*  1010 */    17,   18,   98,   20,
);
    const YY_SHIFT_USE_DFLT = -69;
    const YY_SHIFT_MAX = 173;
    static public $yy_shift_ofst = array(
 /*     0 */   -69,  153,   83,  -22,  118,   13,   48,  637,  602,  429,
 /*    10 */   291,  258,  223,  188,  359,  462,  567,  394,  497,  532,
 /*    20 */   324,  672,  835,  835,  835,  835,  835,  835,  835,  835,
 /*    30 */   310,  310,  310,  310,  815,  829,  812,  832,  380,  380,
 /*    40 */   380,  380,  380,  165,  165,  165,  165,  165,  165,  982,
 /*    50 */   993,  839,  334,  385,   -1,  301,  275,  455,  889,  915,
 /*    60 */   955,  593,  975,  558,  163,  989,   58,  128,  628,  698,
 /*    70 */   707,  935,  165,  165,  -54,  165,  -54,  165,  165,  165,
 /*    80 */   165,  165,  165,  105,    8,  -69,  -69,  -69,  -69,  -69,
 /*    90 */   -69,  -69,  -69,  -69,  -69,  -69,  -69,  -69,  -69,  -69,
 /*   100 */   -69,  -69,  -69,  -69,  -69,  727,  741,  765,  789,  818,
 /*   110 */   818,  779,  549,  479,  692,   -5,   65,  297,   30,  135,
 /*   120 */   205,  240,   95,  411,  411,   40,  127,  319,   26,   66,
 /*   130 */    -2,   96,   22,  273,   20,   18,   68,  -68,  -18,  389,
 /*   140 */    73,  278,  276,  274,  236,  267,  265,  294,  307,  366,
 /*   150 */   333,  364,  337,  295,  311,  398,  232,  208,  136,  138,
 /*   160 */   134,  131,   43,  115,  121,  150,  162,  204,   72,  201,
 /*   170 */   197,  166,  185,  192,
);
    const YY_REDUCE_USE_DFLT = -76;
    const YY_REDUCE_MAX = 104;
    static public $yy_reduce_ofst = array(
 /*     0 */   325,  729,  729,  729,  729,  729,  729,  729,  729,  729,
 /*    10 */   729,  729,  729,  729,  729,  729,  729,  729,  729,  729,
 /*    20 */   729,  729,  865,  873,  885,  853,  845,  893,  833,  825,
 /*    30 */   428,  189,  154,  -56,  345,  575,  149,  149,  328,  306,
 /*    40 */   122,   87,  -75,   35,  140,  175,  254,  173,  -65,  261,
 /*    50 */   261,  261,  261,  261,  379,  261,  448,  261,  261,  261,
 /*    60 */   261,  261,  261,  261,  261,  261,  261,  261,  261,  261,
 /*    70 */   261,  261,  293,  438,  406,  421,  416,  405,  401,  425,
 /*    80 */   447,  444,  349,  350,  358,  377,  384,  390,  382,  402,
 /*    90 */   395,  355,  351,  371,  413,  450,  454,  440,  417,  414,
 /*   100 */   430,  426,   75,   12,  -49,
);
    static public $yyExpectedTokens = array(
        /* 0 */ array(),
        /* 1 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 44, 45, 46, 47, 49, 51, 54, 56, ),
        /* 2 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 43, 44, 45, 47, 49, 51, 54, 56, ),
        /* 3 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 40, 41, 42, 45, 47, 49, 51, 54, 56, ),
        /* 4 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 44, 45, 47, 49, 50, 51, 54, 56, ),
        /* 5 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 44, 45, 47, 48, 49, 51, 54, 56, ),
        /* 6 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 44, 45, 46, 47, 49, 51, 54, 56, ),
        /* 7 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 46, 47, 49, 51, 54, 56, ),
        /* 8 */ array(22, 24, 25, 29, 31, 33, 34, 35, 36, 37, 38, 42, 45, 47, 49, 51, 54, 56, ),
        /* 9 */ array(22, 24, 25, 29, 31, 32, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 54, 56, ),
        /* 10 */ array(22, 24, 25, 29, 31, 32, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 54, 56, ),
        /* 11 */ array(22, 24, 25, 29, 31, 32, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 54, 56, ),
        /* 12 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 43, 45, 47, 49, 51, 54, 56, ),
        /* 13 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 52, 54, 56, ),
        /* 14 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 54, 55, 56, ),
        /* 15 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 47, 49, 50, 51, 54, 56, ),
        /* 16 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 47, 48, 49, 51, 54, 56, ),
        /* 17 */ array(22, 24, 25, 28, 29, 31, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 54, 56, ),
        /* 18 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 46, 47, 49, 51, 54, 56, ),
        /* 19 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 52, 54, 56, ),
        /* 20 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 40, 42, 45, 47, 49, 51, 54, 56, ),
        /* 21 */ array(22, 24, 25, 29, 31, 33, 34, 35, 37, 38, 42, 45, 47, 49, 51, 54, 56, ),
        /* 22 */ array(2, 29, 31, 53, 60, 62, 64, 67, 69, ),
        /* 23 */ array(2, 29, 31, 53, 60, 62, 64, 67, 69, ),
        /* 24 */ array(2, 29, 31, 53, 60, 62, 64, 67, 69, ),
        /* 25 */ array(2, 29, 31, 53, 60, 62, 64, 67, 69, ),
        /* 26 */ array(2, 29, 31, 53, 60, 62, 64, 67, 69, ),
        /* 27 */ array(2, 29, 31, 53, 60, 62, 64, 67, 69, ),
        /* 28 */ array(2, 29, 31, 53, 60, 62, 64, 67, 69, ),
        /* 29 */ array(2, 29, 31, 53, 60, 62, 64, 67, 69, ),
        /* 30 */ array(29, 31, 53, 60, 62, 64, 69, ),
        /* 31 */ array(29, 31, 53, 60, 62, 64, 69, ),
        /* 32 */ array(29, 31, 53, 60, 62, 64, 69, ),
        /* 33 */ array(29, 31, 53, 60, 62, 64, 69, ),
        /* 34 */ array(23, 29, 30, 31, 53, 60, 62, 64, 69, ),
        /* 35 */ array(23, 29, 31, 53, 60, 62, 64, 69, ),
        /* 36 */ array(23, 29, 30, 31, 39, 53, 60, 62, 64, 69, ),
        /* 37 */ array(23, 29, 31, 39, 53, 60, 62, 64, 69, ),
        /* 38 */ array(29, 31, 53, 60, 62, 64, 69, ),
        /* 39 */ array(29, 31, 53, 60, 62, 64, 69, ),
        /* 40 */ array(29, 31, 53, 60, 62, 64, 69, ),
        /* 41 */ array(29, 31, 53, 60, 62, 64, 69, ),
        /* 42 */ array(29, 31, 53, 60, 62, 64, 69, ),
        /* 43 */ array(29, 31, 69, ),
        /* 44 */ array(29, 31, 69, ),
        /* 45 */ array(29, 31, 69, ),
        /* 46 */ array(29, 31, 69, ),
        /* 47 */ array(29, 31, 69, ),
        /* 48 */ array(29, 31, 69, ),
        /* 49 */ array(1, 17, 18, 20, ),
        /* 50 */ array(1, 17, 18, 20, ),
        /* 51 */ array(1, 17, 18, 20, ),
        /* 52 */ array(1, 17, 18, 20, ),
        /* 53 */ array(1, 17, 18, 20, ),
        /* 54 */ array(29, 31, 53, 69, ),
        /* 55 */ array(1, 17, 18, 20, ),
        /* 56 */ array(23, 29, 31, 69, ),
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
        /* 70 */ array(1, 17, 18, 20, ),
        /* 71 */ array(1, 17, 18, 20, ),
        /* 72 */ array(29, 31, 69, ),
        /* 73 */ array(29, 31, 69, ),
        /* 74 */ array(60, 62, 64, ),
        /* 75 */ array(29, 31, 69, ),
        /* 76 */ array(60, 62, 64, ),
        /* 77 */ array(29, 31, 69, ),
        /* 78 */ array(29, 31, 69, ),
        /* 79 */ array(29, 31, 69, ),
        /* 80 */ array(29, 31, 69, ),
        /* 81 */ array(29, 31, 69, ),
        /* 82 */ array(29, 31, 69, ),
        /* 83 */ array(65, 66, ),
        /* 84 */ array(63, 66, ),
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
        /* 105 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 61, ),
        /* 106 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 23, ),
        /* 107 */ array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 108 */ array(4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 109 */ array(5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 110 */ array(5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ),
        /* 111 */ array(11, 39, 68, 70, 71, ),
        /* 112 */ array(23, 68, 70, 71, ),
        /* 113 */ array(23, 68, 70, 71, ),
        /* 114 */ array(30, 68, 70, 71, ),
        /* 115 */ array(59, 68, 70, 71, ),
        /* 116 */ array(23, 68, 70, 71, ),
        /* 117 */ array(30, 68, 70, 71, ),
        /* 118 */ array(23, 68, 70, 71, ),
        /* 119 */ array(23, 68, 70, 71, ),
        /* 120 */ array(11, 68, 70, 71, ),
        /* 121 */ array(23, 68, 70, 71, ),
        /* 122 */ array(14, 15, 16, ),
        /* 123 */ array(68, 70, 71, ),
        /* 124 */ array(68, 70, 71, ),
        /* 125 */ array(63, 66, ),
        /* 126 */ array(65, 66, ),
        /* 127 */ array(57, 58, ),
        /* 128 */ array(23, 58, ),
        /* 129 */ array(23, 53, ),
        /* 130 */ array(23, 58, ),
        /* 131 */ array(26, 27, ),
        /* 132 */ array(23, 58, ),
        /* 133 */ array(21, 58, ),
        /* 134 */ array(23, ),
        /* 135 */ array(23, ),
        /* 136 */ array(23, ),
        /* 137 */ array(69, ),
        /* 138 */ array(23, ),
        /* 139 */ array(23, ),
        /* 140 */ array(23, ),
        /* 141 */ array(23, ),
        /* 142 */ array(23, ),
        /* 143 */ array(23, ),
        /* 144 */ array(23, ),
        /* 145 */ array(19, ),
        /* 146 */ array(23, ),
        /* 147 */ array(23, ),
        /* 148 */ array(23, ),
        /* 149 */ array(23, ),
        /* 150 */ array(58, ),
        /* 151 */ array(23, ),
        /* 152 */ array(23, ),
        /* 153 */ array(61, ),
        /* 154 */ array(23, ),
        /* 155 */ array(23, ),
        /* 156 */ array(23, ),
        /* 157 */ array(23, ),
        /* 158 */ array(23, ),
        /* 159 */ array(23, ),
        /* 160 */ array(23, ),
        /* 161 */ array(23, ),
        /* 162 */ array(72, ),
        /* 163 */ array(23, ),
        /* 164 */ array(23, ),
        /* 165 */ array(23, ),
        /* 166 */ array(23, ),
        /* 167 */ array(23, ),
        /* 168 */ array(69, ),
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
);
    static public $yy_default = array(
 /*     0 */   247,  327,  327,  327,  327,  327,  327,  327,  327,  327,
 /*    10 */   327,  327,  327,  327,  327,  327,  327,  327,  327,  327,
 /*    20 */   327,  327,  327,  327,  327,  327,  327,  327,  327,  327,
 /*    30 */   327,  327,  327,  327,  327,  327,  327,  327,  327,  327,
 /*    40 */   327,  327,  327,  327,  327,  327,  327,  327,  327,  327,
 /*    50 */   327,  327,  327,  327,  327,  327,  327,  245,  327,  327,
 /*    60 */   327,  327,  327,  327,  327,  327,  327,  327,  327,  327,
 /*    70 */   327,  327,  327,  327,  327,  327,  327,  327,  327,  327,
 /*    80 */   327,  327,  327,  327,  327,  247,  247,  247,  247,  247,
 /*    90 */   247,  247,  247,  247,  247,  247,  247,  247,  247,  247,
 /*   100 */   247,  247,  247,  247,  247,  327,  327,  314,  315,  316,
 /*   110 */   318,  327,  327,  327,  327,  297,  327,  327,  327,  327,
 /*   120 */   327,  327,  317,  301,  293,  327,  327,  327,  327,  327,
 /*   130 */   327,  327,  327,  327,  327,  327,  327,  327,  327,  327,
 /*   140 */   327,  327,  327,  327,  327,  327,  327,  327,  327,  327,
 /*   150 */   304,  327,  327,  327,  327,  327,  327,  327,  327,  327,
 /*   160 */   327,  327,  327,  327,  327,  327,  327,  327,  327,  327,
 /*   170 */   327,  327,  327,  327,  261,  262,  260,  259,  281,  284,
 /*   180 */   321,  306,  290,  263,  248,  310,  291,  305,  308,  258,
 /*   190 */   257,  320,  324,  325,  311,  309,  313,  319,  326,  322,
 /*   200 */   264,  256,  246,  283,  255,  253,  254,  312,  266,  274,
 /*   210 */   294,  296,  273,  272,  289,  271,  288,  295,  287,  276,
 /*   220 */   286,  302,  278,  303,  275,  252,  270,  285,  250,  299,
 /*   230 */   249,  298,  267,  277,  251,  300,  282,  269,  323,  279,
 /*   240 */   292,  307,  280,  268,  265,
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
    const YYNOCODE = 99;
    const YYSTACKDEPTH = 100;
    const YYNSTATE = 245;
    const YYNRULE = 82;
    const YYERRORSYMBOL = 73;
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
  'T_INTL',        'T_RPARENT',     'T_STRING_SINGLE_INIT',  'T_STRING_SINGLE_END',
  'T_STRING_DOUBLE_INIT',  'T_STRING_DOUBLE_END',  'T_STRING_CONTENT',  'T_LPARENT',   
  'T_OBJ',         'T_ALPHA',       'T_DOT',         'T_BRACKETS_OPEN',
  'T_BRACKETS_CLOSE',  'error',         'start',         'body',        
  'code',          'stmts',         'filtered_var',  'var_or_string',
  'stmt',          'for_stmt',      'ifchanged_stmt',  'block_stmt',  
  'filter_stmt',   'if_stmt',       'custom_tag',    'alias',       
  'ifequal',       'varname',       'var_list',      'regroup',     
  'string',        'for_def',       'expr',          'fvar_or_string',
  'varname_args',  's_content',   
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
 /*  61 */ "fvar_or_string ::= string",
 /*  62 */ "string ::= T_INTL string T_RPARENT",
 /*  63 */ "string ::= T_STRING_SINGLE_INIT T_STRING_SINGLE_END",
 /*  64 */ "string ::= T_STRING_DOUBLE_INIT T_STRING_DOUBLE_END",
 /*  65 */ "string ::= T_STRING_SINGLE_INIT s_content T_STRING_SINGLE_END",
 /*  66 */ "string ::= T_STRING_DOUBLE_INIT s_content T_STRING_DOUBLE_END",
 /*  67 */ "s_content ::= s_content T_STRING_CONTENT",
 /*  68 */ "s_content ::= T_STRING_CONTENT",
 /*  69 */ "expr ::= T_NOT expr",
 /*  70 */ "expr ::= expr T_AND expr",
 /*  71 */ "expr ::= expr T_OR expr",
 /*  72 */ "expr ::= expr T_PLUS|T_MINUS expr",
 /*  73 */ "expr ::= expr T_EQ|T_NE|T_GT|T_GE|T_LT|T_LE|T_IN expr",
 /*  74 */ "expr ::= expr T_TIMES|T_DIV|T_MOD expr",
 /*  75 */ "expr ::= T_LPARENT expr T_RPARENT",
 /*  76 */ "expr ::= fvar_or_string",
 /*  77 */ "varname ::= varname T_OBJ T_ALPHA",
 /*  78 */ "varname ::= varname T_DOT T_ALPHA",
 /*  79 */ "varname ::= varname T_BRACKETS_OPEN var_or_string T_BRACKETS_CLOSE",
 /*  80 */ "varname ::= T_ALPHA",
 /*  81 */ "varname ::= T_CUSTOM_TAG|T_CUSTOM_BLOCK",
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
  array( 'lhs' => 74, 'rhs' => 1 ),
  array( 'lhs' => 75, 'rhs' => 2 ),
  array( 'lhs' => 75, 'rhs' => 0 ),
  array( 'lhs' => 76, 'rhs' => 2 ),
  array( 'lhs' => 76, 'rhs' => 1 ),
  array( 'lhs' => 76, 'rhs' => 2 ),
  array( 'lhs' => 76, 'rhs' => 3 ),
  array( 'lhs' => 77, 'rhs' => 3 ),
  array( 'lhs' => 77, 'rhs' => 2 ),
  array( 'lhs' => 77, 'rhs' => 1 ),
  array( 'lhs' => 77, 'rhs' => 1 ),
  array( 'lhs' => 77, 'rhs' => 1 ),
  array( 'lhs' => 77, 'rhs' => 1 ),
  array( 'lhs' => 77, 'rhs' => 1 ),
  array( 'lhs' => 77, 'rhs' => 3 ),
  array( 'lhs' => 77, 'rhs' => 1 ),
  array( 'lhs' => 77, 'rhs' => 1 ),
  array( 'lhs' => 77, 'rhs' => 1 ),
  array( 'lhs' => 77, 'rhs' => 7 ),
  array( 'lhs' => 86, 'rhs' => 2 ),
  array( 'lhs' => 86, 'rhs' => 4 ),
  array( 'lhs' => 86, 'rhs' => 3 ),
  array( 'lhs' => 86, 'rhs' => 5 ),
  array( 'lhs' => 86, 'rhs' => 6 ),
  array( 'lhs' => 86, 'rhs' => 7 ),
  array( 'lhs' => 86, 'rhs' => 6 ),
  array( 'lhs' => 87, 'rhs' => 9 ),
  array( 'lhs' => 80, 'rhs' => 1 ),
  array( 'lhs' => 80, 'rhs' => 2 ),
  array( 'lhs' => 93, 'rhs' => 5 ),
  array( 'lhs' => 93, 'rhs' => 7 ),
  array( 'lhs' => 81, 'rhs' => 5 ),
  array( 'lhs' => 81, 'rhs' => 9 ),
  array( 'lhs' => 85, 'rhs' => 7 ),
  array( 'lhs' => 85, 'rhs' => 11 ),
  array( 'lhs' => 82, 'rhs' => 6 ),
  array( 'lhs' => 82, 'rhs' => 7 ),
  array( 'lhs' => 82, 'rhs' => 10 ),
  array( 'lhs' => 82, 'rhs' => 11 ),
  array( 'lhs' => 88, 'rhs' => 8 ),
  array( 'lhs' => 88, 'rhs' => 12 ),
  array( 'lhs' => 88, 'rhs' => 8 ),
  array( 'lhs' => 88, 'rhs' => 12 ),
  array( 'lhs' => 83, 'rhs' => 7 ),
  array( 'lhs' => 83, 'rhs' => 8 ),
  array( 'lhs' => 83, 'rhs' => 7 ),
  array( 'lhs' => 83, 'rhs' => 8 ),
  array( 'lhs' => 84, 'rhs' => 7 ),
  array( 'lhs' => 91, 'rhs' => 6 ),
  array( 'lhs' => 78, 'rhs' => 3 ),
  array( 'lhs' => 78, 'rhs' => 1 ),
  array( 'lhs' => 96, 'rhs' => 3 ),
  array( 'lhs' => 96, 'rhs' => 1 ),
  array( 'lhs' => 90, 'rhs' => 2 ),
  array( 'lhs' => 90, 'rhs' => 3 ),
  array( 'lhs' => 90, 'rhs' => 1 ),
  array( 'lhs' => 79, 'rhs' => 1 ),
  array( 'lhs' => 79, 'rhs' => 1 ),
  array( 'lhs' => 79, 'rhs' => 1 ),
  array( 'lhs' => 95, 'rhs' => 1 ),
  array( 'lhs' => 95, 'rhs' => 1 ),
  array( 'lhs' => 95, 'rhs' => 1 ),
  array( 'lhs' => 92, 'rhs' => 3 ),
  array( 'lhs' => 92, 'rhs' => 2 ),
  array( 'lhs' => 92, 'rhs' => 2 ),
  array( 'lhs' => 92, 'rhs' => 3 ),
  array( 'lhs' => 92, 'rhs' => 3 ),
  array( 'lhs' => 97, 'rhs' => 2 ),
  array( 'lhs' => 97, 'rhs' => 1 ),
  array( 'lhs' => 94, 'rhs' => 2 ),
  array( 'lhs' => 94, 'rhs' => 3 ),
  array( 'lhs' => 94, 'rhs' => 3 ),
  array( 'lhs' => 94, 'rhs' => 3 ),
  array( 'lhs' => 94, 'rhs' => 3 ),
  array( 'lhs' => 94, 'rhs' => 3 ),
  array( 'lhs' => 94, 'rhs' => 3 ),
  array( 'lhs' => 94, 'rhs' => 1 ),
  array( 'lhs' => 89, 'rhs' => 3 ),
  array( 'lhs' => 89, 'rhs' => 3 ),
  array( 'lhs' => 89, 'rhs' => 4 ),
  array( 'lhs' => 89, 'rhs' => 1 ),
  array( 'lhs' => 89, 'rhs' => 1 ),
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
        62 => 8,
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
        68 => 9,
        76 => 9,
        80 => 9,
        81 => 9,
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
        61 => 58,
        59 => 59,
        63 => 63,
        64 => 63,
        65 => 65,
        66 => 65,
        67 => 67,
        69 => 69,
        70 => 70,
        71 => 70,
        72 => 70,
        74 => 70,
        73 => 73,
        75 => 75,
        77 => 77,
        78 => 78,
        79 => 79,
    );
    /* Beginning here are the reduction cases.  A typical example
    ** follows:
    **  #line <lineno> <grammarfile>
    **   function yy_r0($yymsp){ ... }           // User supplied code
    **  #line <lineno> <thisfile>
    */
#line 79 "lib/Haanga/Compiler/Parser.y"
    function yy_r0(){ $this->body = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1582 "lib/Haanga/Compiler/Parser.php"
#line 81 "lib/Haanga/Compiler/Parser.y"
    function yy_r1(){ $this->_retvalue=$this->yystack[$this->yyidx + -1]->minor; $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1585 "lib/Haanga/Compiler/Parser.php"
#line 82 "lib/Haanga/Compiler/Parser.y"
    function yy_r2(){ $this->_retvalue = array();     }
#line 1588 "lib/Haanga/Compiler/Parser.php"
#line 85 "lib/Haanga/Compiler/Parser.y"
    function yy_r3(){ if (count($this->yystack[$this->yyidx + 0]->minor)) $this->yystack[$this->yyidx + 0]->minor['line'] = $this->lex->getLine();  $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1591 "lib/Haanga/Compiler/Parser.php"
#line 86 "lib/Haanga/Compiler/Parser.y"
    function yy_r4(){ $this->_retvalue = array('operation' => 'html', 'html' => $this->yystack[$this->yyidx + 0]->minor, 'line' => $this->lex->getLine() );     }
#line 1594 "lib/Haanga/Compiler/Parser.php"
#line 87 "lib/Haanga/Compiler/Parser.y"
    function yy_r5(){ $this->yystack[$this->yyidx + 0]->minor=rtrim($this->yystack[$this->yyidx + 0]->minor); $this->_retvalue = array('operation' => 'comment', 'comment' => substr($this->yystack[$this->yyidx + 0]->minor, 0, strlen($this->yystack[$this->yyidx + 0]->minor)-2));     }
#line 1597 "lib/Haanga/Compiler/Parser.php"
#line 88 "lib/Haanga/Compiler/Parser.y"
    function yy_r6(){ $this->_retvalue = array('operation' => 'print_var', 'variable' => $this->yystack[$this->yyidx + -1]->minor, 'line' => $this->lex->getLine() );     }
#line 1600 "lib/Haanga/Compiler/Parser.php"
#line 90 "lib/Haanga/Compiler/Parser.y"
    function yy_r7(){ $this->_retvalue = array('operation' => 'base', $this->yystack[$this->yyidx + -1]->minor);     }
#line 1603 "lib/Haanga/Compiler/Parser.php"
#line 91 "lib/Haanga/Compiler/Parser.y"
    function yy_r8(){ $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;     }
#line 1606 "lib/Haanga/Compiler/Parser.php"
#line 92 "lib/Haanga/Compiler/Parser.y"
    function yy_r9(){ $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1609 "lib/Haanga/Compiler/Parser.php"
#line 97 "lib/Haanga/Compiler/Parser.y"
    function yy_r14(){ $this->_retvalue = array('operation' => 'include', $this->yystack[$this->yyidx + -1]->minor);     }
#line 1612 "lib/Haanga/Compiler/Parser.php"
#line 101 "lib/Haanga/Compiler/Parser.y"
    function yy_r18(){ $this->_retvalue = array('operation' => 'autoescape', 'value' => strtolower(@$this->yystack[$this->yyidx + -5]->minor), 'body' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1615 "lib/Haanga/Compiler/Parser.php"
#line 106 "lib/Haanga/Compiler/Parser.y"
    function yy_r19(){ $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -1]->minor, 'list'=>array());     }
#line 1618 "lib/Haanga/Compiler/Parser.php"
#line 107 "lib/Haanga/Compiler/Parser.y"
    function yy_r20(){ $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -3]->minor, 'as' => $this->yystack[$this->yyidx + -1]->minor, 'list'=>array());     }
#line 1621 "lib/Haanga/Compiler/Parser.php"
#line 108 "lib/Haanga/Compiler/Parser.y"
    function yy_r21(){ $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -2]->minor, 'list' => $this->yystack[$this->yyidx + -1]->minor);     }
#line 1624 "lib/Haanga/Compiler/Parser.php"
#line 109 "lib/Haanga/Compiler/Parser.y"
    function yy_r22(){ $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -4]->minor, 'as' => $this->yystack[$this->yyidx + -1]->minor, 'list' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1627 "lib/Haanga/Compiler/Parser.php"
#line 111 "lib/Haanga/Compiler/Parser.y"
    function yy_r23(){ if ('end'.$this->yystack[$this->yyidx + -5]->minor != $this->yystack[$this->yyidx + -1]->minor) { $this->error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor); } $this->_retvalue = array('operation' => 'custom_tag', 'name' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor, 'list' => array());    }
#line 1630 "lib/Haanga/Compiler/Parser.php"
#line 112 "lib/Haanga/Compiler/Parser.y"
    function yy_r24(){ if ('endbuffer' != $this->yystack[$this->yyidx + -1]->minor) { $this->error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor); } $this->_retvalue = array('operation' => 'buffer', 'name' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);    }
#line 1633 "lib/Haanga/Compiler/Parser.php"
#line 113 "lib/Haanga/Compiler/Parser.y"
    function yy_r25(){ if ('endspacefull' != $this->yystack[$this->yyidx + -1]->minor) { $this->error("Unexpected ".$this->yystack[$this->yyidx + -1]->minor); } $this->_retvalue = array('operation' => 'spacefull', 'body' => $this->yystack[$this->yyidx + -3]->minor);    }
#line 1636 "lib/Haanga/Compiler/Parser.php"
#line 116 "lib/Haanga/Compiler/Parser.y"
    function yy_r26(){ $this->_retvalue = array('operation' => 'alias', 'var' => $this->yystack[$this->yyidx + -7]->minor, 'as' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1639 "lib/Haanga/Compiler/Parser.php"
#line 120 "lib/Haanga/Compiler/Parser.y"
    function yy_r28(){
    if (!is_file($this->yystack[$this->yyidx + 0]->minor)) {
        $this->error($this->yystack[$this->yyidx + 0]->minor." is not a valid file"); 
    } 
    require_once $this->yystack[$this->yyidx + 0]->minor;
    }
#line 1647 "lib/Haanga/Compiler/Parser.php"
#line 128 "lib/Haanga/Compiler/Parser.y"
    function yy_r29(){
    $this->compiler->set_context($this->yystack[$this->yyidx + -3]->minor, array());
    $this->_retvalue = array('operation' => 'loop', 'variable' => $this->yystack[$this->yyidx + -3]->minor, 'index' => NULL, 'array' => $this->yystack[$this->yyidx + -1]->minor);
    }
#line 1653 "lib/Haanga/Compiler/Parser.php"
#line 133 "lib/Haanga/Compiler/Parser.y"
    function yy_r30(){
    $this->compiler->set_context($this->yystack[$this->yyidx + -3]->minor, array());
    $this->_retvalue = array('operation' => 'loop', 'variable' => $this->yystack[$this->yyidx + -3]->minor, 'index' => $this->yystack[$this->yyidx + -5]->minor, 'array' => $this->yystack[$this->yyidx + -1]->minor);
    }
#line 1659 "lib/Haanga/Compiler/Parser.php"
#line 139 "lib/Haanga/Compiler/Parser.y"
    function yy_r31(){ 
    $this->_retvalue = $this->yystack[$this->yyidx + -4]->minor;
    $this->_retvalue['body'] = $this->yystack[$this->yyidx + -3]->minor;
    }
#line 1665 "lib/Haanga/Compiler/Parser.php"
#line 144 "lib/Haanga/Compiler/Parser.y"
    function yy_r32(){ 
    $this->_retvalue = $this->yystack[$this->yyidx + -8]->minor;
    $this->_retvalue['body']  = $this->yystack[$this->yyidx + -7]->minor;
    $this->_retvalue['empty'] = $this->yystack[$this->yyidx + -3]->minor;
    }
#line 1672 "lib/Haanga/Compiler/Parser.php"
#line 150 "lib/Haanga/Compiler/Parser.y"
    function yy_r33(){ $this->_retvalue = array('operation' => 'if', 'expr' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1675 "lib/Haanga/Compiler/Parser.php"
#line 151 "lib/Haanga/Compiler/Parser.y"
    function yy_r34(){ $this->_retvalue = array('operation' => 'if', 'expr' => $this->yystack[$this->yyidx + -9]->minor, 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1678 "lib/Haanga/Compiler/Parser.php"
#line 154 "lib/Haanga/Compiler/Parser.y"
    function yy_r35(){ 
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1683 "lib/Haanga/Compiler/Parser.php"
#line 158 "lib/Haanga/Compiler/Parser.y"
    function yy_r36(){ 
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -3]->minor, 'check' => $this->yystack[$this->yyidx + -5]->minor);
    }
#line 1688 "lib/Haanga/Compiler/Parser.php"
#line 161 "lib/Haanga/Compiler/Parser.y"
    function yy_r37(){ 
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor); 
    }
#line 1693 "lib/Haanga/Compiler/Parser.php"
#line 165 "lib/Haanga/Compiler/Parser.y"
    function yy_r38(){ 
    $this->_retvalue = array('operation' => 'ifchanged', 'body' => $this->yystack[$this->yyidx + -7]->minor, 'check' => $this->yystack[$this->yyidx + -9]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1698 "lib/Haanga/Compiler/Parser.php"
#line 170 "lib/Haanga/Compiler/Parser.y"
    function yy_r39(){  $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '==', 1 => $this->yystack[$this->yyidx + -6]->minor, 2 => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1701 "lib/Haanga/Compiler/Parser.php"
#line 171 "lib/Haanga/Compiler/Parser.y"
    function yy_r40(){  $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '==', 1 => $this->yystack[$this->yyidx + -10]->minor, 2 => $this->yystack[$this->yyidx + -9]->minor, 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1704 "lib/Haanga/Compiler/Parser.php"
#line 172 "lib/Haanga/Compiler/Parser.y"
    function yy_r41(){  $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '!=', 1 => $this->yystack[$this->yyidx + -6]->minor, 2 => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1707 "lib/Haanga/Compiler/Parser.php"
#line 173 "lib/Haanga/Compiler/Parser.y"
    function yy_r42(){  $this->_retvalue = array('operation' => 'ifequal', 'cmp' => '!=', 1 => $this->yystack[$this->yyidx + -10]->minor, 2 => $this->yystack[$this->yyidx + -9]->minor, 'body' => $this->yystack[$this->yyidx + -7]->minor, 'else' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1710 "lib/Haanga/Compiler/Parser.php"
#line 177 "lib/Haanga/Compiler/Parser.y"
    function yy_r43(){ $this->_retvalue = array('operation' => 'block', 'name' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1713 "lib/Haanga/Compiler/Parser.php"
#line 179 "lib/Haanga/Compiler/Parser.y"
    function yy_r44(){ $this->_retvalue = array('operation' => 'block', 'name' => $this->yystack[$this->yyidx + -6]->minor, 'body' => $this->yystack[$this->yyidx + -4]->minor);     }
#line 1716 "lib/Haanga/Compiler/Parser.php"
#line 186 "lib/Haanga/Compiler/Parser.y"
    function yy_r47(){ $this->_retvalue = array('operation' => 'filter', 'functions' => $this->yystack[$this->yyidx + -5]->minor, 'body' => $this->yystack[$this->yyidx + -3]->minor);     }
#line 1719 "lib/Haanga/Compiler/Parser.php"
#line 189 "lib/Haanga/Compiler/Parser.y"
    function yy_r48(){ $this->_retvalue=array('operation' => 'regroup', 'array' => $this->yystack[$this->yyidx + -4]->minor, 'row' => $this->yystack[$this->yyidx + -2]->minor, 'as' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1722 "lib/Haanga/Compiler/Parser.php"
#line 192 "lib/Haanga/Compiler/Parser.y"
    function yy_r49(){ $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor; $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1725 "lib/Haanga/Compiler/Parser.php"
#line 193 "lib/Haanga/Compiler/Parser.y"
    function yy_r50(){ $this->_retvalue = array($this->yystack[$this->yyidx + 0]->minor);     }
#line 1728 "lib/Haanga/Compiler/Parser.php"
#line 195 "lib/Haanga/Compiler/Parser.y"
    function yy_r51(){ $this->_retvalue = array($this->yystack[$this->yyidx + -2]->minor, 'args'=>array($this->yystack[$this->yyidx + 0]->minor));     }
#line 1731 "lib/Haanga/Compiler/Parser.php"
#line 199 "lib/Haanga/Compiler/Parser.y"
    function yy_r53(){ $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor; $this->_retvalue[] = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1734 "lib/Haanga/Compiler/Parser.php"
#line 205 "lib/Haanga/Compiler/Parser.y"
    function yy_r56(){ $this->_retvalue = array('var' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1737 "lib/Haanga/Compiler/Parser.php"
#line 206 "lib/Haanga/Compiler/Parser.y"
    function yy_r57(){ $this->_retvalue = array('number' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1740 "lib/Haanga/Compiler/Parser.php"
#line 207 "lib/Haanga/Compiler/Parser.y"
    function yy_r58(){ $this->_retvalue = array('string' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1743 "lib/Haanga/Compiler/Parser.php"
#line 209 "lib/Haanga/Compiler/Parser.y"
    function yy_r59(){ $this->_retvalue = array('var_filter' => $this->yystack[$this->yyidx + 0]->minor);     }
#line 1746 "lib/Haanga/Compiler/Parser.php"
#line 215 "lib/Haanga/Compiler/Parser.y"
    function yy_r63(){  $this->_retvalue = "";     }
#line 1749 "lib/Haanga/Compiler/Parser.php"
#line 217 "lib/Haanga/Compiler/Parser.y"
    function yy_r65(){  $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;     }
#line 1752 "lib/Haanga/Compiler/Parser.php"
#line 219 "lib/Haanga/Compiler/Parser.y"
    function yy_r67(){ $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor.$this->yystack[$this->yyidx + 0]->minor;     }
#line 1755 "lib/Haanga/Compiler/Parser.php"
#line 223 "lib/Haanga/Compiler/Parser.y"
    function yy_r69(){ $this->_retvalue = array('op_expr' => 'not', $this->yystack[$this->yyidx + 0]->minor);     }
#line 1758 "lib/Haanga/Compiler/Parser.php"
#line 224 "lib/Haanga/Compiler/Parser.y"
    function yy_r70(){ $this->_retvalue = array('op_expr' => @$this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);     }
#line 1761 "lib/Haanga/Compiler/Parser.php"
#line 227 "lib/Haanga/Compiler/Parser.y"
    function yy_r73(){ $this->_retvalue = array('op_expr' => trim(@$this->yystack[$this->yyidx + -1]->minor), $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);     }
#line 1764 "lib/Haanga/Compiler/Parser.php"
#line 229 "lib/Haanga/Compiler/Parser.y"
    function yy_r75(){ $this->_retvalue = array('op_expr' => 'expr', $this->yystack[$this->yyidx + -1]->minor);     }
#line 1767 "lib/Haanga/Compiler/Parser.php"
#line 233 "lib/Haanga/Compiler/Parser.y"
    function yy_r77(){ if (!is_array($this->yystack[$this->yyidx + -2]->minor)) { $this->_retvalue = array($this->yystack[$this->yyidx + -2]->minor); } else { $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor; }  $this->_retvalue[]=array('object' => $this->yystack[$this->yyidx + 0]->minor);    }
#line 1770 "lib/Haanga/Compiler/Parser.php"
#line 234 "lib/Haanga/Compiler/Parser.y"
    function yy_r78(){ if (!is_array($this->yystack[$this->yyidx + -2]->minor)) { $this->_retvalue = array($this->yystack[$this->yyidx + -2]->minor); } else { $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor; } $this->_retvalue[] = ($this->compiler->var_is_object($this->_retvalue)) ? array('object' => $this->yystack[$this->yyidx + 0]->minor) : $this->yystack[$this->yyidx + 0]->minor;    }
#line 1773 "lib/Haanga/Compiler/Parser.php"
#line 235 "lib/Haanga/Compiler/Parser.y"
    function yy_r79(){ if (!is_array($this->yystack[$this->yyidx + -3]->minor)) { $this->_retvalue = array($this->yystack[$this->yyidx + -3]->minor); } else { $this->_retvalue = $this->yystack[$this->yyidx + -3]->minor; }  $this->_retvalue[]=$this->yystack[$this->yyidx + -1]->minor;    }
#line 1776 "lib/Haanga/Compiler/Parser.php"

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
    throw new Haanga_Compiler_Exception('Unexpected ' . $this->tokenName($yymajor) . '(' . $TOKEN. '), expected one of: ' . implode(',', $expect));
#line 1896 "lib/Haanga/Compiler/Parser.php"
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

#line 1917 "lib/Haanga/Compiler/Parser.php"
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