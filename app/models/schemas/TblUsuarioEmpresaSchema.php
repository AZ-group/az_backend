<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class TblUsuarioEmpresaSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'tbl_usuario_empresa',

			'id_name'		=> 'uem_intId',

			'attr_types'	=> [
				'uem_intId' => 'INT',
				'uem_dtimFechaCreacion' => 'STR',
				'uem_dtimFechaActualizacion' => 'STR',
				'usu_intIdUsuario' => 'INT',
				'emp_intIdempresa' => 'INT',
				'usu_intIdCreador' => 'INT',
				'usu_intIdActualizador' => 'INT'
			],

			'nullable'		=> ['uem_intId'],

			'rules' 		=> [

			],

			'relationships' => [
				'tbl_empresa' => [
					['tbl_empresa.emp_intId','tbl_usuario_empresa.emp_intIdempresa']
				],
				'tbl_usuario' => [
					['usu_intIdActualizadors.usu_intId','tbl_usuario_empresa.usu_intIdActualizador'],
					['usu_intIdActualizadorss.usu_intId','tbl_usuario_empresa.usu_intIdCreador'],
					['usu_intIdActualizadorsss.usu_intId','tbl_usuario_empresa.usu_intIdUsuario']
				]
			]
		];
	}	
}

