<?php

    // App bootstraping

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require __DIR__.'/../vendor/autoload.php';

    $config = include __DIR__ . '/../config/config.php';

    foreach (new \DirectoryIterator(HELPERS_PATH) as $fileInfo) {
        if($fileInfo->isDot()) continue;
        
        $path = $fileInfo->getPathName();

        if(pathinfo($path, PATHINFO_EXTENSION) == 'php'){
            require_once $path;
        }
    }   

    foreach ($config['providers'] as $provider){
        $p = new $provider();
        $p->boot();
    }
    