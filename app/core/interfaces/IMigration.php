<?php

namespace simplerest\core\interfaces;

interface IMigration {

    /**
     * Run migration
     *
     * @return void
     */
    function up();

}