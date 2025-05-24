<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('description')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->unsignedTinyInteger('installments'); // e.g. 12 for 12 months
            $table->decimal('monthly_amount', 15, 2);
            $table->unsignedTinyInteger('current_paid_months')->default(0);
            $table->date('start_month'); // the payroll month it starts
            $table->enum('status', ['active', 'paid'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
