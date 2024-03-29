<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class TblClaseLibretaMilitarSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'tbl_clase_libreta_militar',

			'id_name'		=> 'clm_intId',

			'attr_types'	=> [
				'clm_intId' => 'INT',
				'clm_varNombre' => 'STR',
				'clm_dtimFechaCreacion' => 'STR',
				'clm_dtimFechaActualizacion' => 'STR',
				'est_intIdEstado' => 'INT',
				'usu_intIdCreador' => 'INT',
				'usu_intIdActualizador' => 'INT'
			],

			'nullable'		=> ['clm_intId'],

			'rules' 		=> [
				'clm_varNombre' => ['max' => 50]
			],

			'relationships' => [
				'tbl_estado' => [
					['tbl_estado.est_intId','tbl_clase_libreta_militar.est_intIdEstado']
				],
				'tbl_usuario' => [
					['usu_intIdActualizadors.usu_intId','tbl_clase_libreta_militar.usu_intIdActualizador'],
					['usu_intIdActualizadorss.usu_intId','tbl_clase_libreta_militar.usu_intIdCreador']
				]
			]
		];
	}	
}

