<?php

namespace simplerest\core\api\v1;

use simplerest\controllers\MyApiController; 
use simplerest\core\interfaces\IAuth;
use simplerest\libs\Factory;
use simplerest\libs\Arrays;
use simplerest\libs\DB;
use simplerest\libs\Debug;
use simplerest\libs\Url;
use simplerest\libs\Validator;
use simplerest\core\exceptions\InvalidValidationException;

class MySelf extends MyApiController 
{  
    //protected $model_table = 'users';   // <-- no sirve más el truco ?
    protected $model_name = 'UsersModel';

    //
    // además necesito el Schema !

    function __construct() 
    { 
        if (Factory::request()->hasAuth()){
            $this->callable = ['get', 'put', 'patch', 'delete'];

            $this->is_listable = true;
            $this->is_retrievable = true;
        }
        
        parent::__construct();
    }

    function get($id = null){
        $id = $this->auth['uid'];
        parent::get($id);
    } 

    function put($id = NULL)
    { 
        $id = $this->auth['uid'];
        parent::put($id);
    } //

    function patch($id = NULL)
    { 
        $id = $this->auth['uid'];
        parent::patch($id);
    } //
        
    function delete($id = null){
        $id = $this->auth['uid'];

        $ok = (bool) DB::table('users')->where([['id', $id], ['active', 1]])
            ->fill(['active'])
        ->update(['active' => 0]);

        if ($ok) {
            Factory::response()->send("Your account was succesfully disabled");
        } else {
            Factory::response()->send("An error has ocurred trying to disable your account.");
        }        
    } // 
       
    
}  