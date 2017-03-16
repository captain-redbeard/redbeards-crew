<?php
/**
 * @author captain-redbeard
 * @since 16/03/17
 */
namespace Redbeard\Crew\Utils;

class Urls
{
    /**
    * Get the current directory as a URL.
    *
    * @returns url
    */
    public static function getDirectoryAsUrl()
    {
        $url = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $url .= $_SERVER['SERVER_NAME'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__DIR__));
        return $url;
    }
    
    /**
    * Get the currency page as a URL.
    *
    * @returns url
    */
    public static function getUrl()
    {
        $url = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $url .= $_SERVER['SERVER_NAME'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['PHP_SELF']);
        return str_replace('/index.php', '', $url);
    }
}
