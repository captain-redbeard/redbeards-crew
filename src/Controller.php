<?php
/**
 * @author captain-redbeard
 * @since 20/01/17
 */
namespace Redbeard\Crew;

use Redbeard\Crew\Config;
use Redbeard\Crew\Session;
use Redbeard\Crew\Database;
use Redbeard\Crew\Utils\Strings;

class Controller
{
    private $redirecting = false;
    
    protected function model($model)
    {
        $model = $this->config('app.path') . $this->config('app.models_path') . $model;
        return new $model;
    }
    
    protected function systemModel($model)
    {
        $model = $this->config('app.system_path') . 'Models\\' . $model;
        return new $model;
    }
    
    protected function startSession()
    {
        Session::start();
        
        if (
            !isset($_SESSION['token']) ||
            (isset($_SESSION['token']) && (time() - $_SESSION['token_time']) > $this->config('app.token_expire_time'))
           ) {
            //Create new token
            $_SESSION['token'] = Strings::generateRandomString(32);
            $_SESSION['token_time'] = time();
        } else {
            //Extend token time, user is still active
            $_SESSION['token_time'] = time();
        }
    }
    
    protected function checkToken()
    {
        $this->startSession();
        
        if (isset($_POST['token']) && $_POST['token'] === $_SESSION['token']) {
            return true;
        } else {
            return false;
        }
    }
    
    protected function isLoggedIn()
    {
        $this->startSession();
        
        if (isset($_SESSION[$this->config('database.user_id_column')], $_SESSION['login_string'])) {
            $user = Database::select(
                "SELECT " . $this->config('database.user_id_column') . ", " .
                    $this->config('database.user_guid_column') .
                    " FROM " . $this->config('database.users_table') .
                    " WHERE " . $this->config('database.user_id_column') .
                    " = ? LIMIT 1;",
                [$_SESSION[$this->config('database.user_id_column')]]
            );
            
            if (count($user) === 1) {
                $login_check = hash(
                    'sha512',
                    $user[0][$this->config('database.user_id_column')] .
                        $_SERVER['HTTP_USER_AGENT'] .
                        $user[0][$this->config('app.user_guid_column')]
                );
                
                if ($login_check === $_SESSION['login_string']) {
                    return true;
                }
            }
        }
            
        return false;
    }
    
    protected function requiresLogin()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }
    }
    
    protected function redirect($page)
    {
        $this->redirecting = true;
        header('Location: ' . $this->config('app.base_href') . '/' . $page);
    }
    
    protected function logout()
    {
        Session::kill();
    }
    
    protected function view($view = [], $data = [], $raw = false)
    {
        $data['BASE_HREF'] = $this->config('app.base_href');
        $data['SITE'] = $this->config('site');
        $data['LOGGED_IN'] = $this->isLoggedIn();
        
        if ($this->isLoggedIn()) {
            $data['USER'] = $this->getUser();
        }
        
        //View directory
        $view_directory = $this->config('app.base_directory') . $this->config('app.views_directory');
        
        if (!$raw) {
            require_once $view_directory . 'template/header.php';
        }
        
        foreach ($view as $v) {
            require_once $view_directory . $v . '.php';
        }
        
        if (!$raw) {
            require_once $view_directory . 'template/footer.php';
        }
    }
    
    protected function config($key, $value = null)
    {
        if ($value === null) {
            return Config::get($key);
        } else {
            Config::set($key, $value);
        }
    }
    
    protected function requiresPermission($permission)
    {
        if (!$_SESSION[$this->config('app.user_session')]->hasPermission($permission)) {
            $this->redirect('permission-denied');
        }
    }
    
    protected function requiresToken($className)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$this->checkToken()) {
            $this->redirect('invalid-token/' . $className);
        }
    }
    
    public function isRedirecting()
    {
        return $this->redirecting;
    }
    
    protected function getUser()
    {
        return $_SESSION[$this->config('app.user_session')];
    }
}
