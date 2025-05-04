<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusColumnToDeliveryHeroesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_heroes', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->comment('0 inactive, 1 active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_heroes', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
