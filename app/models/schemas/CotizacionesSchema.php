<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class CotizacionesSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'cotizaciones',

			'id_name'		=> 'id',

			'attr_types'	=> [
				'id' => 'INT',
				'divisa_id' => 'INT',
				'buy_at' => 'STR',
				'sell_at' => 'STR',
				'arbitration' => 'STR',
				'created_at' => 'STR',
				'updated_at' => 'STR'
			],

			'nullable'		=> ['id', 'created_at', 'updated_at'],

			'rules' 		=> [

			]
		];
	}	
}

