<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\ValidationRules;
use simplerest\models\schemas\FilesSchema;

### readonly
class FilesModel extends Model
{ 
	use FilesSchema;
	### PROPERTIES

	protected $hidden   = [];
	protected $not_fillable = ['filename_as_stored']; // <--- evitar sobre-escritura via .make_ignore

    function __construct($db = NULL){
		$this->loadSchema();		
		parent::__construct($db);
	}	
}

