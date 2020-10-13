<?php

namespace simplerest\models\schemas;

### IMPORTS

trait __NAME__
{ 
	### TRAITS
	
	function loadSchema(){
		$this->id_name = __ID__;

		/*
			Types are INT, STR and BOOL among others
			see: https://secure.php.net/manual/en/pdo.constants.php 
		*/
		$this->schema  = __SCHEMA__;

		$this->nullable 	= __NULLABLES__;

		$this->rules 		= __RULES__;
	}	
}

