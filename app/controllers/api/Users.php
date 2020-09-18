<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class Users extends MyApiController
{
    //static protected $owned = false;

    protected $scope = [
        'guest'      => [], 
        'basic'      => ['show'],
        'regular'    => ['show', 'update', 'delete']
    ];

    function __construct()
    {
        parent::__construct();
    }
        
} // end class
