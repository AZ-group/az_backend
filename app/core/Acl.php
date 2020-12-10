<?php

namespace simplerest\core;

use simplerest\core\Model;
use simplerest\libs\DB;
use simplerest\libs\Factory;
use simplerest\libs\Debug;

abstract class Acl 
{
    protected $roles = [];
    protected $role_perms = [];
    protected $role_ids   = [];
    protected $role_names = [];
    protected $sp_permissions = []; 
    protected $current_role;
    protected $guest_name = 'guest';

    
    public function __construct() { 
        $this->config = Factory::config();
        $this->setup();
    }

    protected function setup(){        

    }

    public function addRole(string $role_name, $role_id = NULL) {
        
    }

    public function addRoles(Array $roles) {
        foreach ($roles as $role_name => $role_id) {

            $this->addRole($role_name, $role_id);
        }

        $this->current_role = null;
        return $this;
    }    	
        
    public function addUserRoles(Array $roles, $uid) {
       
    }


    public function addInherit(string $role_name, $to_role = null) {

    }

    public function addSpecialPermissions(Array $sp_permissions, $to_role = null) {
        
    }
    
    public function addResourcePermissions(string $table, Array $tb_permissions, $to_role = null) {
        if ($to_role != null){
            $this->current_role = $to_role;
        }

        if ($this->current_role == null){
            throw new \Exception("You can't inherit from undefined rol");
        }

        if (!isset($this->role_perms[$this->current_role]['tb_permissions'][$table])){
            $this->role_perms[$this->current_role]['tb_permissions'][$table] = [];
        }

        foreach ($tb_permissions as $tbp){
            switch ($tbp) {
                case 'show':
                    $this->role_perms[$this->current_role]['tb_permissions'][$table][] = 'show';
                break;
                case 'show_all':
                    $this->role_perms[$this->current_role]['tb_permissions'][$table][] = 'show_all';
                break;
                case 'list':
                    $this->role_perms[$this->current_role]['tb_permissions'][$table][] = 'list';
                break;
                case 'list_all':
                    $this->role_perms[$this->current_role]['tb_permissions'][$table][] = 'list_all';
                break;
                case 'read':
                    $this->role_perms[$this->current_role]['tb_permissions'][$table][] = 'show';
                    $this->role_perms[$this->current_role]['tb_permissions'][$table][] = 'list';
                break;
                case 'read_all':  // new
                    $this->role_perms[$this->current_role]['tb_permissions'][$table][] = 'show_all';
                    $this->role_perms[$this->current_role]['tb_permissions'][$table][] = 'list_all';
                break;

                case 'create':
                    $this->role_perms[$this->current_role]['tb_permissions'][$table][] = 'create';
                break;
                case 'update':
                    $this->role_perms[$this->current_role]['tb_permissions'][$table][] = 'update';
                break;
                case 'delete':
                    $this->role_perms[$this->current_role]['tb_permissions'][$table][] = 'delete';    
                break;
                case 'write':
                    $this->role_perms[$this->current_role]['tb_permissions'][$table][] = 'create';
                    $this->role_perms[$this->current_role]['tb_permissions'][$table][] = 'update';
                    $this->role_perms[$this->current_role]['tb_permissions'][$table][] = 'delete';
                break;

                default:
                    throw new \Exception("'$tbp' is not a valid resource permission");
            }
        }

        $this->role_perms[$this->current_role]['tb_permissions'][$table] = array_unique($this->role_perms[$this->current_role]['tb_permissions'][$table]);

        return $this;
    }


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

    public function getRoleName($role_id = NULL){
        if ($role_id == NULL){
            return $this->role_names;
        }

        foreach ($this->role_perms as $name => $r){
            if ($r['role_id'] == $role_id){
                return $name;
            }
        }

        throw new \Exception("Undefined role for role_id '$role_id'");
    }

    public function getRoleId(string $role_name){
        if (isset($this->role_perms[$role_name])){
            return $this->role_perms[$role_name]['role_id'];
        }

        throw new \Exception("Undefined role with name '$role_name'");
    }

    public function roleExists(string $role_name){
        return isset($this->role_perms[$role_name]);
    }

    // sería mucho más rápido si pudiea acceder como $sp_permissions['perm']['role']
    // solo sería hacer un isset($sp_permissions['perm']['role'])
    public function hasSpecialPermission(string $perm, Array $role_names){
        if (!in_array($perm, $this->sp_permissions)){
            throw new \InvalidArgumentException("hasSpecialPermission : invalid permission '$perm'");    
        }

        foreach ($role_names as $r_name){
            if (!isset($this->role_perms[$r_name])){
                //var_dump($this->role_perms);
                throw new \InvalidArgumentException("hasSpecialPermission : invalid role name '$r_name'");
            }

            if (in_array($perm, $this->role_perms[$r_name]['sp_permissions'])){
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
        if (!in_array($perm, ['show', 'show_all', 'list', 'list_all', 'create', 'update', 'delete'])){
            throw new \InvalidArgumentException("hasResourcePermission : invalid permission '$perm'");    
        }

        foreach ($role_names as $r_name){
            if (!isset($this->role_perms[$r_name])){
                throw new \InvalidArgumentException("hasResourcePermission : invalid role name '$r_name'");
            }

            if (isset($this->role_perms[$r_name]['tb_permissions'][$resource])){
                if (in_array($perm, $this->role_perms[$r_name]['tb_permissions'][$resource])){
                    return true;
                }
            }  
        }
        
        return false;
    }

    public function getResourcePermissions(string $role, string $resource, $op_type = null){
        $ops = [
            'read'  => ['show', 'list', 'show_all', 'list_all'],
            'write' => ['create', 'update', 'delete']
        ];

        if ($op_type != null && ($op_type != 'read' && $op_type != 'write')){
            throw new \InvalidArgumentException("getResourcePermissions : '$op_type' is not a valid value for op_type");
        }
        
        if (isset($this->role_perms[$role]['tb_permissions'][$resource])){
            if ($op_type != null){
                return array_intersect($this->role_perms[$role]['tb_permissions'][$resource], $ops[$op_type]);
            }

            return $this->role_perms[$role]['tb_permissions'][$resource];
        }

        return [];
    }    

    public function getRolePermissions(){
        return $this->role_perms;
    }

    function fetchRoles($uid) {
       
    }

    function fetchTbPermissions($uid) {
        
    }

    function fetchSpPermissions($uid) {
       
    }

    function fetchPermissions($uid) : Array { 
        return [
                'tb' => $this->fetchTbPermissions($uid), 
                'sp' => $this->fetchSpPermissions($uid) 
        ];
    }

    function getUserIdFromApiKey($api_key){
        
    }

    public function getTbPermissions(string $table = NULL){
        if (empty($this->permissions)){
            return NULL;
        }

        $tb_perms = $this->permissions['tb'];

        if ($table == NULL)
            return $tb_perms;

        if (!isset($tb_perms[$table]))
            return NULL;

        return $tb_perms[$table];
    }

    public function isGuest(){
        return $this->roles == [$this->getGuest()];
    }

    public function isRegistered(){
        return !$this->isGuest();
    }

    public function getRoles(){
        return $this->roles;
    }

    public function hasRole(string $role){
        return in_array($role, $this->roles);
    }

    public function hasAnyRole(array $authorized_roles){
        $authorized = false;
        foreach ((array) $this->roles as $role)
            if (in_array($role, $authorized_roles))
                $authorized = true;

        return $authorized;        
    }


}

