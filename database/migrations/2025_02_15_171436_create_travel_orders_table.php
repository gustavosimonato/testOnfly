<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('travel_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('destination');
            $table->date('departure_date');
            $table->date('return_date');
            $table->enum('status', ['requested', 'approved', 'cancelled'])->default('requested');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['departure_date', 'return_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_orders');
    }
};
