<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class TblMotivoRetiroSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'tbl_motivo_retiro',

			'id_name'		=> 'mtr_intId',

			'attr_types'	=> [
				'mtr_intId' => 'INT',
				'mtr_varNombre' => 'STR',
				'mtr_varDescripcion' => 'STR',
				'mtr_dtimFechaCreacion' => 'STR',
				'mtr_dtimFechaActualizacion' => 'STR',
				'est_intIdEstado' => 'INT',
				'usu_intIdCreador' => 'INT',
				'usu_intIdActualizador' => 'INT'
			],

			'nullable'		=> ['mtr_intId'],

			'rules' 		=> [
				'mtr_varNombre' => ['max' => 50],
				'mtr_varDescripcion' => ['max' => 250]
			],

			'relationships' => [
				'tbl_estado' => [
					['tbl_estado.est_intId','tbl_motivo_retiro.est_intIdEstado']
				],
				'tbl_usuario' => [
					['usu_intIdActualizadors.usu_intId','tbl_motivo_retiro.usu_intIdActualizador'],
					['usu_intIdActualizadorss.usu_intId','tbl_motivo_retiro.usu_intIdCreador']
				]
			]
		];
	}	
}

