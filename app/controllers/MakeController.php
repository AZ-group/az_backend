<?php

namespace simplerest\controllers;

use simplerest\core\Controller;
use simplerest\core\Request;
use simplerest\libs\Factory;
use simplerest\libs\Debug;
use simplerest\libs\DB;
use simplerest\libs\Strings;

/*
    Class generator

    Commands:

    maker model SuperAwesome
    maker model super_awesome

    maker controller SuperAwesome

    maker api SuperAwesome
    maker api super_awesome

    maker all SuperAwesome

    <-- solo debe funcionar desde la terminal
*/
class MakeController extends Controller
{
    const MODEL_TEMPLATE = CORE_PATH . 'templates' . DIRECTORY_SEPARATOR. 'Model.php';
    const CONTROLLER_TEMPLATE = CORE_PATH . 'templates' . DIRECTORY_SEPARATOR. 'Controller.php';
    const API_TEMPLATE = CORE_PATH . 'templates' . DIRECTORY_SEPARATOR. 'ApiRestfulController.php';

    function __construct()
    {
        if (php_sapi_name() != 'cli'){
            Factory::response()->send("Error: Make can only be excecuted in console", 403);
        }

        parent::__construct();
    }

    function setup($name) {
        $name_lo = strtolower($name);

        if (Strings::endsWith('model', $name_lo)){
            $name = substr($name, 0, -5);
        } elseif (Strings::endsWith('controller', $name_lo)){
            $name = substr($name, 0, -10);
        }

        $name_uc = ucfirst($name);

        if (strpos($name, '_') !== false) {
            $class_name  = Strings::toCamelCase($name);
            $model_table = $name_lo;
        } elseif ($name == $name_lo){
            $model_table = $name;
            $class_name  = ucfirst($name);
        } elseif ($name == $name_uc) {
            $class_name  = $name; 
        }
        
        if (!isset($model_table)){
            $model_table = Strings::fromCamelCase($class_name);
        }

        $this->model_table  = $model_table;
        $this->model_name   = $class_name . 'Model';
        $this->ctr_name     = $class_name . 'Controller';
        $this->api_name     = $class_name;     
        
        //Debug::export($this->model_name,  'model name');
        //Debug::export($this->model_table, 'table name');
    }

    function model($name) { 
        $this->setup($name);    
    
        $filename = $this->model_name.'.php';

        $path = MODELS_PATH . $filename;
        if (file_exists($path)){
            throw new \Exception("File $path alreay exists");
        }

        $data = file_get_contents(self::MODEL_TEMPLATE);
        $data = str_replace('__NAME__', $name, $data);

        $ok = (bool) file_put_contents($path, $data);
        
        if (!$ok) {
            throw new \Exception("Failed trying to write $path");
        } else {
            print_r("$path was generated\r\n");
        } 
    }

    function controller($name) {
        $name = str_replace('/', DIRECTORY_SEPARATOR, $name);
        $namespace = 'simplerest\\controllers';

        $sub_path = '';
        if (strpos($name, DIRECTORY_SEPARATOR) !== false){
            $exp = explode(DIRECTORY_SEPARATOR, $name);
            $sub = implode(DIRECTORY_SEPARATOR, array_slice($exp, 0, count($exp)-1));
            $sub_path = $sub . DIRECTORY_SEPARATOR;
            $name = $exp[count($exp)-1];
            $namespace .= "\\$sub";
        }

        $this->setup($name);    
    
        $filename = $this->ctr_name.'.php';
        $path = CONTROLLERS_PATH . $sub_path . $filename;

        if (file_exists($path)){
            throw new \Exception("File $path alreay exists");
        }

        $data = file_get_contents(self::CONTROLLER_TEMPLATE);
        $data = str_replace('__NAME__', $name, $data);
        $data = str_replace('__NAMESPACE', $namespace, $data);

        $ok = (bool) file_put_contents($path, $data);
        
        if (!$ok) {
            throw new \Exception("Failed trying to write $path");
        } else {
            print_r("$path was generated\r\n");
        } 
    }

    function api($name) { 
        $this->setup($name);    
    
        $filename = $this->api_name.'.php';

        $path = API_PATH . $filename;
        if (file_exists($path)){
            throw new \Exception("File $path alreay exists");
        }

        $data = file_get_contents(self::API_TEMPLATE);
        $data = str_replace('__NAME__', $name, $data);
        $data = str_replace('__SOFT_DELETE__', 'true', $data); // debe depender del schema

        $ok = (bool) file_put_contents($path, $data);
        
        if (!$ok) {
            throw new \Exception("Failed trying to write $path");
        } else {
            print_r("$path was generated\r\n");
        } 
    }

}