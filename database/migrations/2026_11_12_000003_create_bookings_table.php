<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('vehicle_type');
            $table->string('vehicle_brand');
            $table->string('vehicle_model');
            $table->string('plate_number');
            $table->date('booking_date');
            $table->string('time_slot');
            $table->text('complaint')->nullable();
            $table->foreignId('mechanic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending_payment')->index();
            $table->unsignedBigInteger('estimated_total')->default(0);
            $table->unsignedBigInteger('dp_amount')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->text('admin_notes')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('service_started_at')->nullable();
            $table->timestamp('service_finished_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['booking_date', 'time_slot']);
        });

        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('price');
            $table->boolean('is_additional')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('bookings');
    }
};
