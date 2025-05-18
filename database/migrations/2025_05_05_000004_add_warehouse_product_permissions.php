<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddWarehouseProductPermissions extends Migration
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
            'attribute' => 'warehouse_product',
            'created_at' => now(),
            'updated_at' => now(),
            "keywords" => json_encode([
                'read' => "warehouse_product_read",
                'create' => 'warehouse_product_create',
                'update' => 'warehouse_product_update',
                'destroy' => 'warehouse_product_destroy'
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
        DB::table('permissions')->where('attribute', 'warehouse_product')->delete();
    }
} 