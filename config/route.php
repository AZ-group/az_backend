<?php

use simplerest\core\Route;
use simplerest\libs\Debug;

$route = new Route();

Route::get('calc/sum', function($a, $b){
    return "La suma de $a y $b da ". ($a + $b);
}) /* ->where(['a' => '[0-9]+', 'b' =>'[0-9]+']) */ ;

Route::get('tonterias',  'DumbController');
Route::get('chatbot/hi', 'DumbController@hi');

Route::delete('cosas', function($id){
    return "Deleting cosa con id = $id";
});
