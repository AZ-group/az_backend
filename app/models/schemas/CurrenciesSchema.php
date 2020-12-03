<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class CurrenciesSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'currencies',

			'id_name'		=> NULL,

			'attr_types'	=> [
				'alpha2_main' => 'STR',
				'alpha3_main' => 'STR',
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

			'nullable'		=> ['alpha2_main', 'alpha3_main', 'alpha2', 'alpha3', 'langCS', 'langDE', 'langEN', 'langES', 'langFR', 'langIT', 'langNL'],

			'rules' 		=> [
				'alpha2_main' => ['max' => 2],
				'alpha3_main' => ['max' => 3],
				'alpha2' => ['max' => 2],
				'alpha3' => ['max' => 3],
				'currency' => ['max' => 3]
			]
		];
	}	
}

