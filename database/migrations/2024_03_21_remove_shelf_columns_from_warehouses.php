<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['number_of_shelves', 'columns_per_shelf']);
        });
    }

    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->integer('number_of_shelves')->nullable();
            $table->integer('columns_per_shelf')->nullable();
        });
    }
}; 