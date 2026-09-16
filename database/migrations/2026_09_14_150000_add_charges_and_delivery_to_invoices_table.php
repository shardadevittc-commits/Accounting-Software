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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'delivery_type')) {
                $table->string('delivery_type', 10)->default('EX')->nullable()->after('transport_name');
            }
            if (!Schema::hasColumn('invoices', 'freight_amount')) {
                $table->decimal('freight_amount', 12, 2)->default(0)->nullable()->after('freight_charges');
            }
            if (!Schema::hasColumn('invoices', 'insurance_amount')) {
                $table->decimal('insurance_amount', 12, 2)->default(0)->nullable()->after('freight_amount');
            }
            if (!Schema::hasColumn('invoices', 'labour_charges_per_ton')) {
                $table->decimal('labour_charges_per_ton', 12, 2)->default(0)->nullable()->after('insurance_amount');
            }
            if (!Schema::hasColumn('invoices', 'labour_total_amount')) {
                $table->decimal('labour_total_amount', 12, 2)->default(0)->nullable()->after('labour_charges_per_ton');
            }
            if (!Schema::hasColumn('invoices', 'tds_amount')) {
                $table->decimal('tds_amount', 12, 2)->default(0)->nullable()->after('tcs_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_type',
                'freight_amount',
                'insurance_amount',
                'labour_charges_per_ton',
                'labour_total_amount',
                'tds_amount',
            ]);
        });
    }
};
