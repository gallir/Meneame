<?php
namespace Eusonlito\DisposableEmail\Sources;

class FgribreauMailchecker extends SourceInterface
{
    /**
     * @return array
     */
    public static function getDomains()
    {
        $file = static::contents('fgribreau/mailchecker/list.json');

        return call_user_func_array('array_merge', json_decode(preg_replace('#//.*#', '', $file)));
    }
}
