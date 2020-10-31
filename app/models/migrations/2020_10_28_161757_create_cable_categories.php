<?php

use simplerest\core\Schema;
use simplerest\core\interfaces\IMigration;

class CreateCableCategories implements IMigration
{
    /**
	* Run migration.
    *
    * @return void
    */
    public function up()
    {
        $s = new Schema('cable_categories');

        $s
        ->int('id')->unsigned()->auto()->pri()
        ->varchar('nombre', 40)
        ->int('id_parent_category')->unsigned()->nullable()->index()
        ->fk('id_parent_category')->references('id')->on('cable_categories')

        ->create();
    }

    public function down()
    {
        Schema::drop('cable_categories');
    }
}

