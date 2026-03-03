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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();

            
            $table->string('name');                    
            $table->text('address');                    
            $table->string('city');                     
            $table->string('state');                     
            $table->string('country');                  
            $table->string('postal_code');              
            $table->string('phone')->nullable();
            $table->boolean('show_map')->default(false);
            $table->boolean('show_map_link')->default(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->softDeletes();     
            $table->timestamps();      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::dropIfExists('locations');
    }
};
