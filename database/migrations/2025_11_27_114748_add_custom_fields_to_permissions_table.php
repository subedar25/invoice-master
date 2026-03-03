<?php

// In the new file: ..._add_custom_fields_to_permissions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
          
            $table->string('slug')->unique()->after('display_name');
            $table->foreignId('module_id')->nullable()->constrained()->onDelete('cascade')->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'slug', 'module_id']);
        });
    }
};