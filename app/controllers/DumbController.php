<?php

namespace simplerest\controllers;

use simplerest\core\Controller;
use simplerest\core\Request;
use simplerest\libs\Factory;
use simplerest\libs\Debug;
use simplerest\libs\DB;
use simplerest\core\Model;
use simplerest\models\KidsArModel;
use PHPMailer\PHPMailer\PHPMailer;
use simplerest\libs\Utils;
use simplerest\libs\Strings;
use simplerest\libs\Validator;
use simplerest\libs\Files;
use simplerest\libs\Time;
use simplerest\core\Schema;

class DumbController extends Controller
{
    function import(){
        $json = file_get_contents("/home/www/missing_childs_parser/missingchildren_ar.json"); 
        $kids = json_decode($json, true);
        
        $kid = $kids[10];
        unset($kid['extras']);

        //Debug::dd($kid);
        
        foreach ((array) $kids as $ix => $kid){
            if ($kid['found']){
                continue;
            }
            
            $photo = $kid['file_url'];

            unset($kid['file_url']);
            unset($kid['extras']);

            $id = DB::table('kids_ar')->create(
                $kid        
            ); 
        }
        
    }

}