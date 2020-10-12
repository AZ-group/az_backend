<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\models\schemas\BazSchema;

class BazModel extends Model
 { 
	use BazSchema;
	### PROPERTIES

	protected $hidden   = [];

    function __construct($db = NULL){
		$this->loadSchema();		
        parent::__construct($db);
	}	
}

