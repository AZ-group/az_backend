<?php

namespace simplerest\core;

class Acl 
{
    protected $roles    = [];
    protected $role_ids = [];
    protected $role_names = [];
    protected $current_role;
    protected $guest_name = 'guest';
    protected $sp_permissions_allowed = [
        'read_all',
        'write_all',
        'read_all_folders',
        'write_all_folders',
        'read_all_trashcan',
        'write_all_trashcan',
        'lock',
        'transfer',
        'impersonate',
        'fill_all',
        'grant'
    ]; 

    public function __construct() { 
        // $this->config = include CONFIG_PATH . 'config.php';
    }

    public function addRole(string $role_name, $role_id) {
       
        if (in_array($role_id, $this->role_ids)){
            throw new \Exception("Role id '$role_id' can not be repetead. It should be UNIQUE.");
        }

        if (in_array($role_name, $this->role_names)){
            throw new \Exception("Role name '$role_name' can not be repetead. It should be UNIQUE.");
        }

        if ($role_name == 'guest'){
            $this->guest_name = 'guest';
        }

        $this->role_ids[]   = $role_id;
        $this->role_names[] = $role_name;
        
        $this->roles[$role_name] = [
                            'role_id' => $role_id,
                            'sp_permissions' => [],
                            'tb_permissions' => []
        ];

        $this->current_role = $role_name; 

        return $this;
    }

    public function addRoles(Array $roles) {
        foreach ($roles as $role_name => $role_id) {

            $this->addRole($role_name, $role_id);
        }

        $this->current_role = null;
        return $this;
    }    	
	    

    public function addInherit(string $role_name, $to_role = null) {
        if ($to_role != null){
            $this->current_role = $to_role;
        }

        if ($this->current_role == null){
            throw new \Exception("You can't inherit from undefined rol");
        }

        if (!isset($this->roles[$this->current_role]['sp_permissions'])){
            $this->roles[$this->current_role]['sp_permissions'] = [];
        } else {
            if (!empty($this->roles[$this->current_role]['sp_permissions'])){
                throw new \Exception("You can't inherit permissions from '$role_name' when you have already permissions for '".$this->current_role."'");
            }
        }

        if (!isset($this->roles[$this->current_role]['tb_permissions'])){
            $this->roles[$this->current_role]['tb_permissions'] = [];
        } else {
            if (!empty($this->roles[$this->current_role]['tb_permissions'])){
                throw new \Exception("You can't inherit permissions from '$role_name' when you have already permissions for '$this->current_role'");
            }
        }

        if (!empty($this->roles[$this->current_role]['sp_permissions']) || !empty($this->roles[$this->current_role]['sp_permissions'])){
            throw new \Exception("You can't inherit permissions from '$role_name' when you have already permissions for '$this->current_role'");
        }

        if (!isset($this->roles[$role_name]) || !isset($this->roles[$role_name]['sp_permissions']) || !isset($this->roles[$role_name]['tb_permissions']) ){
            throw new \Exception("[ Inherit ] Role '$role_name' not found");
        }

        $this->roles[$this->current_role]['sp_permissions'] = $this->roles[$role_name]['sp_permissions'];
        $this->roles[$this->current_role]['tb_permissions'] = $this->roles[$role_name]['tb_permissions'];

        return $this;
    }

    public function addSpecialPermissions(Array $sp_permissions, $to_role = null) {
        if ($to_role != null){
            $this->current_role = $to_role;
        }

        if ($this->current_role == null){
            throw new \Exception("You can't inherit from undefined rol");
        }

        // chequear que $sp_permissions no se cualquier cosa
        foreach ($sp_permissions as $spp){
            if (!in_array($spp, $this->sp_permissions_allowed)){
                throw new \Exception("'$spp' is not a valid special permission");
            }

            // caso especial de un pseudo-permiso
            if ($spp == 'grant'){
                $this->addResourcePermissions('permissions', ['read', 'write']);
                //return $this;
            }
        }
        
        $this->roles[$this->current_role]['sp_permissions'] = array_unique(array_merge($this->roles[$this->current_role]['sp_permissions'], $sp_permissions));
     
        return $this;
    }
    
    public function addResourcePermissions(string $table, Array $tb_permissions, $to_role = null) {
        if ($to_role != null){
            $this->current_role = $to_role;
        }

        if ($this->current_role == null){
            throw new \Exception("You can't inherit from undefined rol");
        }

        if (!isset($this->roles[$this->current_role]['tb_permissions'][$table])){
            $this->roles[$this->current_role]['tb_permissions'][$table] = [];
        }

        foreach ($tb_permissions as $tbp){
            switch ($tbp) {
                case 'show':
                    $this->roles[$this->current_role]['tb_permissions'][$table][] = 'show';
                break;
                case 'list':
                    $this->roles[$this->current_role]['tb_permissions'][$table][] = 'list';
                break;
                case 'read':
                    $this->roles[$this->current_role]['tb_permissions'][$table][] = 'show';
                    $this->roles[$this->current_role]['tb_permissions'][$table][] = 'list';
                break;
            
                case 'create':
                    $this->roles[$this->current_role]['tb_permissions'][$table][] = 'create';
                break;
                case 'update':
                    $this->roles[$this->current_role]['tb_permissions'][$table][] = 'update';
                break;
                case 'delete':
                    $this->roles[$this->current_role]['tb_permissions'][$table][] = 'delete';    
                break;
                case 'write':
                    $this->roles[$this->current_role]['tb_permissions'][$table][] = 'create';
                    $this->roles[$this->current_role]['tb_permissions'][$table][] = 'update';
                    $this->roles[$this->current_role]['tb_permissions'][$table][] = 'delete';
                break;

                default:
                    throw new \Exception("'$tbp' is not a valid resource permission");
            }
        }

        $this->roles[$this->current_role]['tb_permissions'][$table] = array_unique($this->roles[$this->current_role]['tb_permissions'][$table]);

        return $this;
    }

    
    /*
        Methods which could be static
    */

    public function setGuest(string $guest_name){
        if (!in_array($guest_name, $this->role_names)){
            throw new \Exception("Please add the rol '$guest_name' *before* to set as guest role to avoid mistakes");
        }

        $this->guest_name = $guest_name;
        return $this;
    }

    public function getGuest(){
        if ($this->guest_name == NULL){
            throw new \Exception("Undefined guest rol in ACL");
        }

        return $this->guest_name;
    }

    public function getRoleName($role_id){
        foreach ($this->roles as $name => $r){
            if ($r['role_id'] == $role_id){
                return $name;
            }
        }

        throw new \Exception("Undefined role with id '$role_name'");
    }

    public function getRoleId(string $role_name){
        if (isset($this->roles[$role_name])){
            return $this->roles[$role_name]['role_id'];
        }

        throw new \Exception("Undefined role with name '$role_name'");
    }

    public function roleExists(string $role_name){
        return isset($this->roles[$role_name]);
    }

    // sería mucho más rápido si pudiea acceder como $sp_permissions['perm']['role']
    // solo sería hacer un isset($sp_permissions['perm']['role'])
    public function hasSpecialPermission(string $perm, Array $role_names){
        if (!in_array($perm, $this->sp_permissions_allowed)){
            throw new \InvalidArgumentException("hasSpecialPermission : invalid permission '$perm'");    
        }

        foreach ($role_names as $r_name){
            if (!isset($this->roles[$r_name])){
                //var_dump($this->roles);
                throw new \InvalidArgumentException("hasSpecialPermission : invalid role name '$r_name'");
            }

            if (in_array($perm, $this->roles[$r_name]['sp_permissions'])){
                return true;
            } 
        }
        
        return false;
    }

    /*
        @param $perm string show|list|create|update|delete
        @param $role_names Array 
        @param $resource string
    */
    public function hasResourcePermission(string $perm, Array $role_names, string $resource){
        if (!in_array($perm, ['show', 'list', 'create', 'update', 'delete'])){
            throw new \InvalidArgumentException("hasResourcePermission : invalid permission '$perm'");    
        }

        foreach ($role_names as $r_name){
            if (!isset($this->roles[$r_name])){
                throw new \InvalidArgumentException("hasResourcePermission : invalid role name '$r_name'");
            }

            if (isset($this->roles[$r_name]['tb_permissions'][$resource])){
                if (in_array($perm, $this->roles[$r_name]['tb_permissions'][$resource])){
                    return true;
                }
            }  
        }
        
        return false;
    }

    /*
        @param $perm string read|write|show|list|create|update|delete
        @param $role_names Array 
        @param $resource string
    */
    public function isAllowed(string $op_type, Array $role_names, $resource)
    {        
        switch ($op_type) {
            case 'show':
            case 'list':
                return $this->hasSpecialPermission('read_all', $role_names) || $this->hasResourcePermission($op_type, $role_names, $resource);
            break;

            case 'read':
                // puede ser ineficiente
                return $this->hasSpecialPermission('read_all', $role_names) || ($this->hasResourcePermission('show', $role_names, $resource) && $this->hasResourcePermission('list', $role_names, $resource));
            break;
        
            case 'create':
            case 'update':
            case 'delete':    
                return $this->hasSpecialPermission('write_all', $role_names) || $this->hasResourcePermission($op_type, $role_names, $resource);
            break;

            case 'write':
                // puede ser ineficiente
                return $this->hasSpecialPermission('write_all', $role_names) || ($this->hasResourcePermission('create', $role_names, $resource) && $this->hasResourcePermission('update', $role_names, $resource || $this->hasResourcePermission('delete', $role_names, $resource )));
            break;

            default:
                throw new \Exception("'$op_type' is not a valid permission type");
        }
        
    }

    public function getResourcePermissions(string $role, string $resource, $op_type = null){
        $ops = [
            'read'  => ['show', 'list'],
            'write' => ['create', 'update', 'delete']
        ];

        if ($op_type != null && ($op_type != 'read' && $op_type != 'write')){
            throw new \InvalidArgumentException("getResourcePermissions : '$op_type' is not a valid value for op_type");
        }
        
        if (isset($this->roles[$role]['tb_permissions'][$resource])){
            if ($op_type != null){
                return array_intersect($this->roles[$role]['tb_permissions'][$resource], $ops[$op_type]);
            }

            return $this->roles[$role]['tb_permissions'][$resource];
        }

        return [];
    }    

    public function getRolePermissions(){
        return $this->roles;
    }

   

}


// Testing


/*
$acl = new Acl();

$acl
->addRole('guest', -1)
->addResourcePermissions('products', ['show'])
->addResourcePermissions('foo', ['write'])
->addResourcePermissions('bar', ['read'])
//->setGuest('guest')

->addRole('basic', 2)
->addInherit('guest')
->addResourcePermissions('products', ['list', 'update'])
->addResourcePermissions('foo', ['show'])
->addResourcePermissions('bar', ['list'])

->addRole('regular', 3)
->addInherit('guest')
->addResourcePermissions('products', ['read', 'write'])
->addResourcePermissions('foo', ['read', 'update'])

->addRole('admin', 100)
->addInherit('guest')
->addSpecialPermissions(['read_all', 'write_all'])

->addRole('superadmin', 500)
->addInherit('admin')
->addSpecialPermissions(['lock', 'fill_all']);

//var_dump($acl->getRolePermissions());
//var_dump($acl->getAcl());
//var_dump($acl->getRolePermissions());
//var_dump($acl->hasResourcePermission('read', ['guest', 'admin'], 'xxx'));
//var_dump($acl->getResourcePermissions('basic', 'products', 'read'));
//var_dump($acl->hasResourcePermission('show', ['regular', 'basic'], 'foo'));
var_dump($acl->isAllowed('write', ['admin'], 'baz'));

//var_dump($acl->hasSpecialPermission('lock', ['superadmin', 'admin']));
*/


