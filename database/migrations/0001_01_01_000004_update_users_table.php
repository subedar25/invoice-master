<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {


        Schema::table('users', function (Blueprint $table) {
            // Rename 'name' to 'first_name'
            if (Schema::hasColumn('users', 'name')) {
                $table->renameColumn('name', 'first_name')->after('id');
            }

            // Rename 'added_timestamp' to 'created_at'
            if (Schema::hasColumn('users', 'added_timestamp')) {
                $table->renameColumn('added_timestamp', 'created_at');
            }

            // Change 'password' column to varchar(255) nullable
            if (Schema::hasColumn('users', 'password')) {
                $table->string('password', 255)->nullable()->change();
            }

            // Change 'id' column to unsigned integer
            // if (Schema::hasColumn('users', 'id')) {
            //     $table->unsignedInteger('id')->change();
            // }

            // Add 'last_name' column
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name', 255)->nullable()->after('first_name');
            }

            // Add other missing columns
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'is_wordpress_user')) {
                $table->boolean('is_wordpress_user')->default(0)->after('password');
            }

            if (!Schema::hasColumn('users', 'contributor_status')) {
                $table->enum('contributor_status', ['current','past','no'])->default('no')->after('is_wordpress_user');
            }

            if (!Schema::hasColumn('users', 'remember_token')) {
                $table->string('remember_token', 100)->nullable()->after('permission_roles');
            }

            if (!Schema::hasColumn('users', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }

            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
            }

            if (!Schema::hasColumn('users', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('deleted_at');
            }

            // Adjust other existing columns if needed
            if (Schema::hasColumn('users', 'status_id')) {
                $table->unsignedBigInteger('status_id')->nullable()->change();
            }

            if (Schema::hasColumn('users', 'status_notes')) {
                $table->text('status_notes')->nullable()->change();
            }

            if (Schema::hasColumn('users', 'phone')) {
                $table->text('phone')->nullable()->change();
            }

            if (Schema::hasColumn('users', 'permission_roles')) {
                $table->text('permission_roles')->nullable()->change();
            }

            if (Schema::hasColumn('users', 'privileges')) {
                $table->text('privileges')->nullable()->change();
            }

            if (Schema::hasColumn('users', 'salt')) {
                $table->string('salt')->nullable()->change();
            }

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rename first_name back to name
            if (Schema::hasColumn('users', 'first_name')) {
                $table->renameColumn('first_name', 'name');
            }

            // Rename created_at back to added_timestamp
            if (Schema::hasColumn('users', 'created_at')) {
                $table->renameColumn('created_at', 'added_timestamp');
            }

            // Change 'password' back to binary(60) if needed
            if (Schema::hasColumn('users', 'password')) {
                $table->binary('password', 60)->nullable(false)->change();
            }

            // Change 'id' column back to previous type (if needed)
            if (Schema::hasColumn('users', 'id')) {
                $table->bigInteger('id')->unsigned()->change();
            }

             if (Schema::hasColumn('users', 'privileges')) {
                $table->text('privileges')->nullable(false)->change();
            }

            if (Schema::hasColumn('users', 'salt')) {
                $table->string('salt')->nullable(false)->change();
            }
             Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_wordpress_user')->default(0)->nullable()->change();
            });

            // Drop newly added columns
            $table->dropColumn([
                'last_name', 'email_verified_at', 'is_wordpress_user',
                'contributor_status', 'remember_token',
                'updated_at', 'deleted_at', 'department_id'
            ]);
        });
    }
};
