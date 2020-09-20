<?php

namespace simplerest\core;

use simplerest\core\ResourceController;
use simplerest\core\Request;
use simplerest\libs\Factory;
use simplerest\models\RolesModel;
use simplerest\core\api\v1\AuthController; // no debería estar hardcodeada la version

abstract class ResourceController extends Controller
{
    protected $auth;
    protected $roles;
    protected $uid;
    protected $is_admin;
    protected $permissions = null;
    
    protected $headers = [
        'Access-Control-Allow-Headers' => 'Authorization,Content-Type', 
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET,POST,DELETE,PUT,PATCH,HEAD,OPTIONS',
        'Access-Control-Allow-Credentials' => 'true',
        'Content-Type' => 'application/json; charset=UTF-8'
    ];

    function __construct()
    {       
        foreach ($this->headers as $key => $header){
            header("$key: $header");
        } 
        
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            Factory::response()->sendOK(); // no tocar !
        }

        if (Factory::request()->header('Authorization') == NULL && Factory::request()->header('authorization') == NULL){
            $this->uid = null;
            $this->is_admin = false;
            $this->roles = ['guest'];
        }

        Factory::response()->asObject();

        //var_dump(Factory::request()->headers());    
        //var_dump($this->roles);

        // auth payload
        $this->auth = (new AuthController())->check();

        //var_dump($this->auth);
            
        if (!empty($this->auth)){
            $this->uid = $this->auth->uid; 
            $this->permissions = $this->auth->permissions ?? NULL;

            $r = new RolesModel();
            $this->roles  = $this->auth->roles;              
            
            //var_dump($this->roles); ///

            $this->is_admin = false;
            foreach ($this->roles as $role){
                if ($role != 'guest' && $r->is_admin($role)){
                    $this->is_admin = true;
                    break;
                }
            }                
        }else{
            $this->uid = null;
            $this->is_admin = false;
            $this->roles = ['guest'];
        }

        //var_export($this->roles);
                    
        parent::__construct();
    }

    protected function is_admin(){
        return $this->is_admin;
    }

    protected function getRoles(){
        return $this->roles;
    }
    
    protected function getPermissions(string $table = NULL){
        if ($table == NULL)
            return $this->permissions;

        if (!isset($this->permissions->$table))
            return NULL;

        return $this->permissions->$table;
    }

    protected function isGuest(){
        return $this->roles == ['guest'];
    }

    protected function isRegistered(){
        return !$this->isGuest();
    }

    protected function isAdmin(){
        return $this->is_admin;
    }

    protected function hasRole(string $role){
        return in_array($role, $this->roles);
    }

    protected function hasAnyRole(array $authorized_roles){
        $authorized = false;
        foreach ((array) $this->roles as $role)
            if (in_array($role, $authorized_roles))
                $authorized = true;

        return $authorized;        
    }
    
    
}  