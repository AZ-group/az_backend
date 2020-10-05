<?php
namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Debug;

class BazModel extends Model 
{
	//protected $table_name = "baz";
	protected $id_name = 'id_baz';

	protected $schema = [
		'id_baz' => 'INT',
		'name' => 'STR',
		'cost' => 'STR'
	];

	protected $nullable = [
		'cost'
	];

	protected $rules = [
		'name' => ['type' => 'alpha_spaces']
	];

    function __construct($db = NULL){
		parent::__construct($db);

		//var_export($this->getNullables());
	}

}







