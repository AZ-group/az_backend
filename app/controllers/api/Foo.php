<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class Foo extends MyApiController
{ 
    protected $scope = [
        'guest'      => ['retrieve', 'list', 'write'],  
        'basic'      => ['retrieve', 'list', 'write'],
        'regular'    => ['retrieve', 'list', 'write']
    ];

    function __construct()
    {       
        parent::__construct();
    }

        
} // end class
