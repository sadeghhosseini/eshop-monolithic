<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //reset chached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        //create roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $customer = Role::firstOrCreate(['name' => 'customer']);

        //create permissions
        $adminPermissions = [
            //categories
            'add-categories',
            'edit-any-categories',
            'delete-any-categories',

            //products
            'add-products',
            'edit-any-products',
            'delete-any-products',

            //properties
            'add-properties',
            'edit-any-properties',
            'delete-any-properties',

            
            //users
            'view-any-users',

            //orders
            'view-any-orders',
            'accept-reject-any-comments',
            'edit-any-orders-status',

            //images
            'upload-images',
            'edit-any-images',
            'view-image-list',
            'delete-any-images',

        ];
        $customerPermissions = [
            //addresses
            'add-own-addresses',
            'edit-own-addresses',
            'delete-own-addresses',
            'view-own-addresses',

            //users
            'view-own-user',

            //orders
            'view-own-orders',
            'edit-own-orders-address',#if status does not equal "sent"
            //comments
            'add-comments',
            'edit-own-comments',
            'delete-own-comments',
            'view-any-comments',

            //user
            'place-orders',
            'add-items-to-own-cart',
            'delete-items-from-own-cart',

            
        ];
        $permissions = [
            ...$adminPermissions,
            ...$customerPermissions,
        ];


        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $admin->givePermissionTo(...$adminPermissions);

        $customer->givePermissionTo(...$customerPermissions);
    }
}
