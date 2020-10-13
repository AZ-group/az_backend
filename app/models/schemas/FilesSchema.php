<?php

namespace simplerest\models\schemas;

### IMPORTS

trait FilesSchema
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
			'filename' => 'STR',
			'file_ext' => 'STR',
			'filename_as_stored' => 'STR',
			'belongs_to' => 'INT',
			'guest_access' => 'INT',
			'locked' => 'INT',
			'broken' => 'INT',
			'created_at' => 'STR',
			'deleted_at' => 'STR'
		];

		$this->nullable 	= ['id', 'belongs_to', 'guest_access', 'broken', 'deleted_at'];
	
		$this->rules 		= [
			'filename' => ['max' => 255],
			'file_ext' => ['max' => 30],
			'filename_as_stored' => ['max' => 60]
		];
	}	
}

