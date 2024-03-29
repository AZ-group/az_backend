<?php

use simplerest\core\Schema;
use simplerest\core\interfaces\IMigration;

class CreateCables implements IMigration
{
    /**
	* Run migration.
    *
    * @return void
    */
    public function up()
    {
        $sc = new Schema('cables');

        $sc
        ->int('id')->unsigned()->auto()->pri()
        ->varchar('nombre', 40)
        ->float('calibre')

        ->create();
    }

    public function down()
    {
        Schema::drop('cables');
    }
}

