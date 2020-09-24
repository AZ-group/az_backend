<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 
use simplerest\libs\Factory;

class Roles extends MyApiController
{
    function __construct()
    {
        parent::__construct();
    }
        
    function get($id = null){
        // re-hacer

        if ($id == NULL){
            $rows = [];
            $roles = $roles_m->get_roles();
            foreach ($roles as $idx => $rol){
                $rows[] = [
                    'id' => $idx,
                    'name' => $rol['name'],
                    'permissions' => [
                        'sp_permissions' => [],
                        'tb_permissions' => []
                    ]
                ]; 
            }
            return $rows;
        }else{
            //$row = ...[$id];
            return [
                'id' => $role_id,
                'name' => $role_name,
                'permissions' => [
                    'sp_permissions' => [],
                    'tb_permissions' => []
                ]
            ];
        }
    }

    function create($id = null){
        Factory::response()->sendError('Not implemented', 501, "Roles are read-only");
    }

    function put($id = null){
        Factory::response()->sendError('Not implemented', 501, "Roles are read-only");
    }

    function patch($id = null){
        Factory::response()->sendError('Not implemented', 501, "Roles are read-only");
    }

    function delete($id = null){
        Factory::response()->sendError('Not implemented', 501, "Roles are read-only");
    }

} // end class
