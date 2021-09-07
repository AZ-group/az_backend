<?php

namespace simplerest\controllers;

use simplerest\core\Request;
use simplerest\libs\Factory;
use simplerest\core\api\v1\ResourceController;

class DumbAuthController extends ResourceController
{
    function __construct()
    {
        parent::__construct();
    }

    function super_cool_action($a)
    {
        //var_dump($this->acl->isGuest());    
        //var_dump($this->acl->getRoles());
        //var_dump($this->acl->isRegistered());

        if (!$this->acl->hasAnyRole(['cajero', 'basic']))
            Factory::response()->sendError('Unauthorized', 401);

        // acción cualquiera:
        return ++$a;
    }     
    
    function test()
    {
        $permissions = $this->acl->getTbPermissions();
        foreach ((array) $permissions as $tb => $perms){
            echo "[$tb]\n";
            $perms = (int) $perms;
            printf("List All: %d, Show All: %d, List: %d, Show: %d, Create: %d, Update: %d, Delete: %d", 
                ($perms & 64) AND 1, 
                ($perms & 32 ) AND 1,
                ($perms & 16) AND 1, 
                ($perms & 8 ) AND 1, 
                ($perms & 4 ) AND 1, 
                ($perms & 2 ) AND 1, 
                ($perms & 1 ) AND 1
            );
        }
    }

}