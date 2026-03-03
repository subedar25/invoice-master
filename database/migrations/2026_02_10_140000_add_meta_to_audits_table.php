<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $tableName = config('audit.drivers.database.table', 'audits');

        Schema::connection($connection)->table($tableName, function (Blueprint $table) use ($connection, $tableName) {
            if (!Schema::connection($connection)->hasColumn($tableName, 'meta')) {
                $table->json('meta')->nullable()->after('tags');
            }
        });
    }

    public function down(): void
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $tableName = config('audit.drivers.database.table', 'audits');

        Schema::connection($connection)->table($tableName, function (Blueprint $table) use ($connection, $tableName) {
            if (Schema::connection($connection)->hasColumn($tableName, 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};
