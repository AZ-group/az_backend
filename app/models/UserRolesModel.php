<?php
namespace simplerest\models;

use simplerest\core\Model;

class UserRolesModel extends Model
 {
	protected $table_name = "user_roles";
	protected $id_name = 'id';

	/*
		Types are INT, STR and BOOL among others
		see: https://secure.php.net/manual/en/pdo.constants.php 
	*/
	protected $schema = [
		'id' => 'INT',
		'user_id' => 'INT',
		'role_id' => 'INT',
		'created_at'  => 'STR',
		'updated_at'  => 'STR'
	];

	protected $nullable = ['id'];
	protected $hidden = [ ];

	protected $rules = [
	
	];

    function __construct($db = NULL){
        parent::__construct($db);
    }
	
	
}