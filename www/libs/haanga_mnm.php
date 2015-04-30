<?php

class Haanga_Extension_Filter_NoScheme
{
	public $php_alias = 'url_no_scheme';
}

class Haanga_Extension_Filter_TextToHTML
{
	public $php_alias = 'text_to_html';
}

class Haanga_Extension_Tag_MeneameEndtime
{
	public $is_block = FALSE;

	static function generator($cmp, $args, $assign=NULL)
	{
		/* ast */
		$code = hcode();

		/* llamar a la funcion */
		$exec = hexec('sprintf', "<!--Delivered to you in %4.3f seconds-->",
			hexpr( hexec('microtime', TRUE), '-', hvar('globals', 'start_time') )
		);

		/* imprimir la funcion */
		$cmp->do_print($code, $exec);

		return $code;
	}
}

class Haanga_Extension_Filter_TxtShorter
{
	public $is_block = FALSE;

	static function generator($cmp, $args)
	{
		if (!isset($args[1])) {
			$args[1] = 40; /* truncate to 40 letters by default */
		}
		return hexec('txt_shorter', $args[0], $args[1]);
	}
}

class Haanga_Extension_Filter_SubName
{
	public $php_alias = 'SitesMgr::get_name';
}

class Haanga_Extension_Filter_CleanUrl
{
	public $php_alias = 'clean_input_url';
}

class Haanga_Extension_Filter_UserUri
{
	public $php_alias = 'get_user_uri';
}

class Haanga_Extension_Filter_PostsURL
{
	public $php_alias = 'post_get_base_url';
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
