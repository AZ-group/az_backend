<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\ValidationRules;
use simplerest\models\schemas\FooSchema;

class FooModel extends Model
{ 
	protected $hidden       = [];
	protected $not_fillable = [];
	protected $createdAt = 'alta';
	protected $updatedAt = 'fecha_modificacion';
	protected $deletedAt = 'fecha_borrado'; 
	protected $createdBy = 'creado_por';
	protected $updatedBy = 'modificado_por';
	protected $deletedBy = 'borrado_por'; 

    function __construct(bool $connect = false){
        parent::__construct($connect, new FooSchema());
	}	
}

