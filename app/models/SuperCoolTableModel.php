<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\ValidationRules;
use simplerest\models\schemas\SuperCoolTableSchema;

class SuperCoolTableModel extends Model
 { 
	use SuperCoolTableSchema;
	### PROPERTIES

	protected $hidden   = [];
	protected $not_fillable = [];

    function __construct($db = NULL){
		$this->loadSchema();		
        parent::__construct($db);
	}	
}

