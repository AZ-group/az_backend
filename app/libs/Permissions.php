<?php

namespace simplerest\libs;

class Permissions 
{
    static function fetchRoles($uid) : Array {
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

    static function fetchTbPermissions($uid) : Array {
        $_permissions = DB::table('user_tb_permissions')
        ->assoc()
        ->select(['tb', 'can_list_all as la', 'can_show_all as ra', 'can_list as l', 'can_show as r', 'can_create as c', 'can_update as u', 'can_delete as d'])
        ->where(['user_id' => $uid])
        ->get();

        $perms = [];
        foreach ((array) $_permissions as $p){
            $tb = $p['tb'];
            $perms[$tb] =  $p['la'] * 64 + $p['ra'] * 32 +  $p['l'] * 16 + $p['r'] * 8 + $p['c'] * 4 + $p['u'] * 2 + $p['d'];
        }

        return $perms;
    }

    static function fetchSpPermissions($uid) : Array {
        $perms = DB::table('user_sp_permissions')
        ->assoc()
        ->where(['user_id' => $uid])
        ->join('sp_permissions', 'user_sp_permissions.sp_permission_id', '=', 'sp_permissions.id')
        ->pluck('name');

        return $perms ?? [];
    }

    static function fetchPermissions($uid) : Array { 
        return [
                'tb' => self::fetchTbPermissions($uid), 
                'sp' => self::fetchSpPermissions($uid) 
        ];
    }

}