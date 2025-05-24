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
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('payroll_month'); // e.g. 2025-05-01
            $table->decimal('base_salary', 15, 2);
            $table->decimal('allowance', 15, 2)->nullable();
            $table->decimal('loan_deduction', 15, 2)->nullable();
            $table->decimal('take_home', 15, 2);
            $table->enum('status', ['paid', 'unpaid'])->default('paid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
