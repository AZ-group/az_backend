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

    function onPuttingBeforeCheck2($id, $data){
        $this->instance
        ->showDeleted()
        ->fill(['deleted_at']);
 
        $this->instance2
        ->showDeleted()
        ->where(['deleted_at', NULL, 'IS NOT']);
    }

            
    function onPuttingAfterCheck($id, &$data) { 
        $trashed = $data['trashed'] ?? true;

        if (isset($data['trashed']))
            unset($data['trashed']);

        unset($data['entity']);   

        if (strtolower($trashed) == false || $trashed === 0){
            $data['deleted_at'] = NULL;
        }
    } 


    function delete($id = NULL) {
        parent::delete($id);
    } // 

    function onDeletingBeforeCheck($id){
        $this->instance
        ->showDeleted()
        ->where(['deleted_at', NULL, 'IS NOT']);
    }

        
} // end class
