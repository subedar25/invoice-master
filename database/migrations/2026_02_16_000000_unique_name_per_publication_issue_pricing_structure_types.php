<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX_NAME = 'issue_pricing_structure_types_publication_id_name_unique';

    public function up(): void
    {
        Schema::table('issue_pricing_structure_types', function (Blueprint $table) {
            if (!Schema::hasColumn('issue_pricing_structure_types', 'publication_id')) {
                $table->unsignedBigInteger('publication_id')->nullable()->after('id');
            }
        });

        // Use raw SQL so we can add key length for TEXT column (MySQL requires it)
        DB::statement(
            'CREATE UNIQUE INDEX ' . self::INDEX_NAME . ' ON issue_pricing_structure_types (publication_id, name(255))'
        );
    }

    public function down(): void
    {
        Schema::table('issue_pricing_structure_types', function (Blueprint $table) {
            $table->dropIndex(self::INDEX_NAME);
        });

        Schema::table('issue_pricing_structure_types', function (Blueprint $table) {
            if (Schema::hasColumn('issue_pricing_structure_types', 'publication_id')) {
                $table->dropColumn('publication_id');
            }
        });
    }
};
