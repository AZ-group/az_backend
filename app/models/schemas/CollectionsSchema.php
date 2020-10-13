<?php

namespace simplerest\models\schemas;

### IMPORTS

trait CollectionsSchema
{ 
	### TRAITS
	
	function loadSchema(){
		$this->id_name = 'id';

		/*
			Types are INT, STR and BOOL among others
			see: https://secure.php.net/manual/en/pdo.constants.php 
		*/
		$this->schema  = [
			'id' => 'INT',
			'entity' => 'STR',
			'refs' => 'STR',
			'belongs_to' => 'INT',
			'created_at' => 'STR'
		];

		$this->not_fillable = [];
		$this->nullable 	= ['id'];
	
		$this->rules 		= [
			'entity' => ['max' => 80]
		];
	}	
}

