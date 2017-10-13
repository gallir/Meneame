<?php
namespace Eusonlito\DisposableEmail;

class Check
{
    /**
     * @var array
     */
    private static $domains = [];

    /**
     * @var array
     */
    private static $wildcards = [];

    /**
     * @param string $email
     *
     * @return bool
     */
    public static function email($email)
    {
        if (!static::emailFilter($email)) {
            return false;
        }

        return static::domain(explode('@', $email)[1]);
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public static function emailFilter($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public static function emailExpression($email)
    {
        return (bool)preg_match('/^[a-z0-9_\-\.]+(\+[a-z0-9_\-\.]+)*@[a-z0-9_\-\.]+\.[a-z]{2,6}$/i', $email);
    }

    /**
     * @param string $domain
     *
     * @return bool
     */
    public static function domain($domain)
    {
        if (in_array($domain, static::domains())) {
            return false;
        }

        return static::wildcard($domain);
    }

    /**
     * @param string $domain
     *
     * @return bool
     */
    public static function wildcard($domain)
    {
        return !in_array(implode('.', array_slice(explode('.', $domain), -2)), static::wildcards());
    }

    /**
     * @return array
     */
    private static function domains()
    {
        if (empty(static::$domains)) {
            static::$domains = static::load('domains');
        }

        return static::$domains;
    }

    /**
     * @return array
     */
    private static function wildcards()
    {
        if (empty(static::$wildcards)) {
            static::$wildcards = static::load('wildcards');
        }

        return static::$wildcards;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    private function load($name)
    {
        return require dirname(__DIR__).'/data/'.$name.'.php';
    }
}
