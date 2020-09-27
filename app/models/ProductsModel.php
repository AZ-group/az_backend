<?php
namespace simplerest\models;

use simplerest\core\Model;

/*
	Product extends Model to have access to reflection
	Another way could be to use traits 
*/
class ProductsModel extends Model 
{
 
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

	/*
		(1) Por defecto el Id es requerido -por el validator- excepto que se especifique que es nullable.

		(2) Si el Id tiene AUTOINCREMENT debe ser nullable tanto en la DB como en el modelo y ya que por (1) el id es requerido. 
		
		(3) Advertencia: si existe el campo 'belongs_to' en la DB y en el modelo => debe ser nullable porque el ApiController lo va a intentar rellenar.   <-- hay solución?
	*/
	
	protected $nullable = ['id', 'description', 'size', 'active', 'locked', 'workspace', 'created_at', 'updated_at', 'deleted_at', 'deleted_by' ];


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







