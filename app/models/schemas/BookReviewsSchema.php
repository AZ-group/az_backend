<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class BookReviewsSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'book_reviews',

			'id_name'		=> 'id',

			'attr_types'	=> [
				'id' => 'INT',
				'text' => 'STR',
				'book_id' => 'INT',
				'created_at' => 'STR',
				'updated_at' => 'STR'
			],

			'nullable'		=> ['id', 'created_at', 'updated_at'],

			'rules' 		=> [
				'text' => ['max' => 144]
			],

			'relationships' => [
				'books' => [
					['books.id','book_reviews.book_id']
				]
			]
		];
	}	
}

