<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('warehouse_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_stock_id')->constrained('product_stocks')->onDelete('cascade');
            $table->integer('quantity');
            $table->string('shelf_number')->nullable();
            $table->string('column_number')->nullable();
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_id', 'product_stock_id'], 'wh_prod_stock_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('warehouse_products');
    }
}; 