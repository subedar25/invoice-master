<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoice_details')) {
            return;
        }

        Schema::table('invoice_details', function (Blueprint $table) {
            if (! Schema::hasColumn('invoice_details', 'product_name')) {
                $table->string('product_name')->nullable()->after('product_id');
            }

            if (! Schema::hasColumn('invoice_details', 'hsn')) {
                $table->string('hsn')->nullable()->after('product_name');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('invoice_details')) {
            return;
        }

        Schema::table('invoice_details', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_details', 'hsn')) {
                $table->dropColumn('hsn');
            }

            if (Schema::hasColumn('invoice_details', 'product_name')) {
                $table->dropColumn('product_name');
            }
        });
    }
};

