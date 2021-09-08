<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class BooksSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'books',

			'id_name'		=> 'id',

			'attr_types'	=> [
				'id' => 'INT',
				'name' => 'STR',
				'author_id' => 'INT',
				'editor_id' => 'INT'
			],

			'nullable'		=> ['id'],

			'rules' 		=> [
				'name' => ['max' => 60]
			],

			'relationships' => [
				'users' => [
					['authors.id','books.author_id'],
					['editors.id','books.editor_id']
				],
				'book_reviews' => [
					['book_reviews.book_id','books.id']
				]
			]
		];
	}	
}

