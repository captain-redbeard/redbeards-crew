<?php
/**
 * @author captain-redbeard
 * @since 05/02/17
 */
namespace Redbeard\Crew;

class Config
{
    public static $configs = [];
    
    public static function init()
    {
        //Load default config
        self::loadAll(__DIR__ . '/Config/', false);
        
        //Load user default config
        $config = require_once __DIR__ . '/../../../../application/config.php';
        self::load($config, true);
        
        //Load user config
        self::loadAll(self::get('app.base_directory') . self::get('app.config_directory'), true);
    }
    
    public static function loadAll($directory, $keep_existing = false)
    {
        //Get other configs
        $configs = scandir($directory);
        
        //Load other configs
        foreach ($configs as $file) {
            $pathinfo = pathinfo($file);
            
            if ($file !== '.' && $file !== '..' && $pathinfo['extension'] === 'php') {
                $config = require_once $directory . $file;
                self::load($config, $keep_existing);
            }
        }
    }
    
    public static function load($config, $keep_existing = false)
    {
        foreach ($config as $key => $value) {
            self::set($key, $value, $keep_existing);
            
            foreach ($value as $k => $v) {
                self::set($key . '.' . $k, $v);
            }
        }
    }
    
    public static function get($key)
    {
        $split = explode('.', $key);
        
        switch (count($split)) {
            case 1:
                return self::$configs[$split[0]];
                break;
            case 2:
                return self::$configs[$split[0]][$split[1]];
                break;
            case 3:
                return self::$configs[$split[0]][$split[1]][$split[2]];
                break;
            default:
                return self::$configs[$split[0]][$split[1]][$split[2]][$split[3]];
                break;
        }
    }
    
    public static function set($key, $value, $keep_existing = false)
    {
        $split = explode('.', $key);
        
        switch (count($split)) {
            case 1:
                if (!$keep_existing) {
                    self::$configs[$split[0]] = $value;
                }
                break;
            case 2:
                self::$configs[$split[0]][$split[1]] = $value;
                break;
            case 3:
                self::$configs[$split[0]][$split[1]][$split[2]] = $value;
                break;
            default:
                self::$configs[$split[0]][$split[1]][$split[2]][$split[3]] = $value;
                break;
        }
    }
}
