<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Permission::firstOrCreate(['name' => 'manage ai settings', 'guard_name' => 'web']);

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo('manage ai settings');
        }
    }

    public function down(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->revokePermissionTo('manage ai settings');
        }

        Permission::where('name', 'manage ai settings')->delete();
    }
};
