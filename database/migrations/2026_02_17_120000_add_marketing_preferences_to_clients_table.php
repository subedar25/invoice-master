<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'marketing_preferences')) {
                $table->string('marketing_preferences', 50)->nullable();
            }
            if (! Schema::hasColumn('clients', 'newsletter_weekly_business_updates')) {
                $table->boolean('newsletter_weekly_business_updates')->nullable();
            }
            if (! Schema::hasColumn('clients', 'newsletter_pulse_picks')) {
                $table->boolean('newsletter_pulse_picks')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'marketing_preferences')) {
                $table->dropColumn('marketing_preferences');
            }
            if (Schema::hasColumn('clients', 'newsletter_weekly_business_updates')) {
                $table->dropColumn('newsletter_weekly_business_updates');
            }
            if (Schema::hasColumn('clients', 'newsletter_pulse_picks')) {
                $table->dropColumn('newsletter_pulse_picks');
            }
        });
    }
};
