<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;
use simplerest\libs\DB;
### IMPORTS

class SuperCoolTableModel extends Model
 { 
	### TRAITS
	### PROPERTIES

	/*
		Types are INT, STR and BOOL among others
		see: https://secure.php.net/manual/en/pdo.constants.php 
	*/
	protected $schema = [
			'id' => 'INT',
			'name' => 'STR',
			'active' => 'INT',
			'belongs_to' => 'INT',
			'deleted_at' => 'STR',
			'locked' => 'INT'
	];

	protected $not_fillable = [];
	protected $nullable = ['id', 'deleted_at'];
	protected $hidden   = [];

	protected $rules = [
			'name' => ['max' => 45]
	];

    function __construct($db = NULL){		
        parent::__construct($db);
	}
	
}

