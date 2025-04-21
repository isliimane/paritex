<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Client
            $table->foreignId('order_id')->constrained(); // Commande concernée
            $table->text('reason'); 
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            // Pour vérifier si < 30 jours
            $table->boolean('is_product_unused')->default(false);
            $table->date('purchase_date'); 
            $order = Order::find($request->order_id);
if ($order->created_at->diffInDays(now()) > 30) {
    return response()->json(['message' => 'Délai de retour dépassé'], 400);
}

$table->enum('resolution_type', ['refund', 'exchange'])->nullable();
$table->text('admin_notes')->nullable();
$table->timestamp('processed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('return_requests');
    }
}
