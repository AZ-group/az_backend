<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController;

class UserRoles extends MyApiController
{     
    protected $scope = [
        'guest'      => [], 
        'basic'      => ['list'],
        'regular'    => ['list']
    ];

    function __construct()
    {
        parent::__construct();
    }
        
} // end class
