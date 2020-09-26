<?php

namespace simplerest\core\api\v1;

use simplerest\controllers\MyApiController;
use simplerest\libs\Factory;
use simplerest\libs\Arrays;
use simplerest\libs\Strings;
use simplerest\libs\DB;
use simplerest\libs\Debug;
use simplerest\libs\Validator;
use simplerest\core\exceptions\InvalidValidationException;
use simplerest\libs\Url;


class TrashCan extends MyApiController
{
    protected $model;
    
    function __construct()
    {
        $entity  = Factory::request()->shift('entity');

        if (empty($entity))
            Factory::response()->sendError('Entity is required', 400);

        $entity = Strings::toCamelCase($entity);

        $this->model_name = ucfirst($entity) . 'Model';
        $this->model_table = strtolower($entity);

        $this->model    = 'simplerest\\models\\'. $this->model_name;
        $api_ctrl = '\simplerest\\controllers\\api\\' . ucfirst($entity);
        
        if (!$api_ctrl::hasSoftDelete()){
            Factory::response()->sendError('Not implemented', 501, "Trashcan not implemented for $entity");
        }
        
        if (!class_exists($this->model))
            Factory::response()->sendError("Entity $entity does not exists", 400);

        $this->instance = (new $this->model())->setFetchMode('ASSOC');  
        
        if (!$this->instance->inSchema(['belongs_to']) || !$this->instance->inSchema(['deleted_at'])){
            Factory::response()->sendError('Not implemented', 501, "Trashcan not implemented for $entity");
        }
            
        //Debug::dump($this->model_table);
        //exit;
        parent::__construct();

    }

    function get($id = null) {
        parent::get($id);
    } // 

    function onGettingAfterCheck($id){
        $this->instance
        ->showDeleted()
        ->where(['deleted_at', NULL, 'IS NOT']);
    }


    function post() {
        Factory::response()->sendError('You can not create a trashcan resource',405);
    }        

    protected function modify($id = NULL, bool $put_mode = false)
    {
        parent::modify($id, $put_mode);
    } //  

    function onPuttingBeforeCheck($id, $data){
        $this->instance
        ->showDeleted()
        ->fill(['deleted_at']);;
    }


    
    function delete($id = NULL) {
        if($id == NULL)
            Factory::response()->sendError("Lacks id in request",400);

        if (!ctype_digit($id))
            Factory::response()->sendError('Bad request', 400, 'Id should be an integer');

        $data = Factory::request()->getBody(); 

        try { 

            /////////////////////////////////////////////////////
            $_get  = Factory::request()->getQuery();

            if (!isset($data['entity']))
                Factory::response()->sendError('Entity is needed in request body', 400);

            $entity = Strings::toCamelCase($data['entity']);    
           
            $this->model_name = ucfirst($entity) . 'Model';
            $this->model_table = strtolower($entity);

            $model    = 'simplerest\\models\\'. $this->model_name;
            $api_ctrl = '\simplerest\\controllers\\api\\' . ucfirst($entity);

            if (!class_exists($model))
                Factory::response()->sendError("Entity $entity does not exists", 400);
            
            $conn = DB::getConnection();
            $instance = (new $model($conn))->setFetchMode('ASSOC'); 

            if (!$instance->inSchema(['deleted_at']))
                Factory::response()->sendError('Not implemented', 501, "Trashcan not implemented for $entity");
            
            $owned = $instance->inSchema(['belongs_to']);
            ////////////////////////////////////////////////////

            $instance->fill(['deleted_at']); //

            $instance->showDeleted(); //
            $rows = $instance->where([
                ['id', $id],
                ['deleted_at', NULL, 'IS NOT']
            ])->get();

            if (count($rows) == 0){
                Factory::response()->code(404)->sendError("Register for id=$id does not exists in trash");
            }
            
            if (!$this->is_admin && $owned && $rows[0]['belongs_to'] != $this->uid){
                Factory::response()->sendError('Forbidden', 403, 'You are not the owner');
            }         
                        
            if (!$this->is_admin && isset($rows[0]['locked']) && $rows[0]['locked'] == 1){
                Factory::response()->sendError("Forbidden", 403, "Locked by Admin");
            }

            if($instance->delete(false)){
                Factory::response()->sendJson("OK");
            }	
            else
                Factory::response()->sendError("Record not found in trash can",404);

        } catch (\Exception $e) {
            Factory::response()->sendError("Error during PATCH for id=$id with message: {$e->getMessage()}");
        }

    } // 

        
} // end class
