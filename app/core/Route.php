<?php

namespace simplerest\core;

use simplerest\libs\Url;
//use simplerest\core\Model;
//use simplerest\libs\DB;
use simplerest\libs\Factory;
use simplerest\libs\Debug;

class Route 
{
    protected static $routes  = [];
    protected static $params;
    protected static $current = [];

    public function __construct() {
        global $argv;
        
        // convertir en clase
        $config = include '../config/config.php';

        if (php_sapi_name() != 'cli'){
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $path = preg_replace('/(.*)\/index.php/', '/', $path);
    
            if ($config['BASE_URL'] != '/' && strpos($path, $config['BASE_URL']) === 0) {
                $path = substr($path, strlen($config['BASE_URL']));
            }   
    
            if ($path === false || ! Url::url_check($_SERVER['REQUEST_URI']) )
                Response::getInstance()->sendError('Malformed url', 400); 
    
            $_params = explode('/', $path);
    
            if (empty($_params[0]))  
                array_shift($_params);
        }else{
            $_params = array_slice($argv, 1);
        }

        static::$params = $_params;
        Debug::dd(static::$params);
    }

    public static function resolve(){
        //Debug::dd(static::$routes);
        
        $req_method = $_SERVER['REQUEST_METHOD'] ?? NULL;
        
        // cli 
        if ($req_method == NULL){
            // return;
        }
        
        if (!isset(static::$routes[$req_method])){
            return;
        }

        $callbacks = static::$routes[$req_method];

        foreach($callbacks as $uri => $ck){
            $slugs = explode('/', $uri);

            if (count(static::$params) <  count($slugs)){
                return;
            }

            foreach ($slugs as $k => $sl){
                if (!isset(static::$params[$k]) ||  static::$params[$k] != $sl){
                    return;
                }
            }

            //Debug::dd($uri, 'uri'); 
            //Debug::dd($ck);

            if (is_callable($ck)){
                $data = $ck(...[4,5]);
                Response::getInstance()->send($data);
            }
        }
    }

    /*
     Register
    */

    public static function where($arr){
        static::$routes[static::$current[0]][static::$current[1]];
    }

    public static function get(string $uri, $callback){
        static::$routes['GET'][$uri] = $callback;
        static::$current = ['GET', $uri];
    }

    public static function post(string $uri, $callback){
        static::$routes['POST'][$uri] = $callback;
        static::$current = ['POST', $uri];
    }

    public static function put(string $uri, $callback){
        static::$routes['PUT'][$uri] = $callback;
        static::$current = ['PUT', $uri];
    }

    public static function patch(string $uri, $callback){
        static::$routes['PATCH'][$uri] = $callback;
        static::$current = ['PATCH', $uri];
    }

    public static function delete(string $uri, $callback){
        static::$routes['DELETE'][$uri] = $callback;
        static::$current = ['DELETE', $uri];
    }
    
    public static function options(string $uri, $callback){
        static::$routes['OPTIONS'][$uri] = $callback;
        static::$current = ['OPTIONS', $uri];
    }

}

