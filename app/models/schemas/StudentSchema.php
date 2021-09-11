<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class StudentSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'student',

			'id_name'		=> NULL,

			'attr_types'	=> [
				'id' => 'STR',
				'name' => 'STR',
				'age' => 'INT',
				'class' => 'STR'
			],

			'nullable'		=> ['id', 'name', 'age', 'class'],

			'rules' 		=> [

			],

			'relationships' => [
				
			]
		];
	}	
}

