<?php
/**
 * @author captain-redbeard
 * @since 20/01/17
 */
namespace Redbeard\Crew\Models;

use Redbeard\Crew\Config;
use Redbeard\Crew\Session;
use Redbeard\Crew\Tracking;
use Redbeard\Crew\Database;
use Redbeard\Crew\ChangeControl;
use Redbeard\Crew\Models\Role;
use Redbeard\Crew\Utils\Dates;
use Redbeard\Crew\Utils\Strings;
use Redbeard\Crew\Utils\Validator;
use Redbeard\Crew\ThirdParty\Google2FA;
use Endroid\QrCode\QrCode;

class User
{
    public $id = null;
    public $guid = null;
    public $username = null;
    public $email = null;
    public $first_name = null;
    public $last_name = null;
    public $timezone = null;
    public $secret_key = null;
    public $activation = null;
    public $mfa_enabled = null;
    public $modified = null;
    public $made_date = null;
    public $roles = null;
    public $access = null;
    
    public function __construct(
        $id = null,
        $guid = null,
        $username = null,
        $email = null,
        $first_name = null,
        $last_name = null,
        $timezone = null,
        $secret_key = null,
        $activation = null,
        $mfa_enabled = null,
        $modified = null,
        $made_date = null,
        $access = null
    )
    {
        $this->id = $id;
        $this->guid = $guid;
        $this->username = $username;
        $this->email = $email;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->timezone = $timezone;
        $this->secret_key = $secret_key;
        $this->activation = $activation;
        $this->mfa_enabled = $mfa_enabled;
        $this->modified = $modified;
        $this->made_date = $made_date;
        $this->access = $access;
        $this->roles = $this->initRoles($id);
    }
    
    public function getUser($user_id)
    {
        //Define
        $user = null;
        
        //Get details
        $user_details = Database::select(
            "SELECT user_id, user_guid, username, email, first_name, last_name, timezone, secret_key, activation,
            mfa_enabled, modified, made_date,
                IFNULL((SELECT allowed FROM user_access WHERE user_guid = users.user_guid AND cancelled = 0), 1) AS access
            FROM users
            WHERE user_id = ?;",
            [$user_id]
        );
        
        $user_details = $user_details[0];
        $user = new User(
            $user_details['user_id'],
            $user_details['user_guid'],
            htmlspecialchars($user_details['username']),
            htmlspecialchars($user_details['email']),
            htmlspecialchars($user_details['first_name']),
            htmlspecialchars($user_details['last_name']),
            htmlspecialchars($user_details['timezone']),
            htmlspecialchars($user_details['secret_key']),
            htmlspecialchars($user_details['activation']),
            $user_details['mfa_enabled'],
            $user_details['modified'],
            $user_details['made_date'],
            htmlspecialchars($user_details['access'])
        );
           
        //Return
        return $user;
    }
    
    protected function initRoles($user_id)
    {
        //Define
        $roles = [];
        
        //Get data
        $roles_data = Database::select(
            "SELECT t1.role_id, t2.role_name
            
            FROM user_roles as t1
            JOIN roles as t2 ON t1.role_id = t2.role_id
            
            WHERE t1.user_id = ?;",
            [$user_id]
        );
        
        foreach ($roles_data as $role) {
            $roles[$role['role_name']] = new Role($role['role_id'], $role['role_name']);
        }
        
        //Return
        return $roles;
    }
    
    public function register($username, $password, $confirm_password, $timezone = 'UTC', $role_id = -1, $set_session = true)
    {
        $username = Strings::cleanInput($username);
        $timezone = Strings::cleanInput($timezone, 1);
        
        $validUsername = Validator::validateLength('Username', $username, 4, 256);
        $validPassword = Validator::validateLength('Password', $password, 8, 256);
        
        if ($validUsername !== true) {
            return $validUsername;
        }
        
        if ($validPassword !== true) {
            return $validPassword;
        }
        
        if ($password !== $confirm_password) {
            return 'Passwords don\'t match.';
        }
        
        if ($timezone === -1) {
            return 'You must select a Timezone.';
        }
        
        if (!isset($error)) {
            $existing = Database::select("SELECT user_id FROM users WHERE username = ?;", [$username]);
            if (count($existing) > 0) {
                return 'Username is already taken.';
            }
            
            $password = password_hash($password, PASSWORD_DEFAULT, ['cost' => Config::get('app.password_cost')]);
            $guid = Strings::generateRandomString(32);
            $activation = Strings::generateRandomString(32);
            $secretkey = Google2FA::generateSecretKey();
            
            //Insert user
            $user_id = Database::insert(
                "INSERT INTO users (user_guid, username, email, password, secret_key, activation, timezone, modified) 
                    VALUES (?,?,?,?,?,?,?,NOW());",
                [
                    $guid,
                    $username,
                    $username,
                    $password,
                    $secretkey,
                    $activation,
                    $timezone
                ]
            );
            
            if ($user_id > -1) {
                //Insert user role
                Database::insert(
                    "INSERT into user_roles (user_id, role_id, modified) VALUES (?,?,NOW());",
                    [
                        $user_id,
                        $role_id !== -1 ? $role_id : Config::get('app.user_role')
                    ]
                );
                
                if ($set_session) {
                    Session::start();
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['login_string'] = hash('sha512', $user_id . $_SERVER['HTTP_USER_AGENT'] . $guid);
                    $_SESSION[Config::get('app.user_session')] = $this->getUser($user_id);
                }
                
                return true;
            } else {
                return 'Failed to create user, contact support.';
            }
        }
    }
    
    public function login($username, $password, $mfa = null)
    {
        $username = Strings::cleanInput($username);
        
        $validUsername = Validator::validateLength('Username', $username, 4, 256);
        $validPassword = Validator::validateLength('Password', $password, 8, 256);
        
        if ($validUsername !== true) {
            return $validUsername;
        }
        
        if ($validPassword !== true) {
            return $validPassword;
        }
        
        $existing = Database::select(
            "SELECT user_id, user_guid, password, mfa_enabled, secret_key,
                IFNULL((SELECT allowed FROM user_access WHERE user_guid = users.user_guid AND cancelled = 0), 1) AS access
            FROM users
            WHERE username = ?;",
            [$username]
        );
        
        if (count($existing) > 0) {
            $attempts = Database::select(
                "SELECT made_date FROM login_attempts WHERE user_id = ? 
                    AND made_date > DATE_SUB(NOW(), INTERVAL 2 HOUR);",
                [$existing[0]['user_id']]
            );
            
            if (count($attempts) < Config::get('app.max_login_attempts')) {
                if (!$this->isAllowed($existing[0]['access'], Tracking::getRemoteAddress())) {
                    return 'IP Address is not allowed.';
                }
                
                if (password_verify($password, $existing[0]['password'])) {
                    if (password_needs_rehash(
                        $existing[0]['password'],
                        PASSWORD_DEFAULT,
                        ['cost' => Config::get('app.password_cost')]
                    )) {
                        $newhash = password_hash(
                            $password,
                            PASSWORD_DEFAULT,
                            ['cost' => Config::get('app.password_cost')]
                        );
                            
                        Database::update(
                            "UPDATE users SET password = ?, modified = now() WHERE user_id = ?;",
                            [
                                $newhash,
                                $existing[0]['user_id']
                            ]
                        );
                    }
                    
                    if ($existing[0]['mfa_enabled']) {
                        $rmfa = Google2FA::verifyKey($existing[0]['secret_key'], $mfa);
                        
                        if (!$rmfa) {
                            Database::update(
                                "INSERT INTO login_attempts(user_id, made_date) VALUES (?, NOW());",
                                [$existing[0]['user_id']]
                            );
                            
                            return 'MFA Failed.';
                        }
                    }
                    
                    Session::start();
                    $_SESSION['user_id'] = $existing[0]['user_id'];
                    $_SESSION['login_string'] = hash(
                        'sha512',
                        $existing[0]['user_id'] . $_SERVER['HTTP_USER_AGENT'] . $existing[0]['user_guid']
                    );
                    $_SESSION[Config::get('app.user_session')] = $this->getUser($_SESSION['user_id']);
                    
                    return true;
                } else {
                    Database::update(
                        "INSERT INTO login_attempts(user_id, made_date) VALUES (?, NOW());",
                        [$existing[0]['user_id']]
                    );
                    return 'Incorrect password.';
                }
            } else {
                return 'To many login attempts, try again later.';
            }
        } else {
            return 'User not found.';
        }
    }
    
    public function update($username, $first_name, $last_name, $role_id = -1, $timezone = 'UTC', $set_session = true)
    {
        $username = Strings::cleanInput($username);
        $first_name = Strings::cleanInput($first_name, 2);
        $last_name = Strings::cleanInput($last_name, 2);
        $timezone = Strings::cleanInput($timezone, 1);
        
        $valid_username = Validator::validateLength('Username', $username, 4, 256);
        $valid_first_name = Validator::validateLength('First Name', $first_name, 1, 128);
        $valid_last_name = Validator::validateLength('Last Name', $last_name, 1, 128);
        
        if ($valid_username !== true) {
            return $valid_username;
        }
        
        if ($valid_first_name !== true) {
            return $valid_first_name;
        }
        
        if ($valid_last_name !== true) {
            return $valid_last_name;
        }
        
        $existing = Database::select(
            "SELECT user_id, username FROM users WHERE username = ?;",
            [$username]
        );
        
        if (count($existing) > 0 && $existing[0]['username'] !== $this->username) {
            return 'Username already taken.';
        }
        
        if (Database::updateWithChangeControl(
            'Modified User',
            'users',
            'user_guid',
            $this->guid,
            "UPDATE users SET username = ?, first_name = ?, last_name = ?, timezone = ? WHERE user_guid = ?;",
            [
                $username,
                $first_name,
                $last_name,
                $timezone,
                $this->guid
            ]
        )) {
                //Update user role
                Database::update(
                    "UPDATE user_roles SET role_id = ?, modified = NOW()
                    WHERE user_id = ?;",
                    [
                        $role_id !== -1 ? $role_id : Config::get('app.cms_role'),
                        $existing[0]['user_id']
                    ]
                );
                
                if ($set_session) {
                    $_SESSION[Config::get('app.user_session')] = $this->getUser($this->user_id);
                }
                
                return true;
        } else {
            return 'Failed to update user, contact support.';
        }
    }
    
    public function enableMfa($code1, $code2)
    {
        $code1 = Strings::cleanInput($code1, 2);
        $code2 = Strings::cleanInput($code2, 2);
        
        if ($code1 === null || $code2 === null) {
            return 'You must provide two consecutive codes.';
        }
        
        $result1 = Google2FA::verifyKey($this->secret_key, $code1);
        $result2 = Google2FA::verifyKey($this->secret_key, $code2);
        
        if ($result1 && $result2) {
            if (Database::update(
                "UPDATE users SET mfa_enabled = 1 WHERE user_id = ? AND user_guid = ?;",
                [
                    $this->id,
                    $this->guid
                ]
            )) {
                $this->mfa_enabled = 1;
                return true;
            }
        } else {
            return 'Invalid codes.';
        }
    }
    
    public function disableMfa()
    {
        Database::update(
            "UPDATE users SET mfa_enabled = 0
            WHERE user_id = ?
            AND user_guid = ?;",
            [
                $this->id,
                $this->guid
            ]
        );
        
        $this->mfa_enabled = 0;
        
        return true;
    }
    
    public function resetPasswordRequest()
    {
        //Update database
        Database::update(
            "UPDATE users SET password_reset = 1, password_reset_request_date = NOW()
            WHERE user_id = ?
            AND user_guid = ?;",
            [
                $this->id,
                $this->guid
            ]
        );
        
        //Return
        return true;
    }
    
    public function resetPassword($password, $new_password, $confirm_new_password, $require_password = true)
    {
        if ($new_password !== $confirm_new_password) {
            return 'Passwords don\'t match.';
        }
        
        $validPassword = Validator::validateLength('Password', $new_password, 8, 256);
        
        if ($validPassword !== true) {
            return $validPassword;
        }
        
        $user = Database::select(
            "SELECT user_id, user_guid, password FROM users WHERE user_id = ? AND user_guid = ?;",
            [
                $this->id,
                $this->guid
            ]
        );

        if (count($user) > 0) {
            if (password_verify($password, $user[0]['password']) || !$require_password) {
                $newpass = password_hash(
                    $new_password,
                    PASSWORD_DEFAULT,
                    ['cost' => Config::get('app.password_cost')]
                );
                
                if (Database::update(
                    "UPDATE users SET password = ?, password_reset = 0, password_reset_date = NOW() WHERE user_id = ? AND user_guid = ?;",
                    [
                        $newpass,
                        $this->id,
                        $this->guid
                    ]
                )) {
                    return true;
                } else {
                    return 'Failed to reset password, contact support.';
                }
            } else {
                return 'Incorrect password';
            }
        } else {
            return 'User not found.';
        }
    }
    
    //Check if the ip is within the allowed range
    public function isAllowed($allowed, $ip_address)
    {
        if (Strings::contains($ip_address, $allowed) || $allowed == 1) {
            return true;
        } else {
            //Check further
            $ips = explode(',', $allowed);
            
            //Strip the dots to do a range compare
            $ip_str = str_replace('.', '', $ip_address);
            
            //Check each IP in the exploded range
            for ($i = 0; $i < count($ips); $i++) {
                if (strpos($ips[$i], '-') !== false) {
                    //Break down the IP range
                    $ip2 = explode('-', $ips[$i]);
                    
                    //Is the IP within the specified range
                    if ($ip_str >= str_replace('.', '', $ip2[0]) && $ip_str <= str_replace('.', '', $ip2[1])) {
                        return true;
                    }
                }
            }
            
            //Failed to find the IP
            return false;
        }
    }
    
    public function getQrCode()
    {
        $qrCode = new QrCode();
        $qrCode
            ->setText("otpauth://totp/" .
                      Config::get('site.name') . ":" .
                      $this->username . "?secret=" .
                      $this->secret_key . "&issuer=" .
                      Config::get('site.name'))
            ->setSize(200)
            ->setPadding(0)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setImageType(QrCode::IMAGE_TYPE_PNG)
        ;
        
        return $qrCode;
    }
    
    public function hasRole($role_name)
    {
        return isset($this->roles[$role_name]);
    }
    
    public function hasPermission($perm)
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($perm)) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getHighestRole()
    {
        $return_role = null;
        
        foreach ($this->roles as $role) {
            if ($return_role === null || $role->id <= $return_role->id) {
                $return_role = $role;
            }
        }
        
        return $return_role;
    }
    
    public function getName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    
    public function getModified()
    {
        return Dates::niceTime($this->modified);
    }
    
    public function getMadeDate()
    {
        return Dates::convertTime($this->made_date);
    }
    
    public function hasAccess()
    {
        return $this->access;
    }
}
