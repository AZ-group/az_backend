<?php
namespace simplerest\models;

use simplerest\core\Model;
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
		'belongs_to' => 'INT' 
	];

	/*
		(1) Por defecto el Id es requerido -por el validator- excepto que se especifique que es nullable.

		(2) Si el Id tiene AUTOINCREMENT debe ser nullable tanto en la DB como en el modelo y ya que por (1) el id es requerido. 
		
		(3) Advertencia: si existe el campo 'belongs_to' en la DB y en el modelo => debe ser nullable porque el ApiController lo va a intentar rellenar.   <-- hay solución?
	*/
	
	protected $nullable = ['uuid'];
	
    function __construct($db = NULL){
		parent::__construct($db);
	}

}







