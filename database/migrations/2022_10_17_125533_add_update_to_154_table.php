<?php

use App\Models\Permission;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdateTo154Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $user = \App\Models\User::find(1);

        $permissions = $user->permissions;

        if (!in_array(['seller_as_login'],$permissions))
        {
            $permissions[] = "seller_as_login";
            $user->permissions = $permissions;
            $user->save();
        }

    }

    public function down()
    {
        Schema::table('154', function (Blueprint $table) {
            //
        });
    }
}
