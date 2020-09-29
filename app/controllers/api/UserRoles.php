<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController;

class UserRoles extends MyApiController
{    
    protected $table_name = 'user_roles';

    function __construct()
    {
        parent::__construct();
    }
        
} // end class
