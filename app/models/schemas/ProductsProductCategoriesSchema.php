<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class ProductsProductCategoriesSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'products_product_categories',

			'id_name'		=> 'id',

			'attr_types'	=> [
				'id' => 'INT',
				'product_id' => 'INT',
				'product_category_id' => 'INT',
				'created_at' => 'STR',
				'updated_at' => 'STR'
			],

			'nullable'		=> ['id', 'created_at', 'updated_at'],

			'rules' 		=> [

			],

			'relationships' => [
				// ok
				'products' => [
					['products.id', 'products_product_categories.product_id']
				] 
			]
		];
	}	
}

