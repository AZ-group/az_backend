<?php

use simplerest\core\Schema;

use simplerest\libs\Factory;

class CosasLocas 
{
    /**
	* Run migration.
    *
    * @return void
    */
    public function up()
    {
        Factory::config()['db_connection_default'] = 'db2';

		$sc = new Schema('users');

    }
}

