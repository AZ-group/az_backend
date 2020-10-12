<?php
namespace simplerest\models;

use simplerest\models\base\ProductsModelBase;
use simplerest\libs\ValidationRules;

class ProductsModel extends ProductsModelBase
{
	function __construct($db = NULL){
		$this->addRules((new ValidationRules())
			//->field('id')->type('int')->required()
			//->field('name')->type('str')->max(40)->min(3)
			//->field('description')->type('str')->max(50)
			//->field('cost')->min('5')
			//->field('size')->type('str')->max(20)
			//->field('workspace')->type('str')->max(20)
			//->field('active')->type('bool', 'Value should be 0 or 1')
		);	
		parent::__construct($db);
	}
}







