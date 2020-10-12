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

    maker schema SuperAwesome [-f | --force]
    maker schema super_awesome  [-f | --force]

    maker model SuperAwesomeModel  [-f | --force]
    maker model SuperAwesome [-f | --force]
    maker model super_awesome  [-f | --force]

    maker controller SuperAwesome  [-f | --force]

    maker api SuperAwesome  [-f | --force]
    maker api super_awesome  [-f | --force]

    maker any SuperAwesome  [ -s | --schema ] 
                            [ -m | --model] 
                            [-c | --controller ] 
                            [ -a | --api ] 
                            [-f | --force]
*/
class MakeController extends Controller
{
    const SCHEMAS_PATH = MODELS_PATH . 'schemas' . DIRECTORY_SEPARATOR;

    const MODEL_TEMPLATE  = CORE_PATH . 'templates' . DIRECTORY_SEPARATOR. 'Model.php';
    const SCHEMA_TEMPLATE = CORE_PATH . 'templates' . DIRECTORY_SEPARATOR. 'Schema.php';
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
        $this->class_name   = $class_name;
        $this->model_name   = $class_name . 'Model';
        $this->ctr_name     = $class_name . 'Controller';
        $this->api_name     = $class_name;     
        
        //Debug::export($this->model_name,  'model name');
        //Debug::export($this->model_table, 'table name');
    }

    function controller($name, ...$opt) {
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

        if (file_exists($path) && !in_array('-f', $options) && !in_array('--force', $options)){
            throw new \Exception("File $path alreay exists. Use -f or --force if you want to override.");
        }

        $data = file_get_contents(self::CONTROLLER_TEMPLATE);
        $data = str_replace('__NAME__', $this->ctr_name, $data);
        $data = str_replace('__NAMESPACE', $namespace, $data);

        $ok = (bool) file_put_contents($path, $data);
        
        if (!$ok) {
            throw new \Exception("Failed trying to write $path");
        } else {
            print_r("$path was generated\r\n");
        } 
    }

    function api($name, ...$options) { 
        $this->setup($name);    
    
        $filename = $this->api_name.'.php';

        $path = API_PATH . $filename;
        if (file_exists($path) && !in_array('-f', $options) && !in_array('--force', $options)){
            throw new \Exception("File $path alreay exists. Use -f or --force if you want to override.");
        }

        $data = file_get_contents(self::API_TEMPLATE);
        $data = str_replace('__NAME__', $this->api_name, $data);
        $data = str_replace('__SOFT_DELETE__', 'true', $data); // debe depender del schema

        $ok = (bool) file_put_contents($path, $data);
        
        if (!$ok) {
            throw new \Exception("Failed trying to write $path");
        } else {
            print_r("$path was generated\r\n");
        } 
    }

    function schema($name) { 
        $this->setup($name);    
    
        $filename = $this->class_name.'Schema.php';

        // destination
        $dest_path = self::SCHEMAS_PATH . $filename;

        $file = file_get_contents(self::SCHEMA_TEMPLATE);
        $file = str_replace('__NAME__', $this->class_name.'Schema', $file);

        $fields = DB::select("SHOW COLUMNS FROM {$this->model_table}");
        
        $id_name =  NULL;
        $uuid = false;
        $field_names  = [];
        $types = [];
        $types_raw = [];

        $nullables = [];
        $not_fillable = [];
        $rules = [];

        $get_pdo_const = function (string $sql_type){
            if (preg_match('/int\([0-9]+\)$/', $sql_type) == 1){
                return 'INT';
            } else {
                return 'STR';
            }
        }; 

        foreach ($fields as $field){
            $field_names[] = $field['Field'];
            if ($field['Null']  == 'YES') { $nullables[] = $field['Field']; }
            
            if ($field['Key'] == 'PRI'){ 
                if ($id_name != NULL){
                    throw new \Exception("Only one Primary Key is allowed by convention");
                }
                
                $id_name = $field['Field'];
            }
            if ($field['Extra'] == 'auto_increment') { 
                //$not_fillable[] = $field['Field'];
                $nullables[] = $field['Field']; 
            }
            $types[$field['Field']] = $get_pdo_const($field['Type']);
            $types_raw[$field['Field']] = $field['Type'];

            if ($field['Key'] == 'PRI'){ 
                $field_name_lo = strtolower($field['Field']);
                if ($field_name_lo == 'uuid' || $field_name_lo == 'guid'){
                    if ($types[$field['Field']] != 'STR'){
                        printf("Warning: {$field['Field']} has not a valid type for UUID ***\r\n");
                    }

                    $uuid = true;
                }
            }    
        }

        if ($id_name == NULL){
            throw new \Exception("No Primary Key found!");
        }

        $nullables = array_unique($nullables);

        $escf = function($x){ 
            return "'$x'"; 
        };

        $_schema = [];
        $_rules  = [];
        foreach ($types as $f => $type){
            $_schema[] = "\t\t\t'$f' => '$type'";

            if (preg_match('/^varchar\(([0-9]+)\)$/', $types_raw[$f], $matches)){
                $len = $matches[1];
                $_rules [] = "\t\t\t'$f' => ['max' => $len]";
            }
        }

        $schema = "[\r\n". implode(",\r\n", $_schema). "\r\n\t\t]";
        $rules  = "[\r\n". implode(",\r\n", $_rules). "\r\n\t\t]";

        if ($uuid){
            $nullables[] = $id_name;
            Strings::replace('### IMPORTS', 'use simplerest\traits\Uuids;', $file); 
            Strings::replace('### TRAITS', "use Uuids;", $file);        
        }

        Strings::replace('__ID__', "'$id_name'", $file);  
        Strings::replace('__SCHEMA__', $schema, $file);
        Strings::replace('__NULLABLES__', '['. implode(', ',array_map($escf, $nullables)). ']',$file);        
        Strings::replace('__NOT_FILLABLE__', '['.implode(', ',array_map($escf, $not_fillable)). ']',$file);
        Strings::replace('__RULES__', $rules, $file);
        

        $ok = (bool) file_put_contents($dest_path, $file);
        
        if (!$ok) {
            throw new \Exception("Failed trying to write $dest_path");
        } else {
            print_r("$dest_path was generated\r\n");
        } 
    }

    function model($name, ...$options) { 
        $this->setup($name);    

        $filename = $this->model_name.'.php';

        // destination
        $dest_path = MODELS_PATH . $filename;
      
        if (file_exists($dest_path) && !in_array('-f', $options) && !in_array('--force', $options)){
            throw new \Exception("File $dest_path alreay exists. Use -f or --force if you want to override.");
        }

        $file = file_get_contents(self::MODEL_TEMPLATE);
        $file = str_replace('__NAME__', $this->class_name.'Model', $file);

        Strings::replace('### IMPORTS', "use simplerest\\models\\schemas\\{$this->class_name}Schema;", $file); 
        Strings::replace('### TRAITS', "use {$this->class_name}Schema;", $file); 
            
        $ok = (bool) file_put_contents($dest_path, $file);
        
        if (!$ok) {
            throw new \Exception("Failed trying to write $dest_path");
        } else {
            print_r("$dest_path was generated\r\n");
        } 
    }
}