<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_code', 30)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->date('delivery_date')->nullable();
            $table->time('delivery_time')->nullable();
            $table->integer('quantity_delivered')->nullable();
            $table->integer('empty_cylinders_collected')->default(0);
            $table->string('status', 20)->default('assigned'); // assigned, in_transit, arrived, completed, cancelled
            $table->text('special_instructions')->nullable();
            $table->text('driver_notes')->nullable();
            $table->json('delivery_photos')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->float('arrival_latitude')->nullable();
            $table->float('arrival_longitude')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};