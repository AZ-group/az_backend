<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class TblCategoriaLicenciaConduccion extends MyApiController
{ 
    static protected $soft_delete = true;

    function __construct()
    {       
        parent::__construct();
    }        
} 
