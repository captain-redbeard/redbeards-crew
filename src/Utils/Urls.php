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
        $url = self::getProtocol();
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
        $url = self::getProtocol();
        $url .= $_SERVER['SERVER_NAME'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['PHP_SELF']);
        return str_replace('/index.php', '', $url);
    }
    
    /**
    * Check if SSL is used.
    *
    * @returns boolean
    */
    public static function isSecure()
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['https'] == 'on') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
             (
                $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ||
                $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'on'
             )
            );
    }
    
    /**
    * Get the protocol in use.
    *
    * returns string
    */
    public static function getProtocol()
    {
        return self::isSecure() ? 'https://' : 'http://';
    }
}
