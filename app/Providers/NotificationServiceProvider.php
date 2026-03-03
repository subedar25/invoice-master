<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Notification\Contracts\NotificationRepository;
use App\Infrastructure\Persistence\Notification\EloquentNotificationRepository;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
         $this->app->bind(
            NotificationRepository::class,
            EloquentNotificationRepository::class
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
