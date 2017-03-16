<?php
/**
 * @author captain-redbeard
 * @since 16/03/17
 */
namespace Redbeard\Crew\Utils;

class Formats
{
    /**
    * Convert to specified string into biggest byte format.
    * e.g. 12000 = 12 KB
    *
    * @param $bytes - string to be converted
    *
    * @returns converted string
    */
    public static function convertBytes($bytes)
    {
        $types = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        for ($i = 0; $bytes >= 1024 && $i < (count($types) -1); $bytes /= 1024, $i++) {
            return(round($bytes, 2) . ' ' . $types[$i]);
        }
        
        return $bytes . ' bytes';
    }
}
