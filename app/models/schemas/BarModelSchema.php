<?php

namespace simplerest\models\schemas;

use simplerest\traits\Uuids;

trait BarModelSchema
{ 
	use Uuids;

	
	function loadSchema(){
		$this->id_name = __ID__;

		/*
			Types are INT, STR and BOOL among others
			see: https://secure.php.net/manual/en/pdo.constants.php 
		*/
		$this->schema  = [
			'uuid' => 'STR',
			'name' => 'STR',
			'price' => 'STR',
			'belongs_to' => 'INT',
			'updated_at' => 'STR'
		];

		$this->not_fillable = [];
		$this->nullable 	= ['updated_at', 'uuid'];
	
		$this->rules 		= [
			'uuid' => ['max' => 36],
			'name' => ['max' => 50]
		];
	}	
}

