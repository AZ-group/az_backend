<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class Foo extends MyApiController
{ 
    protected $scope = [
        'guest'      => [],  
        'registered' => ['create'],
        'basic'      => [],
        'regular'    => ['read', 'list', 'write']
    ];

    function __construct()
    {       
        parent::__construct();
    }

        
} // end class
