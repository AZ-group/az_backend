<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 
use simplerest\libs\Debug;

class Foo extends MyApiController
{ 
    function __construct()
    {       
        parent::__construct();
    }

    public function onGetting($id) { 
        //Debug::dump($id, 'id');
    }
    
    public function onGot($id, ?int $count){ }

    public function onDeleting($id){ }
    public function onDeleted($id, ?int $affected){ }

    public function onPosting($id, Array $data){ }
    public function onPost($id, Array $data){ }

    public function onPutting($id, Array $data){
        //Debug::dump($id, 'id');
        //Debug::dump($data, 'data');
     }

    public function onPut($id, Array $data, ?int $affected){ 
        //Debug::dump($id, 'id');
        //Debug::dump($data, 'data');
        //Debug::dump($affected, 'affected rows');
    }
        
} // end class
