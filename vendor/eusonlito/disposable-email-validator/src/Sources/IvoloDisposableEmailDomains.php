<?php
namespace Eusonlito\DisposableEmail\Sources;

class IvoloDisposableEmailDomains extends SourceInterface
{
    /**
     * @return array
     */
    public static function getDomains()
    {
        return static::getFile('index');
    }

    /**
     * @return array
     */
    public static function getWildcards()
    {
        return static::getFile('wildcard');
    }

    /**
     * @param string $file
     *
     * @return array
     */
    private static function getFile($file)
    {
        return static::json('ivolo/disposable-email-domains/'.$file.'.json');
    }
}
