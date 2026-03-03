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
        Schema::create('user_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->timestamps();
        });



        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'status_id')) {
                $table->unsignedBigInteger('status_id')->nullable()->after('driver');
                $table->foreign('status_id')
                    ->references('id')
                    ->on('user_statuses')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['status_id']);
                $table->dropColumn('status_id');
            });


        Schema::dropIfExists('user_statuses');
    }
};
