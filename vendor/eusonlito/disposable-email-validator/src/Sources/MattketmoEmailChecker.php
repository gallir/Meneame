<?php
namespace Eusonlito\DisposableEmail\Sources;

class MattketmoEmailChecker extends SourceInterface
{
    /**
     * @return array
     */
    public static function getDomains()
    {
        return static::file('mattketmo/email-checker/res/throwaway_domains.txt');
    }
}
