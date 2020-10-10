<?php

namespace simplerest\traits;

use simplerest\libs\Factory;
use simplerest\libs\DB;

trait ExceptionHandler
{
    /**
     * exception_handler
     *
     * @param  mixed $e
     *
     * @return void
     */
    function exception_handler($e) {
        DB::closeAllConnections();

        $error_detail = $this->config['debug'] ? 'Error on line number '.$e->getLine().' in file - '.$e->getFile() : '';
        
        if (php_sapi_name() == 'cli'){
            print_r("$error_detail\r\n");
            print_r($e->getMessage() . "\r\n");
        } else {
            Factory::response()->sendError($e->getMessage(), 500, $error_detail);
        }   
    }
    
}
