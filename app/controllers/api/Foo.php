<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class Foo extends MyApiController
{ 
    protected $scope = [
        'guest'      => ['show', 'list', 'write'],  
        'basic'      => ['show', 'list', 'write'],
        'regular'    => ['show', 'list', 'write']
    ];

    function __construct()
    {       
        parent::__construct();
    }

        
} // end class
