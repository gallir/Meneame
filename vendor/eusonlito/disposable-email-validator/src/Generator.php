<?php
namespace Eusonlito\DisposableEmail;

class Generator
{
    /**
     * @var array
     */
    private static $sources = [
        'IvoloDisposableEmailDomains', 'MattketmoEmailChecker', 'FgribreauMailchecker', 'MartensonDisposableEmailDomains'
    ];

    /**
     * @var array
     */
    private static $domains = [];

    /**
     * @var array
     */
    private static $wildcards = [];

    /**
     * @var array
     */
    private static $whitelist = [];

    /**
     * @return void
     */
    public static function generate()
    {
        foreach (static::$sources as $source) {
            static::addSource(__NAMESPACE__.'\\Sources\\'.$source);
        }

        static::store();
    }

    /**
     * @param string $source
     *
     * @return void
     */
    private static function addSource($source)
    {
        static::$whitelist = array_merge(static::$whitelist, $source::getWhitelist());
        static::$domains = array_merge(static::$domains, $source::getDomains());
        static::$wildcards = array_merge(static::$wildcards, $source::getWildcards());
    }

    /**
     * @return void
     */
    private static function store()
    {
        static::storeFile('whitelist', static::$whitelist);
        static::storeFile('domains', static::$domains);
        static::storeFile('wildcards', static::$wildcards);
    }

    /**
     * @param string $name
     * @param array  $list
     * @param bool  $filter
     *
     * @return void
     */
    private static function storeFile($name, array $list, $filter = false)
    {
        $list = array_filter(array_unique(array_map('strtolower', $list)));

        if ($filter) {
            $list = array_diff($list, static::$whitelist);
        }

        sort($list);

        file_put_contents(ROOT.'/data/'.$name.'.php', '<?php return '.var_export($list, true).';', LOCK_EX);
    }
}
