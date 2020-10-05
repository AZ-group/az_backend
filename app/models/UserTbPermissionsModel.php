<?php
namespace simplerest\models;

use simplerest\core\Model;


class UserTbPermissionsModel extends Model 
{
	protected $table_name = "user_tb_permissions";
	protected $id_name = 'id';

	protected $schema = [
		'id' 			=> 'INT',
		'tb' 			=> 'STR',
		'user_id' 		=> 'INT',
		'can_list_all'	=> 'INT',
		'can_show_all'	=> 'INT', 
		'can_list'		=> 'INT',
		'can_show' 		=> 'INT', 
		'can_create'	=> 'INT',
		'can_update' 	=> 'INT',
		'can_delete'	=> 'INT',
		'created_by' 	=> 'INT',
		'created_at' 	=> 'STR',
		'updated_at' 	=> 'STR'
	];

	protected $rules = [
	];

    function __construct($db = NULL){
		parent::__construct($db);
	}

}







