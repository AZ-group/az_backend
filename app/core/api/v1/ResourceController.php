<?php

namespace simplerest\core\api\v1;

use simplerest\libs\Debug;
use simplerest\core\Request;
use simplerest\libs\Factory;
use simplerest\core\Acl;
use simplerest\core\Controller;
use simplerest\core\api\v1\AuthController;


abstract class ResourceController extends Controller
{
    protected $acl;
    protected $auth;
    protected $uid;
    protected $roles = [];
    protected $permissions = [];


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

        $this->acl = include CONFIG_PATH . 'acl.php';
        
        if (!Factory::request()->hasAuth()){
            $this->uid = null;
            $this->roles = [$this->acl->getGuest()];
        }

        Factory::response()->asObject();

        // auth payload
        $this->auth = (new AuthController())->check();

        //var_dump($this->roles);
        //var_dump($this->auth);
        //Debug::dd($acl->getRoles());  ///// 
        //exit;
        
        if (!empty($this->auth)) 
        {
            $this->uid = $this->auth->uid; 
            $this->roles  = $this->auth->roles;
            $this->permissions = $this->auth->permissions ?? NULL;                          
        }else{
            $this->uid = null;
            $this->roles = [$this->acl->getGuest()];
            $this->permissions = [];
        }

        //var_export($this->roles);
        //var_export($this->permissions);
                    
        parent::__construct();
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
        return $this->roles == [$this->acl->getGuest()];
    }

    protected function isRegistered(){
        return !$this->isGuest();
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