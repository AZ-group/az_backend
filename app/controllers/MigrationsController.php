<?php

namespace simplerest\controllers;

use simplerest\core\Controller;
use simplerest\core\Request;
use simplerest\core\Response;
use simplerest\libs\Factory;
use simplerest\libs\DB;
use simplerest\libs\Strings;
use simplerest\libs\Debug;

class MigrationsController extends Controller
{
    function make($name, ...$opt) {
        return (new MakeController)->migration($name, $opt);
    }
    
    /*
        Implementar --force para ejecutar en migración

        Migrating: 2014_10_12_000000_create_users_table
        Migrated:  2014_10_12_000000_create_users_table
        Migrating: 2014_10_12_100000_create_password_resets_table
        Migrated:  2014_10_12_100000_create_password_resets_table
        Migrating: 2020_10_28_145609_as_d_f
        Migrated:  2020_10_28_145609_as_d_f

    */
    function migrate() {
        foreach (new \DirectoryIterator(MIGRATIONS_PATH) as $fileInfo) {
            if($fileInfo->isDot()) continue;

            $filename   = $fileInfo->getFilename();            
            $class_name = Strings::toCamelCase(substr(substr($filename,18),0,-4));
            
            require MIGRATIONS_PATH . DIRECTORY_SEPARATOR . $filename;

            if (!class_exists($class_name)){
                throw new \Exception ("Class '$class_name' does not exists in $filename");
            }

            echo "Migrating '$filename'\r\n";
            (new $class_name())->up();
            echo "Migrated  '$filename' --ok\r\n";
        }         
    }

    /*
        Implementar: 
        
        --step=N donde N es la cantidad de pasos hacia atrás (por defecto usar N=1)
        --all 

        Las debe borrar? o solo des-hacer y mover el puntero?
    */
    function rollback() {
        foreach (new \DirectoryIterator(MIGRATIONS_PATH) as $fileInfo) {
            if($fileInfo->isDot()) continue;

            $filename   = $fileInfo->getFilename();            
            $class_name = Strings::toCamelCase(substr(substr($filename,18),0,-4));
            
            require MIGRATIONS_PATH . DIRECTORY_SEPARATOR . $filename;

            if (!class_exists($class_name)){
                throw new \Exception ("Class '$class_name' does not exists in $filename");
            }

            echo "Rolling back '$filename'\r\n";
            (new $class_name())->down();
            echo "Rolled back  '$filename' --ok\r\n";
        }     
    }

    /*
        Rollback de todas las migraciones. Equivale a "rollback --all"
    */
    function reset() {

    }

    /*
        The command will drop all tables from the database and then execute the "migrate" command

        --seed corre todos los db seeders

        Dropped all tables successfully. * 
        Migration table created successfully. *
        Migrating: 2014_10_12_000000_create_users_table
        Migrated:  2014_10_12_000000_create_users_table
        Migrating: 2014_10_12_100000_create_password_resets_table
        Migrated:  2014_10_12_100000_create_password_resets_table

        <--- para uno usar el método down()

    */
    function fresh() {

    }

    /*
        Rolling back: 2014_10_12_100000_create_password_resets_table
        Rolled back:  2014_10_12_100000_create_password_resets_table
        Rolling back: 2014_10_12_000000_create_users_table
        Rolled back:  2014_10_12_000000_create_users_table
        Migrating: 2014_10_12_000000_create_users_table
        Migrated:  2014_10_12_000000_create_users_table
        Migrating: 2014_10_12_100000_create_password_resets_table
        Migrated:  2014_10_12_100000_create_password_resets_table
    */
    function refresh() {

    }
}

