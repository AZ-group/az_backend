<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class CablesSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'cables',

			'id_name'		=> 'id',

			'attr_types'	=> [
				'id' => 'STR',
				'nombre' => 'STR',
				'calibre' => 'STR'
			],

			'nullable'		=> ['id'],

			'rules' 		=> [
				'nombre' => ['max' => 40]
			]
		];
	}	
}

