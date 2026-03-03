<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('issue_pricing_structure_attributes');
        Schema::dropIfExists('issue_orientations');
        Schema::dropIfExists('issue_sections');
        Schema::dropIfExists('issue_pricing_structure_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a destructive removal, so down is not fully restoring the schemas.
    }
};
