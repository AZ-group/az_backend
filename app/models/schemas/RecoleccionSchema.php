<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class RecoleccionSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'recoleccion',

			'id_name'		=> 'id',

			'attr_types'	=> [
				'id' => 'INT',
				'idcliente' => 'INT',
				'calle' => 'STR',
				'latitud' => 'STR',
				'longitud' => 'STR',
				'fecha_r' => 'STR',
				'hora_d' => 'STR',
				'hora_h' => 'STR',
				'notas' => 'STR',
				'alta' => 'STR',
				'activa' => 'INT',
				'rec' => 'INT',
				'referencia' => 'STR',
				'orden' => 'INT',
				'idpago' => 'INT',
				'ciudad' => 'STR',
				'idencomendado' => 'INT'
			],

			'nullable'		=> ['id', 'calle', 'latitud', 'longitud', 'hora_d', 'hora_h', 'notas', 'ciudad'],

			'rules' 		=> [
				'calle' => ['max' => 255],
				'latitud' => ['max' => 30],
				'longitud' => ['max' => 30],
				'hora_d' => ['max' => 10],
				'hora_h' => ['max' => 10],
				'notas' => ['max' => 255],
				'referencia' => ['max' => 10],
				'ciudad' => ['max' => 255]
			]
		];
	}	
}

