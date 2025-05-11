<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdateTo162 extends Migration
{
    public function up()
    {
        $user = \App\Models\User::find(1);

        $permissions = $user->permissions;

        if (!in_array(['package_status_change'],$permissions) && !in_array(['subscription_setting_read'],$permissions))
        {
            $permissions[]      = "subscription_setting_read";
            $permissions[]      = "online_payment_read";
            $user->permissions  = $permissions;
            $user->save();
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('country_id')->after('email')->default(19);
        });
        Schema::table('carts', function (Blueprint $table) {
            $table->boolean('is_buy_now')->after('product_referral_code')->default(19);
        });
    }

    public function down()
    {
        Schema::table('162', function (Blueprint $table) {
            //
        });
    }
}
