<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController;; 

class FolderOtherPermissions extends MyApiController
{     
    protected $scope = [
        'guest'   => [ ],  
        'basic'   => ['show'],
        'regular' => ['show', 'list', 'write']
    ];
    
    function __construct()
    {
        parent::__construct();
    }
        
} // end class
