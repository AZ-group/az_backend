<?php

namespace simplerest\models\schemas;

### IMPORTS

trait BazSchema
{ 
	### TRAITS
	
	function loadSchema(){
		$this->id_name = 'id_baz';

		/*
			Types are INT, STR and BOOL among others
			see: https://secure.php.net/manual/en/pdo.constants.php 
		*/
		$this->schema  = [
			'id_baz' => 'INT',
			'name' => 'STR',
			'cost' => 'STR'
		];

		$this->not_fillable = [];
		$this->nullable 	= [];
	
		$this->rules 		= [
			'name' => ['max' => 45]
		];
	}	
}

