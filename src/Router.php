<?php
/**
 * @author captain-redbeard
 * @since 20/01/17
 */
namespace Redbeard\Crew;

use Redbeard\Crew\Config;
use Redbeard\Crew\Utils\Strings;

class Router
{
    protected $controller = null;
    protected $method = null;
    protected $parameters = [];
    protected $controller_index = 0;
    protected $method_index = 1;
    
    public function route($get, $post)
    {
        $this->controller = Config::get('app.path') . Config::get('app.controllers_path') . Config::get('app.default_controller');
        $this->method = Config::get('app.default_method');
        
        //Get parsed url
        $url = $this->parseUrl($get);
        
        //Set controller
        $url = $this->setController(
            $url,
            $this->controller_index
        );
        
        //Create controller instance
        $this->controller = new $this->controller();
        
        //Set method
        $url = $this->setMethod(
            $url,
            $this->method_index
        );
        
        //Set parameters
        $this->parameters = $url;
        if ($post != null) {
            array_push($this->parameters, $post);
        }
        
        //Call controller->method
        if (!$this->controller->isRedirecting()) {
            call_user_func_array([$this->controller, $this->method], $this->parameters);
        }
    }
    
    private function parseUrl($get)
    {
        if (isset($get['url'])) {
            return explode(
                '/',
                Strings::cleanInput(
                    filter_var(
                        filter_var(
                            rtrim($get['url'], '/'),
                            FILTER_SANITIZE_URL
                        ),
                        FILTER_SANITIZE_FULL_SPECIAL_CHARS
                    ),
                    2
                )
            );
        }
        
        return [];
    }
    
    private function setController($url, $controller_index)
    {
        if (isset($url[$controller_index])) {
            $temp = '';
            
            //Get folder structure
            for ($i = 0; $i <= $controller_index; $i++) {
                $temp .= Strings::cleanMethodName($url[$i]);
                
                if ($i < $controller_index) {
                    $temp .= '/';
                }
            }
            
            //Check if controller is found, otherwise keep searching folders
            if (file_exists(Config::get('app.base_directory') . Config::get('app.controllers_directory') . $temp . '.php')) {
                $this->controller = Config::get('app.path') . Config::get('app.controllers_path') . str_replace('/', '\\', $temp);
                $this->method_index = $controller_index + 1;
                
                //Unset URL
                for ($i = 0; $i <= $controller_index; $i++) {
                    unset($url[$i]);
                }
            } elseif (file_exists(Config::get('app.base_directory') . Config::get('app.controllers_directory') . $temp)) {
                $url = $this->setController(
                    $url,
                    ($controller_index + 1)
                );
            }
        }
        
        return $url;
    }
    
    private function setMethod($url, $method_index)
    {
        if (isset($url[$method_index])) {
            $temp = Strings::cleanMethodName($url[$method_index]);
            
            if (method_exists($this->controller, $temp)) {
                $reflection_method = new \ReflectionMethod($this->controller, $temp);
                if ($reflection_method->isPublic()) {
                    $this->method = $temp;
                    unset($url[$method_index]);
                }
            }
        }
        
        return $url;
    }
}
