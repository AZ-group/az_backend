<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class SuperCoolTable extends MyApiController
{ 
    static protected $guest_access = true;
    
    protected $scope = [
        'guest'      => ['read'],  
        'basic'      => ['read', 'write'],
        'regular'    => ['read', 'write']
    ];

    function __construct()
    {       
        parent::__construct();
    }

        
} // end class
