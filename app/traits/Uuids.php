<?php

namespace simplerest\traits;

trait Uuids
{
    protected function boot()
    {
        parent::boot();

        $this->registerInputMutator('uuid', function($id){ 
			return $id == NULL ? uuid_create(UUID_TYPE_RANDOM) : $id; 
		});
    }    
}
