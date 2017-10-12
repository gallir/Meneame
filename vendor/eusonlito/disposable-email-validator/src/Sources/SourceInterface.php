<?php
namespace Eusonlito\DisposableEmail\Sources;

abstract class SourceInterface
{
    /**
     * @return array
     */
    public static function getDomains()
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getWildcards()
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getWhitelist()
    {
        return [];
    }

    /**
     * @param string $file
     *
     * @return array
     */
    protected static function file($file)
    {
        return file(static::vendor($file), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    /**
     * @param string $file
     *
     * @return array
     */
    protected static function json($file)
    {
        return json_decode(static::contents($file));
    }

    /**
     * @param string $file
     *
     * @return array
     */
    protected static function contents($file)
    {
        return file_get_contents(static::vendor($file));
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected static function vendor($file)
    {
        return ROOT.'/vendor/'.$file;
    }
}
