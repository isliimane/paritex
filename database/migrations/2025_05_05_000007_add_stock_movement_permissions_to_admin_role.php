<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddStockMovmentPermissionsToAdminRole extends Migration
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
                'stock_movment_read',
                'stock_movment_create',
                'stock_movment_update',
                'stock_movment_delete'
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
                'stock_movment_read',
                'stock_movment_create',
                'stock_movment_update',
                'stock_movment_delete'
            ])->pluck('id');

            DB::table('role_has_permissions')
                ->where('role_id', $adminRole->id)
                ->whereIn('permission_id', $permissions)
                ->delete();
        }
    }
} 