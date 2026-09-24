<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_configs', function (Blueprint $table) {
            $table->id();
            $table->decimal('town_center_lat', 10, 7);
            $table->decimal('town_center_lng', 10, 7);
            $table->decimal('service_radius_km', 5, 2)->default(10);
            $table->decimal('default_delivery_fee', 10, 2)->default(60);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_configs');
    }
};