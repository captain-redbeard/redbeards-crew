<?php
/**
 * @author captain-redbeard
 * @since 18/02/17
 */
namespace Redbeard\Crew;

class Tracking
{
    private static $browserArray = [
        'Windows Mobile' => '(IEMobile)',
        'Android Mobile' => '(Android)',
        'iPhone Mobile' => '(iPhone)',
        'Firefox' => '(Firefox)',
        'Google Chrome' => '(Chrome)',
        'Internet Explorer' => '(MSIE)',
        'Opera' => '(Opera)',
        'Safari' => '(Safari)',
        'Netscape' => '(Netscape)'
    ];
    
    private static $osArray = [
        'Windows CE' => '(Windows ce)',
        'Windows 95' => '(Win95)|(Windows 95)',
        'Windows 98' => '(Win98)|(Windows 98)',
        'Windows ME' => '(Windows ME)',
        'Windows NT 4.0' => '(WinNT)|(Windows NT 4.0)|(WinNT4.0)|(Windows NT)',
        'Windows 2000' => '(Windows 2000)|(Windows NT 5.0)|(Windows NT 5.01)',
        'Windows XP' => '(Windows XP)|(Windows NT 5.1)',
        'Windows Vista' => '(Windows NT 6.0)',
        'Windows 7' => '(Windows NT 6.1)|(Windows NT 7.0)',
        'Windows 8' => '(Windows NT 6.2)',
        'Windows 8.1' => '(Windows NT 6.3)',
        'Windows 10' => '(Windows NT 10.0)',
        
        'Mac OS X Beta (Kodiak)' => '(Mac OS X beta)',
        'Mac OS X Cheetah' => '(Mac OS X 10.0)',
        'Mac OS X Puma' => '(Mac OS X 10.1)',
        'Mac OS X Jaguar' => '(Mac OS X 10.2)',
        'Mac OS X Panther' => '(Mac OS X 10.3)',
        'Mac OS X Tiger' => '(Mac OS X 10.4)',
        'Mac OS X Leopard' => '(Mac OS X 10.5)',
        'Mac OS X Snow Leopard' => '(Mac OS X 10.6)',
        'Mac OS X Lion' => '(Mac OS X 10.7)',
        'Mac OS X Mountain Lion' => '(Mac OS X 10.8)',
        'Mac OS X Mavericks' => '(Mac OS X 10.9)',
        'Mac OS X Yosemite' => '(Mac OS X 10.10)',
        'Mac OS X El Capitan' => '(Mac OS X 10.11)',
        'Mac OS Sierra' => '(Mac OS X 10.12)',
        'Mac OS (classic)' => '(mac_powerpc)|(macintosh)',
        
        'Linux' => '(X11)|(Linux)',
        'Ubuntu' => '(Ubuntu)',
        'OpenBSD' => '(OpenBSD)',
        'SunOS' => '(SunOS)',
        'QNX' => '(QNX)',
        'BeOS' => '(BeOS)',
        'OS2' => '(os\/2)',
        'SearchBot' => '(nuhk)|(googlebot)|(yammybot)|(openbot)|(slurp)|(msnbot)|(ask jeeves\/teoma)|(ia_archiver)',
        'Mac OS' => '(Mac_PowerPC)|(Macintosh)|(Mac OS)'
    ];
    
    public static function getUserAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }
    
    public static function getRemoteAddress()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }
    
    public static function getXForwardedFor()
    {
        return isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
    }
    
    public static function getReferer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }
    
    public static function getHostAddress()
    {
        return gethostbyaddr(self::getRemoteAddress());
    }
    
    public static function getBrowser()
    {
        $browser = 'Unknown Browser';
        
        foreach (self::$browserArray as $k => $v) {
            if (preg_match("/$v/i", self::getUserAgent())) {
                $browser = $k;
                break;
            }
        }
        
        return $browser;
    }
    
    public static function getOperatingSystem()
    {
        $os = 'Unknown Operating System';
        
        foreach (self::$osArray as $k => $v) {
            if (preg_match("/$v/i", self::getUserAgent())) {
                $os = $k;
                break;
            }
        }
        
        return $os;
    }
}
