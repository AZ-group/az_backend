<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;
use simplerest\libs\DB;

class __NAME__Model extends Model
 { 
	protected $not_fillable = [];
	protected $nullable = [];
	protected $hidden   = [];

	/*
		Types are INT, STR and BOOL among others
		see: https://secure.php.net/manual/en/pdo.constants.php 
	*/
	protected $schema = [
		
	];

	protected $rules = [
	
	];

    function __construct($db = NULL){		
        parent::__construct($db);
	}
	
}

