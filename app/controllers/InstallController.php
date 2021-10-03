<?php

namespace simplerest\controllers;

use simplerest\core\ConsoleController;
use simplerest\core\Model;
use simplerest\libs\Files;

class InstallController extends ConsoleController
{
    function __construct()
    {
        parent::__construct();
        $this->install();
    }

    private function install(bool $continue = false){
        $file = file_get_contents(ETC_PATH . 'db_base.sql');
        
        /*
            Estoy seguro no hay un ; más que para indicar terminación de sentencias
        */
        $sentences = explode(';', $file);

        Model::query('SET foreign_key_checks = 0');
        
        foreach ($sentences as $sentence){
            $sentence = trim($sentence);

            if ($sentence == ''){
                continue;
            }

            dd($sentence, 'SENTENCE');

            try {
                $ok = Model::query($sentence);
            } catch (\Exception $e){
                Files::logger($e->getMessage());

                if ($continue == 1 || $continue = 'on' || $continue == 'yes'){
                    dd($e, 'Sql Exception');
                } else {
                    throw new \Exception($e->getMessage());
                }
            }
        }

        Model::query('SET foreign_key_checks = 1');

        $res = shell_exec("php com make schema all -f --from:main");
        dd($res);

        $res = shell_exec("php com make model all --from:main");
        dd($res);
    }

}

