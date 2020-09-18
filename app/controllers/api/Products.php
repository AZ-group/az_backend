<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class Products extends MyApiController
{ 
    static protected $folder_field = 'workspace';

    function __construct()
    {       
        $this->scope['guest'] = ['read'];
        $this->scope['basic'] = ['read', 'write'];
        $this->scope['regular'] = ['read', 'write'];

        parent::__construct();
    }

        
} // end class
