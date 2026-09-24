<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rider_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('radius_km');
            $table->enum('status', ['pending', 'accepted', 'expired', 'rejected'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['order_id', 'rider_id']);
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_offers');
    }
};