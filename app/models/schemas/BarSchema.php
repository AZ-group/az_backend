<?php

namespace simplerest\models\schemas;

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
			'belongs_to' => 'INT',
			'updated_at' => 'STR'
		],

			'nullable'		=> ['updated_at', 'uuid'],

			'rules' 		=> [
				'uuid' => ['max' => 36],
				'name' => ['max' => 50]
			]
		];
	}	
}

