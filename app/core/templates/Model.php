<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;
use simplerest\libs\DB;
### ADDITIONAL IMPORTS

class __NAME__ extends Model
 { 
	### TRAITS
	### ADDITIONAL PROPERTIES

	/*
		Types are INT, STR and BOOL among others
		see: https://secure.php.net/manual/en/pdo.constants.php 
	*/
	protected $schema = __SCHEMA__;

	protected $not_fillable = __NOT_FILLABLE__;
	protected $nullable = __NULLABLES__;
	protected $hidden   = [];

	protected $rules = __RULES__;

    function __construct($db = NULL){		
        parent::__construct($db);
	}
	
}

