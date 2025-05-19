<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddWarehouseTransferPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permissions')->insert(
        [
            'attribute' => 'warehouse_transfer',
            'created_at' => now(),
            'updated_at' => now(),
            "keywords" => json_encode([
                'read' => "warehouse_transfer_read",
                'create' => 'warehouse_transfer_create',
                'update' => 'warehouse_transfer_update',
                'destroy' => 'warehouse_transfer_destroy'
            ])
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions')->where('attribute', 'warehouse_transfer')->delete();
    }
} 