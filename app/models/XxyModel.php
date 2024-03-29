<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\ValidationRules;
use simplerest\models\schemas\db1\XxySchema;

class XxyModel extends Model
{ 
	
	

	protected $hidden   = [];
	protected $not_fillable = [];

    function __construct(bool $connect = false){
        parent::__construct($connect, new XxySchema());
	}	
}

