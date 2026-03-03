<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Alters publications: remove user_id and parent_id, add publication_type_id.
     */
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'user_id']);
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->string('code')->nullable()->after('description');;
            $table->boolean('status')->default(true)->after('description');
            $table->unsignedBigInteger('publication_type_id')->nullable()->after('id');
            $table->foreign('publication_type_id')
                ->references('id')
                ->on('publication_types')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropForeign(['publication_type_id']);
            $table->dropColumn('publication_type_id');
            $table->dropColumn('code');
            $table->dropColumn('status');
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->after('id');
            $table->unsignedInteger('parent_id')->nullable()->after('name');
            $table->index('parent_id');
            $table->foreign('parent_id')
                ->references('id')
                ->on('publications')
                ->nullOnDelete();
        });
    }
};
