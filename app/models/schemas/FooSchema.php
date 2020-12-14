<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class FooSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'foo',

			'id_name'		=> 'id',

			'attr_types'	=> [
				'id' => 'INT',
				'bar' => 'STR',
				'hide' => 'INT',
				'alta' => 'STR',
				'creado_por' => 'INT',
				'fecha_modificacion' => 'STR',
				'modificado_por' => 'INT',
				'fecha_borrado' => 'STR',
				'borrado_por' => 'INT'
			],

			'nullable'		=> ['alta', 'creado_por', 'fecha_modificacion', 'modificado_por', 'fecha_borrado', 'borrado_por'],

			'rules' 		=> [
				'bar' => ['max' => 45]
			]
		];
	}	
}

