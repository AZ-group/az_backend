<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class Products extends MyApiController
{ 
    static protected $folder_field = 'workspace';

    function __construct()
    {       
        $this->scope['guest'] = ['read'];
        $this->scope['basic'] = ['read', 'write'];
        $this->scope['regular'] = ['read', 'write'];

        parent::__construct();
    }

    
    function onReadingFolderBeforeAuth() {

        if ($this->isGuest()){
            // Informar que debe estar "logueado"
            return;
        }

        if ($this->isAdmin()){
            return;
        }

        $token = \simplerest\libs\Factory::request()->getQuery('token');
    
        // decodificar token y si es válido proseguir
        
        $uid = $this->auth->uid;
        $folder = $this->folder;

        // insertar en la tabla folder_permissions el permiso para el usuario con id $uid`
        // y el folder  $folder
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
