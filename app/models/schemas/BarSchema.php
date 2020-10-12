<?php

namespace simplerest\models\schemas;

use simplerest\traits\Uuids;

trait BarSchema
{ 
	use Uuids;
	
	function loadSchema(){
		$this->id_name = 'uuid';

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

