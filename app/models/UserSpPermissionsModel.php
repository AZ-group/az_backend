<?php
namespace simplerest\models;

use simplerest\core\Model;


class UserSpPermissionsModel extends Model 
{
	protected $table_name = "user_sp_permissions";
	protected $id_name = 'id';

	protected $schema = [
		'id' 				=> 'INT',
		'sp_permission_id' 	=> 'INT',
		'user_id' 			=> 'INT',
		//'created_by' 		=> 'INT',
		'created_at' 		=> 'STR',
		'updated_at' 		=> 'STR'
	];

	protected $rules = [
	];

    function __construct($db = NULL){
		parent::__construct($db);
	}

}







