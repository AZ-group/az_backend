<?php

namespace simplerest\models\schemas\db1;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class BarSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'bar',

			'id_name'		=> 'uuid',

			'attr_types'	=> [
				'uuid' => 'STR',
				'name' => 'STR',
				'price' => 'STR',
				'email' => 'STR',
				'belongs_to' => 'INT',
				'created_at' => 'STR',
				'deleted_at' => 'STR',
				'updated_at' => 'STR'
			],

			'nullable'		=> ['created_at', 'deleted_at', 'updated_at', 'uuid'],

			'rules' 		=> [
				'uuid' => ['max' => 36],
				'name' => ['max' => 50],
				'email' => ['max' => 80]
			],

			'relationships' => [
				
			]
		];
	}	
}

