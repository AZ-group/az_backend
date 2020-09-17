<?php
namespace simplerest\models;

use simplerest\core\Model;

/*
	Product extends Model to have access to reflection
	Another way could be to use traits 
*/
class ProductsModel extends Model 
{
	protected $nullable = ['description', 'size', 'active', 'locked', 'workspace', 'created_at', 'updated_at', 'deleted_at', 'deleted_by' ];
 
	/*
		Types are INT, STR and BOOL among others
		see: https://secure.php.net/manual/en/pdo.constants.php 
	*/
	protected $schema = [
		'id' => 'INT',
		'name' => 'STR',
		'description' => 'STR',
		'size' => 'STR',
		'cost' => 'INT',
		'workspace' => 'STR',
		'created_at' => 'STR',
		'created_by' => 'INT',
		'updated_at' => 'STR',
		'updated_by' => 'INT',
		'deleted_at' => 'STR',
		'deleted_by' => 'INT',
		'active' => 'INT',
		'locked' => 'INT',		 
		'belongs_to' => 'INT' 
	];

	protected $rules = [
        'name' 			=> ['min'=>3, 'max'=>40],
		'description' 	=> ['max'=>50],
		'size' 			=> ['max'=>20],
		'workspace'		=> ['max'=>20],
		'active'		=> ['type' => 'bool', 'messages' => [ 'type' => 'Value should be 0 or 1'] ]
	];

    function __construct($db = NULL){
		parent::__construct($db);
	}

}







