<?php
/**
 * @author captain-redbeard
 * @since 16/03/17
 */
namespace Redbeard\Crew\Utils;

class Strings
{
    /**
    * Generate a random string with the desired length.
    * a-zA-Z0-9
    *
    * @param $length        - desired length
    * @param $character_set - characters to use
    *
    * @returns random string
    */
    public static function generateRandomString(
        $length,
        $character_set = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    )
    {
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $character_set[random_int(0, strlen($character_set) - 1)];
        }
        
        return $randomString;
    }
    
    /**
    * Check if the container contains the string.
    *
    * @param $contains  - needle
    * @param $container - haystack
    *
    * @returns boolean
    */
    public static function contains($contains, $container)
    {
        return strpos(strtolower($container), strtolower($contains)) !== false;
    }
    
    /**
    * Limit the words allowed in one string.
    *
    * @param $string        - string to limit
    * @param $word_limit    - word limit count
    *
    * @returns word limited string
    */
    public static function limitWords($string, $word_limit)
    {
        $words = explode(' ', $string);
        return implode(' ', array_slice($words, 0, $word_limit));
    }
    
    /**
    * Truncate the specified string to the desired length.
    *
    * @param $string - string to truncate
    * @param $length - desired length
    * @param $append - string to append
    *
    * @returns truncated string
    */
    public static function truncate($string, $length, $append = '')
    {
        if (strlen($string) > $length) {
            $string = substr($string, 0, $length) . $append;
        }
        
        return $string;
    }
    
    /**
    * Clean the specified url, normally used for page titles.
    *
    * @param $url - string to clean
    *
    * @returns cleaned string
    */
    public static function cleanURL($url)
    {
        $newtitle = self::limitWords($url, 10);
        $urltitle = preg_replace('/[^a-z0-9]/i', ' ', $newtitle);
        return strtolower(str_replace(' ', '-', $newtitle));
    }
    
    /**
    * Restore the specified url, normally used for page titles.
    *
    * @param $url - string to restore
    *
    * @returns restored string
    */
    public static function restoreURL($url)
    {
        return ucfirst(str_replace('-', ' ', $url));
    }
    
    /**
    * Cleans the specified string by removing spaces and hypens and
    * applying camel case.
    *
    * @param $name - string to clean
    *
    * @returns cleaned string
    */
    public static function cleanMethodName($name)
    {
        return str_replace(
            ' ',
            '',
            ucwords(
                str_replace(
                    '-',
                    ' ',
                    strtolower($name)
                )
            )
        );
    }
    
    /**
    * Clean the string to the specified degree.
    *
    * @param $input - string to clean
    * @param $level - degree to clean
    *
    * Levels:
    * 0 - none
    * 1 - strip tags, replace all except a-zA-Z0-9-_\/@.
    * 2 - strip tags, replace all except a-zA-Z0-9-\/
    * 3 - strip tags, replace all except a-zA-Z0-9-
    * 4 - strip tags, replace all except a-zA-Z0-9-, cleanTitle
    *
    * @returns cleaned string
    */
    public static function cleanInput($input, $level = -1)
    {
        switch ($level) {
            case 0:
                $clean = $input;
                break;
            case 1:
                $clean = strip_tags($input);
                $clean = preg_replace('/[^a-zA-Z0-9 \-_\/ @.]/i', ' ', $clean);
                break;
            case 2:
                $clean = strip_tags($input);
                $clean = preg_replace('/[^a-zA-Z0-9 \-\/ ,]/i', ' ', $clean);
                break;
            case 3:
                $clean = strip_tags($input);
                $clean = preg_replace('/[^a-zA-Z0-9 \-]/i', ' ', $clean);
                break;
            case 4:
                $clean = strip_tags($input);
                $clean = preg_replace('/[^a-zA-Z0-9 \-]/i', ' ', $clean);
                $clean = self::cleanUrl($clean);
                break;
            default:
                $clean = strip_tags($input);
                break;
        }
        
        return $clean;
    }
}
