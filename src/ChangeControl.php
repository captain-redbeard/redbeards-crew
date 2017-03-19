<?php
/**
 * @author captain-redbeard
 * @since 25/02/17
 */
namespace Redbeard\Crew;

use Redbeard\Crew\Config;
use Redbeard\Crew\Tracking;
use Redbeard\Crew\Database;
use Redbeard\Crew\Utils\Strings;

class ChangeControl
{
    public static function add($title, $array1 = null, $array2 = null, $description = null)
    {
        //Generate guid
        $guid = Strings::generateRandomString(32);
        $title = '<strong>' . Strings::cleanInput($title, 2) . '</strong>';
        $ip_address = Tracking::getRemoteAddress();
        $host_address = Tracking::getHostAddress();
        $ip_fowarded = Tracking::getXForwardedFor();
        $reffered = Tracking::getReferer();
        $os = Tracking::getOperatingSystem();
        $browser = Tracking::getBrowser();
        $agent = Tracking::getUserAgent();
        
        if ($description === null) {
            $description = '';
            $result1 = array_diff($array1, $array2);
            $result2 = array_diff($array2, $array1);

            foreach ($result1 as $key => $value) {
                $description .= $key . ': ' . $value . ' -> ' . $result2[$key] . '<br/>';
            }
        }
        
        //Add to database
        Database::insert(
            "INSERT INTO change_controls (change_guid, user_guid, title, description, ip_address, host_address,
            ip_fowarded, reffered, operating_system, browser, browser_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);",
            [
                $guid,
                $_SESSION[Config::get('app.user_session')]->guid,
                $title,
                $description,
                $ip_address,
                $host_address,
                $ip_fowarded,
                $reffered,
                $os,
                $browser,
                $agent
            ]
        );
    }
}
