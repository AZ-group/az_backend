<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class ProductCommentsSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'product_comments',

			'id_name'		=> 'product_id',

			'attr_types'	=> [
				'id' => 'INT',
				'text' => 'STR',
				'product_id' => 'INT'
			],

			'nullable'		=> [],

			'rules' 		=> [
				'text' => ['max' => 144]
			],

			'relationships' => [
				'products' => [
					['products.id','product_comments.product_id']
				]
			]
		];
	}	
}

