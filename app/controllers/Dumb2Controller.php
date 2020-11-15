<?php

namespace simplerest\controllers;

use simplerest\core\Controller;
use simplerest\core\Model;
use simplerest\core\Request;
use simplerest\libs\Factory;
use simplerest\libs\Debug;
use simplerest\libs\DB;
use simplerest\libs\Utils;
use simplerest\libs\Strings;
use simplerest\libs\Arrays;
use simplerest\libs\Validator;
use simplerest\libs\Files;
use simplerest\libs\Time;
use simplerest\core\Schema;
use simplerest\core\Route;
use simplerest\models\BarModel;
use simplerest\models\UsersModel;
use simplerest\models\ProductsModel;
use simplerest\models\UserRolesModel;
use PHPMailer\PHPMailer\PHPMailer;

class Dumb2Controller extends Controller
{
    function __construct()
    {
        parent::__construct();        
    }


    function speed()
    {
    	define('TIMES', 1);

    	$pre_compiled_sql = 'SELECT * FROM products  WHERE (cost > ?) AND deleted_at ?';
		$bindings = [1.05, 'IS NULL'];

        Time::setUnit('MILI');

        // la función que se ejecuta después es siempre más rápida
        // cuando es sobre los mismos datos


     	$t2 = Time::exec(function() use($pre_compiled_sql, $bindings) {
            Arrays::str_replace_array('?', $bindings, $pre_compiled_sql);
        }, TIMES);

        $t1 = Time::exec(function() use($pre_compiled_sql, $bindings) {
            Arrays::str_replace_array_old('?', $bindings, $pre_compiled_sql);
        }, TIMES);
        
   
        $dt = $t2 - $t1;
        $pd = ($t2 / $t1) * 100;
        $pd = substr($pd, 0, 3);

        dd("Time: $t1 ms (old)");
        dd("Time: $t2 ms");
        dd("Time diff: $dt ($pd%)");
    }

    function s1()
    {
    	$pre_compiled_sql = 'SELECT * FROM products  WHERE (cost > ?) AND deleted_at ?';
		$bindings = [1.05, 'IS NULL'];

        Time::setUnit('MILI');

        // la función que se ejecuta después es siempre más rápida
        // cuando es sobre los mismos datos

        $t = Time::exec(function() use($pre_compiled_sql, $bindings) {
            Arrays::str_replace_array_old('?', $bindings, $pre_compiled_sql);
        }, 1);

        dd("Time: $t ms");
    }

    function s2()
    {
    	$pre_compiled_sql = 'SELECT * FROM products  WHERE (cost > ?) AND deleted_at ?';
		$bindings = [1.05, 'IS NULL'];

        Time::setUnit('MILI');

        // la función que se ejecuta después es siempre más rápida
        // cuando es sobre los mismos datos

        $t = Time::exec(function() use($pre_compiled_sql, $bindings) {
            Arrays::str_replace_array('?', $bindings, $pre_compiled_sql);
        }, 1);

        dd("Time: $t ms");
    }



}

