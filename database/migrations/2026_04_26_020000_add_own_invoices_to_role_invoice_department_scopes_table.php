<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('role_invoice_department_scopes')) {
            return;
        }

        Schema::table('role_invoice_department_scopes', function (Blueprint $table) {
            if (! Schema::hasColumn('role_invoice_department_scopes', 'own_invoices')) {
                $table->boolean('own_invoices')->default(false)->after('all_departments');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('role_invoice_department_scopes')) {
            return;
        }

        Schema::table('role_invoice_department_scopes', function (Blueprint $table) {
            if (Schema::hasColumn('role_invoice_department_scopes', 'own_invoices')) {
                $table->dropColumn('own_invoices');
            }
        });
    }
};

