<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\ValidationRules;
### IMPORTS

class __NAME__ extends Model
 { 
	### TRAITS
	### PROPERTIES

	protected $hidden   = [];
	protected $not_fillable = [];

    function __construct($db = NULL){
		$this->loadSchema();		
        parent::__construct($db);
	}	
}

