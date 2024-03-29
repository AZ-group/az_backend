<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class TblTipoCuentaBancariaSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'tbl_tipo_cuenta_bancaria',

			'id_name'		=> 'tcb_intId',

			'attr_types'	=> [
				'tcb_intId' => 'INT',
				'tcb_varDescripcion' => 'STR',
				'tcb_dtimFechaCreacion' => 'STR',
				'tcb_dtimFechaActualizacion' => 'STR',
				'est_intIdEstado' => 'INT',
				'usu_intIdCreador' => 'INT',
				'usu_intIdActualizador' => 'INT'
			],

			'nullable'		=> ['tcb_intId'],

			'rules' 		=> [
				'tcb_varDescripcion' => ['max' => 50]
			],

			'relationships' => [
				'tbl_estado' => [
					['tbl_estado.est_intId','tbl_tipo_cuenta_bancaria.est_intIdEstado']
				],
				'tbl_usuario' => [
					['usu_intIdActualizadors.usu_intId','tbl_tipo_cuenta_bancaria.usu_intIdActualizador'],
					['usu_intIdActualizadorss.usu_intId','tbl_tipo_cuenta_bancaria.usu_intIdCreador']
				]
			]
		];
	}	
}

