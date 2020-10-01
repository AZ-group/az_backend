<?php

use simplerest\core\Acl;
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
    ->addResourcePermissions('products', ['show', 'list'])
    ->addResourcePermissions('baz', ['read'])
    //->setGuest('guest')

    ->addRole('registered', 1)
    ->addInherit('guest')
    ->addResourcePermissions('roles', ['read'])

    ->addRole('basic', 2)
    ->addInherit('registered')
    ->addResourcePermissions('products', ['write'])
    ->addResourcePermissions('foo', ['read'])

    //->addRole('registeredO', 1)
    //->addRole('registered', 10)

    ->addRole('regular', 3)
    ->addInherit('registered')
    ->addResourcePermissions('products', ['read', 'write'])
    ->addResourcePermissions('foo', ['read', 'update'])
    ->addResourcePermissions('super_cool_table', ['read', 'write'])

    ->addRole('supervisor')
    ->addInherit('registered')
    ->addSpecialPermissions(['read_all', 'impersonate'])

    ->addRole('admin', 100)
    ->addInherit('registered')
    ->addSpecialPermissions(['read_all', 'write_all', 'read_all_folders', 'lock', 'fill_all', 'impersonate'])
 
    ->addRole('superadmin', 500)
    ->addInherit('admin')
    ->addSpecialPermissions([
                             'write_all_folders', 
                             'read_all_trashcan',
                             'write_all_trashcan',
                             'transfer',
                             'grant'
                            ]);

        
        

    /////////////////////

    //Debug::export($acl->hasResourcePermission('list', ['basic'], 'super_cool_table'));
    //Debug::export($acl->getSpPermissions());
    //Debug::export($acl->getRolePermissions(), 'perms');

    /////////////////////

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
