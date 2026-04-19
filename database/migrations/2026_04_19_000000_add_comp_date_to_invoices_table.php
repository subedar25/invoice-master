<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('invoices', 'comp_date')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->date('comp_date')->nullable()->after('pay_term');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'comp_date')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('comp_date');
            });
        }
    }
};
