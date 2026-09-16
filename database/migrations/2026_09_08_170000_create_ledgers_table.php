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
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('group_type', ['assets', 'liabilities', 'equity', 'revenue', 'expenses']);
            $table->string('category')->default('general'); // cash, bank, customer, supplier, sales, purchase, tax, expense, general
            $table->unsignedBigInteger('party_id')->nullable()->index(); // links to devine.customers cust_id
            $table->string('party_type')->nullable(); // 'customer', 'supplier'
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->enum('opening_balance_type', ['dr', 'cr'])->default('dr');
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->boolean('is_system')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['group_type', 'status']);
            $table->index(['category', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
