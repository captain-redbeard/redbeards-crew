<?php
/**
 * @author captain-redbeard
 * @since 20/01/17
 */
namespace Redbeard\Crew\Models;

use Redbeard\Crew\Config;
use Redbeard\Crew\Database;

class Role
{
    public $id = null;
    public $name = null;
    public $permissions = null;
    
    public function __construct($id = null, $name = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->permissions = $this->setRolePermissions($id);
    }
    
    public function getRoles()
    {
        //Define array
        $roles = [];
        
        //Get data
        $role_data = Database::select(
            "SELECT role_id, role_name
            FROM roles;",
            []
        );
        
        foreach ($role_data as $role) {
            if ($role['role_id'] >= $_SESSION[Config::get('app.user_session')]->getHighestRole()->id &&
                $role['role_id'] < Config::get('app.user_role')
               ) {
                array_push(
                    $roles,
                    new Role(
                        $role['role_id'],
                        $role['role_name']
                    )
                );
            }
        }
        
        //Return
        return $roles;
    }
    
    public function setRolePermissions($role_id)
    {
        //Define
        $permissions = [];
        
        //Get data
        $role_perms = Database::select(
            "SELECT t2.perm_id, t2.perm_desc
            FROM role_perm as t1
            JOIN permissions as t2 ON t1.perm_id = t2.perm_id
            WHERE t1.role_id = ?;",
            [$role_id]
        );
        
        foreach ($role_perms as $perm) {
            $permissions[$perm['perm_desc']] = $perm['perm_id'];
        }
        
        //Return
        return $permissions;
    }
    
    public function hasPermission($permission)
    {
        return isset($this->permissions[$permission]);
    }
}
