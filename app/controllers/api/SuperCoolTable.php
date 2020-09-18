<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class SuperCoolTable extends MyApiController
{ 
    static protected $guest_access = true;
    
    protected $scope = [
        'guest'      => ['retrieve'],  
        'basic'      => ['retrieve', 'write'],
        'regular'    => ['retrieve', 'write']
    ];

    function __construct()
    {       
        parent::__construct();
    }

        
} // end class
