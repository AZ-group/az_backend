<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    require __DIR__.'/vendor/autoload.php';

    use simplerest\core\FrontController;

    $config = include __DIR__ . '/config/config.php';

        
    FrontController::resolve();
 	



