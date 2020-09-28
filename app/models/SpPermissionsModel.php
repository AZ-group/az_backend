<?php
namespace simplerest\models;

use simplerest\core\Model;


class SpPermissionsModel extends Model 
{
	protected $table_name = "sp_permissions";
	protected $id_name = 'id';

	protected $schema = [
		'id' 				=> 'INT',
		'name' 	=> 'STR'
	];

	protected $rules = [
	];

    function __construct($db = NULL){
		parent::__construct($db);
	}

}







