<?php

use simplerest\core\Route;
use simplerest\libs\Factory;

function route(string $name){
    return Route::getRouteByName($name);
}

function protocol(){
    $config = Factory::config();

    if ($config['HTTPS'] == 1 || strtolower($config['HTTPS']) == 'on'){
        $protocol = 'https';
    } else {
        $protocol = 'http';
    }

    return $protocol;
}