<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;
use simplerest\libs\ValidationRules;
use simplerest\models\schemas\RecoleccionSchema;

class RecoleccionModel extends Model
{ 
	protected $hidden   = [];
	protected $not_fillable = [];

    function __construct(bool $connect = false){
        parent::__construct($connect, new RecoleccionSchema());
	}

}

