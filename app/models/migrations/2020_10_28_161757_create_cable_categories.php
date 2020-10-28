<?php

use simplerest\core\Schema;

class CreateCableCategories /* implements IMigration */
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
}

