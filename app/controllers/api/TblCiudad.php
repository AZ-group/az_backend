<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class TblCiudad extends MyApiController
{ 
    static protected $soft_delete = true;

    function __construct()
    {       
        parent::__construct();
    }        
} 
