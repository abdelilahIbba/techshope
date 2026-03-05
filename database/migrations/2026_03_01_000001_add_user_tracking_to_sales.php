<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('cashier_user_id')->nullable()->after('customer_id')
                  ->constrained('users')->nullOnDelete();
            $table->string('status')->default('completed')->after('payment_method');
            $table->text('notes')->nullable()->after('status');
            
            $table->index(['cashier_user_id', 'date']);
            $table->index(['status', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['cashier_user_id']);
            $table->dropIndex(['cashier_user_id', 'date']);
            $table->dropIndex(['status', 'date']);
            $table->dropColumn(['cashier_user_id', 'status', 'notes']);
        });
    }
};
