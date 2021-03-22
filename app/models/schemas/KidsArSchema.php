<?php

namespace simplerest\models\schemas;

use simplerest\core\interfaces\ISchema;

### IMPORTS

class KidsArSchema implements ISchema
{ 
	### TRAITS
	
	function get(){
		return [
			'table_name'	=> 'kids_ar',

			'id_name'		=> 'id',

			'attr_types'	=> [
				'id' => 'INT',
				'fullname' => 'STR',
				'date_missing' => 'STR',
				'age_photo' => 'INT',
				'location' => 'STR',
				'ori_data' => 'STR',
				'ori_id' => 'STR',
				'found' => 'INT',
				'extras' => 'STR'
			],

			'nullable'		=> ['id', 'date_missing', 'age_photo', 'location', 'extras'],

			'rules' 		=> [
				'fullname' => ['max' => 100],
				'location' => ['max' => 255],
				'ori_data' => ['max' => 120],
				'ori_id' => ['max' => 240]
			]
		];
	}	
}

