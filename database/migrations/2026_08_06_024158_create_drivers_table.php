<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('driver_code', 20)->unique();
            $table->string('license_number', 50)->nullable();
            $table->date('license_expiry')->nullable();
            $table->string('lorry_plate', 20)->nullable();
            $table->integer('lorry_capacity')->nullable();
            $table->string('status', 20)->default('available'); // available, on_delivery, off_duty
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};