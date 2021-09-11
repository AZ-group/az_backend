<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class TblConsecutivo extends MyApiController
{ 
    static protected $soft_delete = true;

    function __construct()
    {       
        parent::__construct();
    }        
} 
