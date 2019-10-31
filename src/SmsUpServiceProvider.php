<?php

namespace SquareetLabs\LaravelSmsUp;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Class SmsUpServiceProvider
 * @package SquareetLabs\LaravelSmsUp
 */
class SmsUpServiceProvider extends ServiceProvider
{
    /**
     * Register.
     */
    public function register()
    {
        Notification::resolved(function (ChannelManager $service) {
            $service->extend('smsUp', function () {
                return new SmsUpChannel();
            });
        });
        $this->app->bind('smsUp', function() {
            return new SmsUpManager(config('services.smsUp'));
        });

        $this->registerRoutes();
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
        });
    }

    /**
     * Get the SmsUp route group configuration array.
     *
     * @return array
     */
    private function routeConfiguration()
    {
        return [
            'domain' => null,
            'namespace' => 'App\Services\SmsUp\Http\Controllers',
            'prefix' => 'smsup'
        ];
    }
}