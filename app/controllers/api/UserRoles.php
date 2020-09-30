<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController;
use simplerest\libs\Factory;


class UserRoles extends MyApiController
{    
    protected $table_name = 'user_roles';

    function __construct()
    {
        if (Factory::request()->hasAuth()){
            $this->callable = ['get'];

            $this->is_retrievable   = true;
        }

        parent::__construct();
    }

    function get($id = null){
        if ($this->isRegistered() && !Factory::acl()->hasSpecialPermission('read_all', $this->roles)){
            $id = $this->auth['uid'];
        }
        
        parent::get($id);
    } 

    function post($id = NULL)
    { 
        if ($this->isRegistered() && !Factory::acl()->hasSpecialPermission('write_all', $this->roles)){
            $id = $this->auth['uid'];
        }

        parent::post($id);
    } //
    
    function put($id = NULL)
    { 
        if ($this->isRegistered() && !Factory::acl()->hasSpecialPermission('grant', $this->roles)){
            $id = $this->auth['uid'];
        }

        parent::put($id);
    } //

    function patch($id = NULL)
    { 
        if ($this->isRegistered() && !Factory::acl()->hasSpecialPermission('grant', $this->roles)){
            $id = $this->auth['uid'];
        }

        parent::patch($id);
    } //


    function delete($id = NULL)
    { 
        if ($this->isRegistered() && !Factory::acl()->hasSpecialPermission('write_all', $this->roles)){
            $id = $this->auth['uid'];
        }

        parent::delete($id);
    } //
        
} // end class
