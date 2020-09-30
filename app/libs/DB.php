<?php

namespace simplerest\libs;

use simplerest\core\Model;

class DB {

	public static $conn;
	public static $instance;  

    private function __construct() { }

    public static function getConnection($options = null) {
		if (self::$conn != null)
			return self::$conn;

		$config = include CONFIG_PATH . 'config.php';

        $db_name = $config['database']['db_name'];
		$host    = $config['database']['host'] ?? 'localhost';
		$user    = $config['database']['user'] ?? 'root';
		$pass    = $config['database']['pass'] ?? '';
		
		try {
			if (empty($options)){
				$options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
				//$options[\PDO::ATTR_EMULATE_PREPARES] = false;  /* es posible desactivar ? */
			}
				
			self::$conn = new \PDO("mysql:host=" . $host . ";dbname=" . $db_name, $user, $pass, $options);
            self::$conn->exec("set names utf8");
		} catch (\PDOException $e) {
			throw new \PDOException($e->getMessage());
		}	
		
		return self::$conn;
	}
		
	// Returns last executed query 
	public static function getQueryLog(){
		return static::$instance->getLog();
	}
	
	public static function table($from, $alias = NULL) {

		// Usar un wrapper y chequear el tipo
		if (stripos($from, ' FROM ') === false){
			$tb_name = $from;
		
			$names = explode('_', $tb_name);
			$names = array_map(function($str){ return ucfirst($str); }, $names);
			$instance = implode('', $names).'Model';		

			$class = '\\simplerest\\models\\' . $instance;
			$obj = new $class(self::getConnection(), $alias);
			
			if (!is_null($alias))
				$obj->setTableAlias($alias);

			static::$instance = $obj;			
			return $obj;	
		}

		$instance = new Model(self::getConnection());
		static::$instance = $instance;

		$st = ($instance)->fromRaw($from);	
		return $st;
	}

	public static function beginTransaction(){
		/* 
		  Not much to it! Forcing PDO to throw exceptions instead errors is the key to being able to use the try / catch which simplifies the logic needed to perform the rollback.
		*/
		static::getConnection()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		static::getConnection()->beginTransaction();
	}

	public static function commit(){
		static::getConnection()->commit();
	}

	public static function rollback(){
		static::getConnection()->rollback();
	}

	// https://github.com/laravel/framework/blob/4.1/src/Illuminate/DB/Connection.php#L417
	public static function transaction(\Closure $callback)
    {
		static::beginTransaction();

		try
		{
			$result = $callback();
			static::commit();
		}catch (\Exception $e){
			static::rollBack();
			throw $e;
		}

		return $result;
    }
		
}
