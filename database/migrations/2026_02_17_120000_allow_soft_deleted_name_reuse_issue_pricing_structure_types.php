<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_INDEX = 'issue_pricing_structure_types_publication_id_name_unique';
    /** Keep under MySQL 64-char identifier limit. */
    private const NEW_INDEX = 'ipt_pub_id_name_deleted_at_uniq';
    /** Standalone index so FK (publication_id -> publications.id) remains valid after we drop the unique. */
    private const PUBLICATION_ID_INDEX = 'ipt_publication_id_idx';

    /**
     * Allow reusing a name per publication when the existing record is soft deleted.
     * Replace unique(publication_id, name) with unique(publication_id, name, deleted_at)
     * so only one row per (publication_id, name) can have deleted_at IS NULL.
     *
     * The old unique index is used by the FK (publication_id -> publications.id).
     * Create a dedicated index on publication_id first so the FK remains valid, then drop the old unique.
     */
    public function up(): void
    {
        if (! Schema::hasTable('issue_pricing_structure_types')) {
            return;
        }

        // Ensure publication_id has its own index so the FK to publications.id is still supported after we drop the unique
        $this->createPublicationIdIndexIfMissing();

        $this->dropIndexIfExists(self::OLD_INDEX);

        DB::statement(
            'CREATE UNIQUE INDEX ' . self::NEW_INDEX . ' ON issue_pricing_structure_types (publication_id, name(255), deleted_at)'
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('issue_pricing_structure_types')) {
            return;
        }

        $this->dropIndexIfExists(self::NEW_INDEX);

        DB::statement(
            'CREATE UNIQUE INDEX ' . self::OLD_INDEX . ' ON issue_pricing_structure_types (publication_id, name(255))'
        );

        // Remove the standalone publication_id index we added (FK will use the restored unique index again)
        $this->dropIndexIfExists(self::PUBLICATION_ID_INDEX);
    }

    private function createPublicationIdIndexIfMissing(): void
    {
        $indexExists = DB::selectOne(
            "SELECT 1 FROM information_schema.statistics 
             WHERE table_schema = DATABASE() 
             AND table_name = 'issue_pricing_structure_types' 
             AND index_name = ?",
            [self::PUBLICATION_ID_INDEX]
        );

        if (! $indexExists) {
            DB::statement('CREATE INDEX ' . self::PUBLICATION_ID_INDEX . ' ON issue_pricing_structure_types (publication_id)');
        }
    }

    private function dropIndexIfExists(string $indexName): void
    {
        $indexExists = DB::selectOne(
            "SELECT 1 FROM information_schema.statistics 
             WHERE table_schema = DATABASE() 
             AND table_name = 'issue_pricing_structure_types' 
             AND index_name = ?",
            [$indexName]
        );

        if ($indexExists) {
            DB::statement('ALTER TABLE issue_pricing_structure_types DROP INDEX ' . $indexName);
        }
    }
};
