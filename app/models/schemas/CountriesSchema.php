<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class CountriesSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'countries',

			'id_name'		=> 'code',

			'attr_types'	=> [
				'code' => 'INT',
				'alpha2' => 'STR',
				'alpha3' => 'STR',
				'langCS' => 'STR',
				'langDE' => 'STR',
				'langEN' => 'STR',
				'langES' => 'STR',
				'langFR' => 'STR',
				'langIT' => 'STR',
				'langNL' => 'STR',
				'currency' => 'STR'
			],

			'nullable'		=> [],

			'rules' 		=> [
				'alpha2' => ['max' => 2],
				'alpha3' => ['max' => 3],
				'langCS' => ['max' => 45],
				'langDE' => ['max' => 45],
				'langEN' => ['max' => 45],
				'langES' => ['max' => 45],
				'langFR' => ['max' => 45],
				'langIT' => ['max' => 45],
				'langNL' => ['max' => 45],
				'currency' => ['max' => 3]
			]
		];
	}	
}

