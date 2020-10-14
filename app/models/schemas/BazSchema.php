<?php

namespace simplerest\models\schemas;

### IMPORTS

trait BazSchema
{ 
	### TRAITS
	
	function loadSchema(){

		// En conjunto deberían definir el 'schema'
		$this->id_name = 'id_baz';

		/*
			debería ser 'attribute_types' 
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

