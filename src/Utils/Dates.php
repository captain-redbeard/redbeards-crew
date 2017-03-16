<?php
/**
 * @author captain-redbeard
 * @since 16/03/17
 */
namespace Redbeard\Crew\Utils;

use Redbeard\Crew\Config;
use \DateTime;
use \DateTimeZone;

class Dates
{
    /**
    * Convert the specified time into the users timezone.
    *
    * @param $time_convert  - datetime to convert
    * @param $short         - short date formate
    *
    * @returns converted datetime
    */
    public static function convertDateTime($time_convert, $short = false)
    {
        $userTime = new DateTime($time_convert, new DateTimeZone(Config::get('app.timezone')));
        $userTime->setTimezone(new DateTimeZone($_SESSION[Config::get('app.user_session')]->timezone));
        if (!$short) {
            return $userTime->format('Y-m-d h:i:s A');
        } else {
            return date('d/m') != $userTime->format('d/m') ?
                $userTime->format('d/m h:i A') :
                $userTime->format('h:i A');
        }
    }
    
    /**
    * Convert the specified datetime into past or future tense.
    *
    * @param $date - datetime to convert
    *
    * @returns converted datetime
    */
    public static function niceTime($date)
    {
        if (empty($date)) {
            return 'No date provided.';
        }
        
        $periods = ['second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade'];
        $lengths = ['60','60','24','7','4.35','12','10'];
        $now = time();
        $unix_date = strtotime($date);
        
        if (empty($unix_date)) {
            return 'Bad date.';
        }
        
        if ($now > $unix_date) {
            $difference = $now - $unix_date;
            $tense = 'ago';
        } else {
            $difference = $unix_date - $now;
            $tense = '';
        }
        
        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
            $difference /= $lengths[$j];
        }
        
        $difference = round($difference);
        
        if ($difference != 1) {
            $periods[$j].= 's';
        }
        
        return "$difference $periods[$j] {$tense}";
    }
}
