<?php

use boctulus\grained_acl\Acl;
use simplerest\libs\Debug;


$acl_cache = false;
$acl_file = '../app/security/acl.cache';

// Check whether ACL data already exist
if (!$acl_cache || is_file($acl_file) !== true) {

    /*
        Roles are backed in database but role permissions not.
        Role permissions can be decorated and these decorators are backed.
    */

    $acl = new Acl();

    $acl
    ->addRole('guest', -1)
    ->addResourcePermissions('roles', ['read_all'])
    ->addResourcePermissions('products', ['read_all'])  //  ---- es importante el read_all_trashcah !!!
    ->addResourcePermissions('baz', ['read'])
    ->addResourcePermissions('bar', ['read', 'write'])
    ->addResourcePermissions('divisas', ['read', 'write'])
    ->addResourcePermissions('cotizaciones', ['read', 'write'])
    //->setGuest('guest')

    ->addRole('registered', 1)
    ->addInherit('guest')
    ->addResourcePermissions('user_roles', ['read'])
    ->addResourcePermissions('products', ['write'])
    ->addResourcePermissions('super_cool_table', ['read', 'write'])

    ->addRole('basic', 2) 
    ->addInherit('registered')
    ->addResourcePermissions('products', ['write'])    
    ->addResourcePermissions('files', ['read','list_all', 'write'])
    //->addSpecialPermissions(['read_all'])


    //->addRole('registeredO', 1)
    //->addRole('registered', 10)

    ->addRole('regular', 3)
    ->addInherit('registered')
    ->addResourcePermissions('products', ['read', 'write'])
    ->addResourcePermissions('foo', ['read', 'update'])
    //->addResourcePermissions('users', ['read', 'update'])
   
    ->addRole('supervisor', 502)  // salta sino especifico el id al leerlo
    ->addInherit('registered')
    ->addResourcePermissions('users', ['read_all'])  // <--

    ->addRole('admin', 100)
    ->addInherit('registered')
    ->addSpecialPermissions(['read_all', 'write_all', 'read_all_folders', 'lock', 'fill_all', 'impersonate'])
 

    ->addRole('superadmin', 500)
    ->addInherit('admin')
    ->addSpecialPermissions([
        'read_all_trashcan',
        'write_all_trashcan',
        'write_all_folders', 
        'write_all_collections',
        'transfer',
        'grant'
    ]);

                        

    if (!is_writable($acl_file)){
        throw new \Exception("$acl_file is not writable. Check permissions");   
    }

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


//var_export($acl->getRolePermissions());

return $acl;
