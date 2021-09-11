<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class TblCargo extends MyApiController
{ 
    static protected $soft_delete = true;

    function __construct()
    {       
        parent::__construct();
    }        
} 
