<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class Foo extends MyApiController
{ 
    protected $scope = [
        'guest'      => ['read', 'list', 'write'],  
        'basic'      => ['read', 'list', 'write'],
        'regular'    => ['read', 'list', 'write']
    ];

    function __construct()
    {       
        parent::__construct();
    }

        
} // end class
