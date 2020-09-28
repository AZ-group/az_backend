<?php

namespace simplerest\core;

use simplerest\libs\DB;
use simplerest\libs\Factory;

abstract class Controller
{
    protected $callable = [];

    function __construct() {
        $this->config = include CONFIG_PATH . 'config.php';

        if ($this->config['error_handling']) {
            set_exception_handler([$this, 'exception_handler']);
        }
    }

    function view(string $view_path, array $vars_to_be_passed  = null, $layout = 'app_layout.php'){
        $view = new View($view_path, $vars_to_be_passed, $layout); 
    }

    protected function getConnection() {
        return DB::getConnection();
    }

    function getCallable(){
        return $this->callable;
    }

    function addCallable(string $method){
        $this->callable = array_unique(array_merge($this->callable, [$method]));
    }

    
    /**
     * exception_handler
     *
     * @param  mixed $e
     *
     * @return void
     */
    function exception_handler($e) {
        $error_detail = $this->config['debug'] ? 'Error on line number '.$e->getLine().' in file - '.$e->getFile() : '';
        Factory::response()->sendError($e->getMessage(), 500, $error_detail);
    }

}