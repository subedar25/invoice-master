<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Permission; 

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
         $this->app->bind(
            \Spatie\Permission\Contracts\Permission::class,
            Permission::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
