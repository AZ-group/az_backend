<?php

use simplerest\core\Route;
use simplerest\core\Response;
use simplerest\libs\Factory;


function route(string $name){
    return Route::getRouteByName($name);
}

function http_protocol(){
    $config = Factory::config();

    if ($config['HTTPS'] == 1 || strtolower($config['HTTPS']) == 'on'){
        $protocol = 'https';
    } else {
        $protocol = 'http';
    }

    return $protocol;
}

function redirect(string $url){
    return Response::redirect($url);
}