<?php

namespace simplerest\controllers;

class ProductsController extends MyController
{
	function index(){
		return view('products.php');
	}
}
	