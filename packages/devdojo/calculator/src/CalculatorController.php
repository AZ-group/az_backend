<?php

namespace Devdojo\Calculator;

use simplerest\core\Controller;
use simplerest\core\Request;
use simplerest\core\Response;
use simplerest\libs\Factory;
use simplerest\libs\DB;

class CalculatorController extends Controller
{
    public function add($a, $b){
    	echo $a + $b;
    }

    public function subtract($a, $b){
    	echo $a - $b;
    }
}

