<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\ValidationRules;
use simplerest\models\schemas\CollectionsSchema;

class CollectionsModel extends Model
 { 
	use CollectionsSchema;
	### PROPERTIES

	protected $hidden   = [];

    function __construct($db = NULL){
		$this->loadSchema();		
        parent::__construct($db);
	}	
}

