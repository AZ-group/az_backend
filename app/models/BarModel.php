<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;
use simplerest\libs\DB;
use simplerest\traits\Uuids;

class BarModel extends Model
 { 
	use Uuids;

	protected $id_name = 'uuid';

	/*
		Types are INT, STR and BOOL among others
		see: https://secure.php.net/manual/en/pdo.constants.php 
	*/
	protected $schema = [
			'uuid' => 'STR',
			'name' => 'STR',
			'price' => 'STR',
			'belongs_to' => 'INT',
			'updated_at' => 'STR'
	];

	protected $not_fillable = [];
	protected $nullable = ['updated_at', 'uuid'];
	protected $hidden   = [];

	protected $rules = [
			'uuid' => ['max' => 36],
			'name' => ['max' => 50]
	];

    function __construct($db = NULL){		
        parent::__construct($db);
	}
	
}

