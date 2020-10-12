<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\models\schemas\BarSchema;

class BarModel extends Model
 { 
	use BarSchema;
	### PROPERTIES

	protected $hidden   = [];

    function __construct($db = NULL){
		$this->loadSchema();		
        parent::__construct($db);
	}	
}

