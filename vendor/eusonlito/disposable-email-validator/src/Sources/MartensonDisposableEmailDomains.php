<?php
namespace Eusonlito\DisposableEmail\Sources;

class MartensonDisposableEmailDomains extends SourceInterface
{
    /**
     * @return array
     */
    public static function getDomains()
    {
        return static::file('martenson/disposable-email-domains/disposable_email_blacklist.conf');
    }

    /**
     * @return array
     */
    public static function getWhitelist()
    {
        return static::file('martenson/disposable-email-domains/whitelist.conf');
    }
}
