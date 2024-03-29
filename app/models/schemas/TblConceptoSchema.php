<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class TblConceptoSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'tbl_concepto',

			'id_name'		=> 'cct_intId',

			'attr_types'	=> [
				'cct_intId' => 'INT',
				'cct_varNombre' => 'STR',
				'cct_varDescripcion' => 'STR',
				'cct_dtimFechaCreacion' => 'STR',
				'cct_dtimFechaActualizacion' => 'STR',
				'est_intIdEstado' => 'INT',
				'usu_intIdCreador' => 'INT',
				'usu_intIdActualizador' => 'INT'
			],

			'nullable'		=> ['cct_intId'],

			'rules' 		=> [
				'cct_varNombre' => ['max' => 50],
				'cct_varDescripcion' => ['max' => 250]
			],

			'relationships' => [
				'tbl_estado' => [
					['tbl_estado.est_intId','tbl_concepto.est_intIdEstado']
				],
				'tbl_usuario' => [
					['usu_intIdActualizadors.usu_intId','tbl_concepto.usu_intIdActualizador'],
					['usu_intIdActualizadorss.usu_intId','tbl_concepto.usu_intIdCreador']
				]
			]
		];
	}	
}

