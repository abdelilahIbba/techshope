<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('authorized_by_user_id')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->string('reason'); // 'client_request', 'error', 'fraud', 'return', 'other'
            $table->text('notes')->nullable();
            $table->decimal('refund_amount', 10, 2);
            $table->string('refund_method')->nullable(); // cash, card, transfer
            $table->timestamp('cancelled_at');
            $table->timestamps();
            
            $table->index(['cancelled_at', 'reason']);
            $table->index('cancelled_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_cancellations');
    }
};
