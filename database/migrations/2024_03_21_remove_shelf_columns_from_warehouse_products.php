<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warehouse_products', function (Blueprint $table) {
            $table->dropColumn(['shelf_number', 'column_number']);
        });
    }

    public function down()
    {
        Schema::table('warehouse_products', function (Blueprint $table) {
            $table->integer('shelf_number')->nullable();
            $table->integer('column_number')->nullable();
        });
    }
}; 