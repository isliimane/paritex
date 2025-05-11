<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddWarehouseTransferPermissionsToAdminRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        
        if ($adminRole) {
            $permissions = DB::table('permissions')->whereIn('name', [
                'warehouse_transfer_read',
                'warehouse_transfer_create',
                'warehouse_transfer_update',
                'warehouse_transfer_delete'
            ])->pluck('id');

            foreach ($permissions as $permissionId) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $adminRole->id
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        
        if ($adminRole) {
            $permissions = DB::table('permissions')->whereIn('name', [
                'warehouse_transfer_read',
                'warehouse_transfer_create',
                'warehouse_transfer_update',
                'warehouse_transfer_delete'
            ])->pluck('id');

            DB::table('role_has_permissions')
                ->where('role_id', $adminRole->id)
                ->whereIn('permission_id', $permissions)
                ->delete();
        }
    }
} 