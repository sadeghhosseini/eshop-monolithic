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
            //address
            
            'view-address-any',
            //categories
            'add-category',
            'edit-category-any',
            'delete-category-any',

            //products
            'add-product',
            'edit-product-any',
            'delete-product-any',

            //properties
            'add-property',
            'edit-property-any',
            'delete-property-any',
            
            
            
            //images
            'add-image',
            'edit-image-any',
            'delete-image-any',
            
            //users
            'view-user-any',

            //orders
            'view-order-any',
            'accept_or_reject-comment-any',
            'edit-order(status)-any',

        ];
        $customerPermissions = [
            //addresses
            'add-address-own',
            'edit-address-own',
            'delete-address-own',
            'view-address-own',

            //users
            'view-user-own',
            'edit-user(name)-own',



            //comments
            'add-comment',
            'edit-comment-own',
            'delete-comment-own',
            'delete-comment-any',

            //user
            'place-order',
            // 'add-items-to-own-cart',
            'add-cart.item-own',
            // 'delete-items-from-own-cart',
            'delete-cart.item-own',
            'update-cart.item-own',
            
            //orders
            'view-order-own',
            'edit-order(address)-own',#if status does not equal "sent"
            
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
