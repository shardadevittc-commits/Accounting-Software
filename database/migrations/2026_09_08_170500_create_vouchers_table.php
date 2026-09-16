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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique();
            $table->enum('voucher_type', ['sales', 'purchase', 'general', 'cash', 'bank']);
            $table->date('voucher_date');
            $table->unsignedBigInteger('party_id')->nullable()->index();
            $table->string('party_name')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->string('reference_no')->nullable();
            $table->text('description')->nullable();
            $table->text('narration')->nullable();
            $table->string('transaction_mode')->nullable(); // 'receipt', 'payment'
            $table->string('payment_method')->nullable(); // 'cash', 'cheque', 'neft', 'rtgs', 'imps', 'upi', 'bank_transfer', 'other'
            $table->string('instrument_no')->nullable(); // Cheque / UTR / Txn No
            $table->date('instrument_date')->nullable();
            $table->foreignId('bank_account_id')->nullable()->constrained('ledgers')->nullOnDelete();
            $table->enum('status', ['posted', 'draft', 'cancelled'])->default('posted');
            $table->decimal('total_debit', 15, 2)->default(0.00);
            $table->decimal('total_credit', 15, 2)->default(0.00);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['voucher_type', 'voucher_date']);
            $table->index(['voucher_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
