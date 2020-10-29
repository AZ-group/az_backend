<?php

namespace simplerest\controllers;

use simplerest\core\Controller;
use simplerest\core\Request;
use simplerest\core\Response;
use simplerest\core\Schema;
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
            
            require_once MIGRATIONS_PATH . DIRECTORY_SEPARATOR . $filename;

            if (!class_exists($class_name)){
                throw new \Exception ("Class '$class_name' does not exists in $filename");
            }

            echo "Migrating '$filename'\r\n";
            (new $class_name())->up();
            echo "Migrated  '$filename' --ok\r\n";
        }         
    }

    /*
        Regresa migraciones (por defecto solo una)

        Debe ir borrando registros de `migrations` excepto una migración tenga batch=0 en cuyo caso la saltea
    */
    function rollback(...$opt) 
    {
        $steps = 1;
        if (isset($opt[0]) && $opt[0] !== NULL){
            $steps = Strings::slice($opt[0], '/^--step=([0-9]+)$/');
            
            if ($opt[0] == '--all'){
                $steps = PHP_INT_MAX;
            }
        }

        $filenames = [];
        foreach (new \DirectoryIterator(MIGRATIONS_PATH) as $fileInfo) {
            if($fileInfo->isDot()) continue;
            $filenames[] = $fileInfo->getFilename();
        }    

        $filenames = array_reverse($filenames);

        $cnt = min($steps, count($filenames));
        for ($i=0; $i<$cnt; $i++){
            $filename   = $filenames[$i];            
            $class_name = Strings::toCamelCase(substr(substr($filename,18),0,-4));

            require_once MIGRATIONS_PATH . DIRECTORY_SEPARATOR . $filename;

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
        $this->rollback("--all");
    }

    /*
        The command will drop all tables from the database (incluso las que no están afectadas por las migraciones) and then execute the "migrate" command

        --seed corre todos los db seeders

        Dropped all tables successfully. * 
        Migration table created successfully. *
        Migrating: 2014_10_12_000000_create_users_table
        Migrated:  2014_10_12_000000_create_users_table
        Migrating: 2014_10_12_100000_create_password_resets_table
        Migrated:  2014_10_12_100000_create_password_resets_table

        <--- para no usar el método down()

        Nota: Comienza haciendo un TRUNCATE de la tabla `migrations` o sea borra todo inclusive los campos con  batch=0

    */
    function fresh() {
        Schema::FKcheck(0);
        
        $tables = Schema::getTablesFromCurrentDb();

        $conn = DB::getConnection();   
        try{
            foreach($tables as $table){

                echo "Dropping table '$table'\r\n";
                $st = $conn->prepare("DROP TABLE IF EXISTS `$table`;");
                $res = $st->execute();
                echo "Dropped table  '$table' --ok\r\n";

                $this->migrate();
            }
        } catch (\PDOException $e) {    
            Debug::dd("DROP TABLE for `$table` failed", "PDO Error");
            Debug::dd($e->getMessage(), "MSG"); 
            throw $e;
        } catch (\Exception $e) {   
            Debug::dd($e->getMessage(), "MSG");
            throw $e;
        }	             

        Schema::FKcheck(1);
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

        Lo que hace exactamente es un reset() seguido un migrate()
    */
    function refresh() {
        $this->reset();
        $this->migrate();
    }
}

