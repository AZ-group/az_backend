<?php

use simplerest\core\Acl;


$acl_cache = false;
$acl_file = '../app/security/acl.cache';

// Check whether ACL data already exist
if (!$acl_cache || is_file($acl_file) !== true) {

    // ... Define roles, access, etc

    $acl = new Acl();

    $acl
    ->addRole('guest', -1)
    ->addResourcePermissions('products', ['show', 'list'])
    //->setGuest('guest')

    ->addRole('basic', 2)
    ->addInherit('guest')
    ->addResourcePermissions('products', ['write'])
    ->addResourcePermissions('foo', ['read'])

    ->addRole('regular', 3)
    ->addInherit('guest')
    ->addResourcePermissions('products', ['read', 'write'])
    ->addResourcePermissions('foo', ['read', 'update'])

    ->addRole('admin', 100)
    ->addInherit('guest')
    ->addSpecialPermissions(['read_all', 'write_all',  'lock', 'fill_all', 'impersonate'])
    //->restrictImpersonateTo(['guest', 'basic', 'regular'])

    ->addRole('superadmin', 500)
    ->addInherit('admin')
    ->addSpecialPermissions(['read_all_folders',
                             'write_all_folders', 
                             'read_all_trashcan',
                             'write_all_trashcan',
                             'transfer'
                            ]);

    

    // Store serialized list into plain file
    file_put_contents(
        $acl_file,
        serialize($acl)
    );
} else {
    // Restore ACL object from serialized file

    $acl = unserialize(
        file_get_contents($acl_file)
    );
}


//var_dump($acl->getRolePermissions());

return $acl;
