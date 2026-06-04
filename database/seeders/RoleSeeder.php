<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
{
    $admin = Role::create(['name' => 'admin']);
    $staff = Role::create(['name' => 'staff']);

    $view = Permission::create(['name' => 'view sto']);
    $create = Permission::create(['name' => 'create sto']);

    $admin->givePermissionTo([$view, $create]);
    $staff->givePermissionTo($view);
}

}
