<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 
use simplerest\libs\Factory;

class Cotizaciones extends MyApiController
{ 
    static protected $soft_delete = true;

    function __construct()
    {       
        Factory::request()->shiftQuery('_');
        parent::__construct();
    }        
} 
