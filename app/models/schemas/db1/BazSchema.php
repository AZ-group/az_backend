<?php

namespace simplerest\models\schemas\db1;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class BazSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'baz',

			'id_name'		=> 'id_baz',

			'attr_types'	=> [
				'id_baz' => 'INT',
				'name' => 'STR',
				'cost' => 'STR'
			],

			'nullable'		=> [],

			'rules' 		=> [
				'name' => ['max' => 45]
			],

			'relationships' => [
				
			]
		];
	}	
}

