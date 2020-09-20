<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class Products extends MyApiController
{ 
    static protected $folder_field = 'workspace';

    function __construct()
    {       
        $this->scope['guest'] = ['read'];
        $this->scope['basic'] = ['read'];
        $this->scope['regular'] = ['read', 'write'];

        parent::__construct();
    }

    
    function onReadingFolderBeforeAuth() {
        echo 'BEFORE AUTH';
        var_dump("Reading folder {$this->folder} with id={$this->id}");
    }

    function onReadingFolderAfterAuth() {
        echo 'AFTER AUTH ';
        var_dump("Reading folder {$this->folder} with id={$this->id}");
    }

    /*
    function onWritingFolder() {
        var_dump("Writing folder {$this->folder} with id={$this->id}");
    }
    */
    
        
} // end class
