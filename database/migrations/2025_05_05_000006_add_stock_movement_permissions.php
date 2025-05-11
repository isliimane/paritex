<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddStockMovementPermissions extends Migration
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
            'attribute' => 'stock_movment',
            'created_at' => now(),
            'updated_at' => now(),
            "keywords" => json_encode([
                'read' => "stock_movment_read",
                'create' => 'stock_movment_create',
                'update' => 'stock_movment_update',
                'destroy' => 'stock_movment_destroy'
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
        DB::table('permissions')->where('attribute', 'stock_movment')->delete();
    }
} 