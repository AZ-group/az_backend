<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 
use simplerest\libs\Factory;
use simplerest\libs\DB;
use simplerest\libs\Validator;
use simplerest\core\exceptions\InvalidValidationException;
use simplerest\libs\Debug;

class Permissions extends MyApiController
{ 
    
    protected $model_name  = 'UserTbPermissionsModel';
    protected $table_name = 'user_tb_permissions';

    function __construct()
    {       
        parent::__construct();
    }

    function post(){
        $data = Factory::request()->getBody();

        if (empty($data))
            Factory::response()->sendError('Invalid JSON',400);
        
        $model    = '\\simplerest\\models\\'.$this->model_name;
        $instance = DB::table('user_tb_permissions')->setFetchMode('ASSOC');

        try {
            $conn = DB::getConnection();
            $instance->setConn($conn);

            if ($instance->inSchema(['created_by'])){
                $data['created_by'] = $this->uid;
            }

            $validado = (new Validator)->validate($instance->getRules(), $data);
            if ($validado !== true){
                Factory::response()->sendError('Data validation error', 400, $validado);
            }  

            DB::transaction(function() use($data, $instance){                
                $ok = DB::table('user_tb_permissions')->where(['tb' => $data['tb'], 'user_id' => $data['user_id']])->delete(false);

                $instance->create($data);
            });
            
            Factory::response()->send(['id' => $instance->id], 201);
           

        } catch (InvalidValidationException $e) { 
            Factory::response()->sendError('Validation Error', 400, json_decode($e->getMessage()));
        } catch (\Exception $e) {
            Factory::response()->sendError($e->getMessage());
        }	

    } // 
        
} // end class
