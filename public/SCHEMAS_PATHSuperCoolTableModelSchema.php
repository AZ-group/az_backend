<?php

namespace simplerest\models\schemas;

### IMPORTS

trait SuperCoolTableModelSchema
 { 
	function loadSchema(){
		### TRAITS

		/*
			Types are INT, STR and BOOL among others
			see: https://secure.php.net/manual/en/pdo.constants.php 
		*/
		$this->schema = [
			'id' => 'INT',
			'name' => 'STR',
			'active' => 'INT',
			'belongs_to' => 'INT',
			'deleted_at' => 'STR',
			'locked' => 'INT'
		];

		$this->not_fillable = [];
		$this->nullable 	= ['id', 'deleted_at'];
	
		$this->rules 		= [
			'name' => ['max' => 45]
		];
	}	
}

