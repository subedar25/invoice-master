<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add publication_id and status to match the PriceStructureType model and app form.
     * publications.id is INT UNSIGNED (increments), so publication_id must match.
     */
    public function up(): void
    {
        Schema::table('issue_pricing_structure_types', function (Blueprint $table) {
            if (!Schema::hasColumn('issue_pricing_structure_types', 'publication_id')) {
                $table->unsignedInteger('publication_id')->nullable()->after('name');
            }
            if (!Schema::hasColumn('issue_pricing_structure_types', 'status')) {
                $table->boolean('status')->default(true)->after('google_drive_folder_id');
            }
        });

        if (Schema::hasColumn('issue_pricing_structure_types', 'publication_id')) {
            // Ensure publication_id is INT UNSIGNED to match publications.id (increments = INT UNSIGNED)
            DB::statement('ALTER TABLE issue_pricing_structure_types MODIFY publication_id INT UNSIGNED NULL');

            try {
                Schema::table('issue_pricing_structure_types', function (Blueprint $table) {
                    $table->foreign('publication_id')
                        ->references('id')
                        ->on('publications')
                        ->onDelete('set null');
                });
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                if (str_contains($msg, 'Duplicate') === false && str_contains($msg, 'already exists') === false) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('issue_pricing_structure_types', function (Blueprint $table) {
                $table->dropForeign(['publication_id']);
            });
        } catch (\Throwable $e) {
            // Foreign key may not exist if this migration was partially run
        }

        Schema::table('issue_pricing_structure_types', function (Blueprint $table) {
            $columnsToDrop = array_filter(
                ['publication_id', 'status'],
                fn ($col) => Schema::hasColumn('issue_pricing_structure_types', $col)
            );
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
