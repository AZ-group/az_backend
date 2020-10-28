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

    */
    function migrate() {
        foreach (new \DirectoryIterator(MIGRATIONS_PATH) as $fileInfo) {
            if($fileInfo->isDot()) continue;
           
            $class_name = Strings::toCamelCase(substr(substr($fileInfo->getFilename(),18),0,-4));
            Debug::dd($class_name);

            require MIGRATIONS_PATH . DIRECTORY_SEPARATOR . $fileInfo->getFilename();
            (new $class_name())->up();
        }         
    }

    /*
        Implementar: 
        
        --step=N donde N es la cantidad de pasos hacia atrás (por defecto usar N=1)
        --all 

        Las debe borrar? o solo des-hacer y mover el puntero?
    */
    function rollback() {

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

