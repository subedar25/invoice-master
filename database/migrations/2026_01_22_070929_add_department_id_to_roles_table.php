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
        Schema::table('roles', function (Blueprint $table) {
            // Add a nullable foreign key column
            $table->foreignId('department_id')
                  ->nullable() // <-- Makes the column optional
                  ->constrained('departments') // <-- Assumes a 'departments' table exists
                  ->onDelete('set null'); // <-- If a department is deleted, set this to NULL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Drop the foreign key first, then the column
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};