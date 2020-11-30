<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class DivisasSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'divisas',

			'id_name'		=> 'id',

			'attr_types'	=> [
				'id' => 'INT',
				'ISO' => 'STR',
				'symbol' => 'STR',
				'name' => 'STR',
				'country' => 'STR',
				'position' => 'INT',
				'created_at' => 'STR',
				'updated_at' => 'STR'
			],

			'nullable'		=> ['id', 'ISO', 'position'],

			'rules' 		=> [
				'ISO' => ['max' => 10],
				'symbol' => ['max' => 8],
				'name' => ['max' => 32],
				'country' => ['max' => 60]
			]
		];
	}	
}

