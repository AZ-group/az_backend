<?php

namespace simplerest\models\schemas;

### IMPORTS

trait ProductsSchema
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
			'name' => 'STR',
			'description' => 'STR',
			'size' => 'STR',
			'cost' => 'INT',
			'created_at' => 'STR',
			'created_by' => 'INT',
			'updated_at' => 'STR',
			'updated_by' => 'INT',
			'deleted_at' => 'STR',
			'deleted_by' => 'INT',
			'active' => 'INT',
			'locked' => 'INT',
			'workspace' => 'STR',
			'belongs_to' => 'INT'
		];

		$this->not_fillable = [];
		$this->nullable 	= ['id', 'description', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by', 'active', 'locked', 'workspace', 'belongs_to'];
	
		$this->rules 		= [
			'name' => ['max' => 50],
			'description' => ['max' => 240],
			'size' => ['max' => 30],
			'workspace' => ['max' => 40]
		];
	}	
}

