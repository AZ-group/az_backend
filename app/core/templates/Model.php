<?php

namespace simplerest\models;

use simplerest\core\Model;
### IMPORTS

class __NAME__ extends Model
 { 
	### TRAITS
	### PROPERTIES

	protected $hidden   = [];

    function __construct($db = NULL){
		$this->loadSchema();		
        parent::__construct($db);
	}	
}

