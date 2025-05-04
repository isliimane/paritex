<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWarehouseIdToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('pickup_hub_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
    }
} 