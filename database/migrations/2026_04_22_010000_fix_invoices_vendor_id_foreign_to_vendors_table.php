<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * invoices.vendor_id was incorrectly constrained to users; the app uses Vendor models (vendors table).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoices') || ! Schema::hasTable('vendors')) {
            return;
        }

        $vendorIds = DB::table('vendors')->pluck('id')->all();
        $hasInvoices = DB::table('invoices')->exists();

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
        });

        if ($vendorIds !== []) {
            $firstVendorId = (int) $vendorIds[0];
            DB::table('invoices')
                ->whereNotIn('vendor_id', $vendorIds)
                ->update(['vendor_id' => $firstVendorId]);
        } elseif ($hasInvoices) {
            throw new \RuntimeException(
                'Cannot fix invoices.vendor_id because invoices exist but vendors table is empty. Add at least one vendor, then re-run migration.'
            );
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('vendor_id')
                ->references('id')
                ->on('vendors')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('invoices') || ! Schema::hasTable('users')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('vendor_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
