<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\ValidationRules;
use simplerest\models\schemas\ProductsSchema;

class ProductsModel extends Model
 { 
	use ProductsSchema;
	### PROPERTIES

	protected $hidden   = [];

    function __construct($db = NULL){
		$this->loadSchema();		
        parent::__construct($db);
	}	
}

