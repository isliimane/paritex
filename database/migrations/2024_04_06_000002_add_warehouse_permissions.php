<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddWarehousePermissions extends Migration
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
            'attribute' => 'warehouse',
            'created_at' => now(),
            'updated_at' => now(),
            "keywords" => json_encode([
                'read' => "warehouse_read",
                'create' => 'warehouse_create',
                'update' => 'warehouse_update',
                'destroy' => 'warehouse_destroy'
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
        DB::table('permissions')->where('attribute', 'warehouse')->delete();
    }
} 