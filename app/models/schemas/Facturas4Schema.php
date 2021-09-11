<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class Facturas4Schema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'facturas4',

			'id_name'		=> 'id',

			'attr_types'	=> [
				'id' => 'STR',
				'edad' => 'STR',
				'firstname' => 'STR',
				'lastname' => 'STR',
				'username' => 'STR',
				'password' => 'STR',
				'password_char' => 'STR',
				'texto_vb' => 'STR',
				'texto' => 'STR',
				'texto_tiny' => 'STR',
				'texto_md' => 'STR',
				'texto_long' => 'STR',
				'codigo' => 'STR',
				'blob_tiny' => 'STR',
				'blob_md' => 'STR',
				'blob_long' => 'STR',
				'bb' => 'STR',
				'json_str' => 'STR',
				'karma' => 'INT',
				'code' => 'STR',
				'big_num' => 'INT',
				'ubig' => 'STR',
				'medium' => 'INT',
				'small' => 'INT',
				'tiny' => 'INT',
				'saldo' => 'STR',
				'flotante' => 'STR',
				'doble_p' => 'STR',
				'num_real' => 'STR',
				'some_bits' => 'STR',
				'active' => 'INT',
				'paused' => 'INT',
				'flavors' => 'STR',
				'role' => 'STR',
				'hora' => 'STR',
				'birth_year' => 'STR',
				'fecha' => 'STR',
				'vencimiento' => 'STR',
				'ts' => 'STR',
				'deleted_at' => 'STR',
				'created_at' => 'STR',
				'updated_at' => 'STR',
				'correo' => 'STR',
				'user_id' => 'INT'
			],

			'nullable'		=> ['id', 'lastname', 'password_char', 'vencimiento'],

			'rules' 		=> [
				'firstname' => ['max' => 60],
				'lastname' => ['max' => 60],
				'username' => ['max' => 60],
				'password' => ['max' => 128],
				'correo' => ['max' => 60]
			],

			'relationships' => [
				'users' => [
					['users.id','facturas4.user_id']
				]
			]
		];
	}	
}

