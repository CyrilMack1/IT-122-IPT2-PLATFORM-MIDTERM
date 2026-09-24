<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('restaurant_id')->constrained();
            $table->foreignId('rider_id')->nullable()->constrained('riders');

            $table->enum('status', [
                'received', 'confirmed', 'preparing', 'finding_rider',
                'rider_assigned', 'picked_up', 'out_for_delivery', 'delivered',
                'rejected', 'cancelled', 'no_rider'
            ])->default('received');

            $table->decimal('food_cost', 10, 2);
            $table->decimal('delivery_fee', 10, 2);
            $table->decimal('total_amount', 10, 2);

            $table->string('delivery_address');
            $table->decimal('delivery_lat', 10, 7);
            $table->decimal('delivery_lng', 10, 7);

            $table->boolean('is_external_order')->default(false);
            $table->unsignedTinyInteger('restaurant_rating')->nullable();
            $table->unsignedTinyInteger('rider_rating')->nullable();

            $table->timestamps();
            $table->index(['status', 'rider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};