<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class SuperCoolTable extends MyApiController
{ 
    
    protected $scope = [
        'guest'      => ['show'],  
        'basic'      => ['show', 'write'],
        'regular'    => ['show', 'write']
    ];

    function __construct()
    {       
        parent::__construct();
    }

        
} // end class
