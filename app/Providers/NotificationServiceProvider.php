<?php

namespace App\Providers;

use App\Core\Channels\FirebaseChannel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\ChannelManager;


class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('firebase', function () {
            return new FirebaseChannel();
        });

        \Illuminate\Support\Facades\Notification::resolved(function (ChannelManager $service) {            
            $service->extend('firebase', function ($app) {
                return $app->make('firebase');
            });
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
