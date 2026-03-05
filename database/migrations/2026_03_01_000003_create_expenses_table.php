<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->string('category'); // 'rent', 'utilities', 'salaries', 'supplies', 'maintenance', 'marketing', 'other'
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->string('payment_method'); // 'cash', 'bank_transfer', 'check', 'card'
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected', 'paid'
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable(); // chemin vers scan du reçu
            $table->timestamps();
            
            $table->index(['expense_date', 'category']);
            $table->index(['status', 'created_by_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
