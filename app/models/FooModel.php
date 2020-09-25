<?php
namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Debug;

class FooModel extends Model 
{
	protected $table_name = "foo";
	protected $id_name = 'id';

	protected $schema = [
		'id' => 'INT',
		'bar' => 'STR',
		'hide' => 'INT',
		'deleted_at' => 'STR'
	];

	protected $rules = [
		'bar' => ['type' => 'alpha']
	];

    function __construct($db = NULL){
		parent::__construct($db);
	}

	function onReading() {
		$this->where(['hide' => 0]);
	}

}







