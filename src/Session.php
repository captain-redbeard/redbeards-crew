<?php
/**
 * @author captain-redbeard
 * @since 20/01/17
 */
namespace Redbeard\Crew;

use Redbeard\Crew\Config;

class Session
{
    public static function start()
    {
        if (!isset($_SESSION)) {
            $session_name = Config::get('app.user_session');
            $secure = Config::get('app.secure_cookies');
            $http_only = true;
            
            $cookie_params = session_get_cookie_params();
            session_set_cookie_params(
                $cookie_params['lifetime'],
                $cookie_params['path'],
                $cookie_params['domain'],
                $secure,
                $http_only
            );
            
            session_name($session_name);
            session_start();
            session_regenerate_id();
            return true;
        }
        
        return false;
    }
    
    public static function kill()
    {
        self::start();
        
        $_SESSION = [];
        
        $params = session_get_cookie_params();
        
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
        
        return session_destroy();
    }
}
