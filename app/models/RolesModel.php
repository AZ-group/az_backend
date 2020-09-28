<?php

namespace simplerest\models;

use simplerest\core\Model;

class RolesModel extends Model {

	protected $schema = [
		'id' 	=> 'INT',
		'name' 	=> 'STR'
	];

	protected $rules = [
	];

    function __construct($db = NULL){
		parent::__construct($db);
	}
}