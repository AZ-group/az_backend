<?php

namespace boctulus\basic_acl;

use simplerest\libs\DB;
use simplerest\libs\Factory;

class Acl extends \simplerest\core\Acl
{
 
    protected function setup(){        
        // get all available sp_permissions
        $this->sp_permissions = DB::table('sp_permissions')->pluck('name');

        // get all available roles
        $this->roles = DB::table('roles')->get();

        foreach($this->roles as $rr){
            $this->role_names[] = $rr['name'];
        }
    }

    public function getSpPermissions(){
        return $this->sp_permissions;
    }

    public function addRole(string $role_name, $role_id = NULL) {
        $create = true;

        if (in_array($role_id, $this->role_ids)){
            $create = false;

            foreach ($this->roles as $rr){
                if ($rr['id'] == $role_id && $rr['name'] != $role_name){
                    throw new \Exception("Role id '$role_id' can not be repetead. Trying to assign to '$role_name' but it was used for '{$rr['name']}' and it should be UNIQUE.");      
                }
            }
        }

        if (in_array($role_name, $this->role_names)){
            $create = false;
            
            foreach ($this->roles as $rr){
                if ($rr['id'] != $role_id && $rr['name'] == $role_name){
                    if ($role_id != NULL) {
                        throw new \Exception("Role name '$role_name' can not be repetead. Trying to assign to id '$role_id' but it was used for '{$rr['id']}' and it should be UNIQUE.");  
                    }
                       
                }
            }
        }

        if ($role_name == 'guest'){
            $this->guest_name = 'guest';
        }

        if ($create){
            $role_id = DB::table('roles')->create([
                'id'   => $role_id,
                'name' => $role_name
            ]);
        }
        
        $this->role_ids[]   = $role_id;
        $this->role_names[] = $role_name;
        
        $this->role_perms[$role_name] = [
                            'role_id' => $role_id,
                            'sp_permissions' => [],
                            'tb_permissions' => []
        ];

        $this->current_role = $role_name; 

        return $this;
    }

    public function addUserRoles(Array $roles, $uid) {
        foreach ($roles as $role) {
            $role_id = $this->getRoleId($role);

            if ($role_id == null){
                throw new \Exception("Role $role is invalid");
            }
            
            // lo ideal es validar los roles y obtener los ids para luego hacer un "INSERT in bulk"
            $ur_id = DB::table('user_roles')
            ->where(['id' => $uid])
            ->create(['user_id' => $uid, 'role_id' => $role_id]);

            if (empty($ur_id))
                throw new \Exception("Error registrating user role $role");             
        }         
    }

    public function addInherit(string $role_name, $to_role = null) {
        if ($to_role != null){
            $this->current_role = $to_role;
        }

        if ($this->current_role == null){
            throw new \Exception("You can't inherit from undefined rol");
        }

        if (!isset($this->role_perms[$this->current_role]['sp_permissions'])){
            $this->role_perms[$this->current_role]['sp_permissions'] = [];
        } else {
            if (!empty($this->role_perms[$this->current_role]['sp_permissions'])){
                throw new \Exception("You can't inherit permissions from '$role_name' when you have already permissions for '".$this->current_role."'");
            }
        }

        if (!isset($this->role_perms[$this->current_role]['tb_permissions'])){
            $this->role_perms[$this->current_role]['tb_permissions'] = [];
        } else {
            if (!empty($this->role_perms[$this->current_role]['tb_permissions'])){
                throw new \Exception("You can't inherit permissions from '$role_name' when you have already permissions for '$this->current_role'");
            }
        }

        if (!empty($this->role_perms[$this->current_role]['sp_permissions']) || !empty($this->role_perms[$this->current_role]['sp_permissions'])){
            throw new \Exception("You can't inherit permissions from '$role_name' when you have already permissions for '$this->current_role'");
        }

        if (!isset($this->role_perms[$role_name]) || !isset($this->role_perms[$role_name]['sp_permissions']) || !isset($this->role_perms[$role_name]['tb_permissions']) ){
            throw new \Exception("[ Inherit ] Role '$role_name' not found");
        }

        $this->role_perms[$this->current_role]['sp_permissions'] = $this->role_perms[$role_name]['sp_permissions'];
        $this->role_perms[$this->current_role]['tb_permissions'] = $this->role_perms[$role_name]['tb_permissions'];

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
            if (!in_array($spp, $this->sp_permissions)){
                throw new \Exception("'$spp' is not a valid special permission");
            }

            // caso especial de un pseudo-permiso
            if ($spp == 'grant'){
                $this->addResourcePermissions('tb_permissions', ['read', 'write']);
                //return $this;
            }
        }
        
        $this->role_perms[$this->current_role]['sp_permissions'] = array_unique(array_merge($this->role_perms[$this->current_role]['sp_permissions'], $sp_permissions));
     
        return $this;
    }

    function fetchRoles($uid) : Array {
        $rows = DB::table('user_roles')
        ->assoc()
        ->where(['user_id', $uid])
        ->select(['role_id as role'])
        ->get();	

        $acl = Factory::acl();

        $roles = [];
        if (count($rows) != 0){
            foreach ($rows as $row){
                $roles[] = $acl->getRoleName($row['role']);
            }
        }

        return $roles;
    }

    function fetchTbPermissions($uid) : Array {
        $_permissions = DB::table('user_tb_permissions')
        ->assoc()
        ->select([  
                    'tb', 
                    'can_list_all as la',
                    'can_show_all as ra', 
                    'can_list as l',
                    'can_show as r',
                    'can_create as c',
                    'can_update as u',
                    'can_delete as d'])
        ->where(['user_id' => $uid])
        ->get();

        $perms = [];
        foreach ((array) $_permissions as $p){
            $tb = $p['tb'];
            $perms[$tb] =  $p['la'] * 64 + $p['ra'] * 32 +  $p['l'] * 16 + $p['r'] * 8 + $p['c'] * 4 + $p['u'] * 2 + $p['d'];
        }

        return $perms;
    }

    function fetchSpPermissions($uid) : Array {
        $perms = DB::table('user_sp_permissions')
        ->assoc()
        ->where(['user_id' => $uid])
        ->join('sp_permissions', 'user_sp_permissions.sp_permission_id', '=', 'sp_permissions.id')
        ->pluck('name');

        return $perms ?? [];
    }

    function getUserIdFromApiKey($api_key){
        $uid = DB::table('api_keys')
        ->where(['value', $api_key])
        ->value('user_id');

        return $uid;
    }



}

