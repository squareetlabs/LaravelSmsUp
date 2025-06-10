<?php

namespace SquareetLabs\LaravelSmsUp;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use SquareetLabs\LaravelSmsUp\Exceptions\CouldNotSendNotification;

/**
 * Class SmsUpServiceProvider
 * @package SquareetLabs\LaravelSmsUp
 */
class SmsUpServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/smsup.php' => config_path('smsup.php'),
        ], 'smsup-config');

        $this->registerRoutes();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/smsup.php', 'smsup');

        // Registrar el canal de notificaciones
        $this->registerNotificationChannel();

        // Registrar el manager principal
        $this->registerSmsUpManager();
    }

    /**
     * Register the notification channel.
     *
     * @return void
     */
    protected function registerNotificationChannel()
    {
        // Compatibilidad con diferentes versiones de Laravel
        if (method_exists(Notification::class, 'resolved')) {
            // Laravel 5.5+
            Notification::resolved(function (ChannelManager $service) {
                $service->extend('smsup', function ($app) {
                    return new SmsUpChannel($app['smsup']);
                });
            });
        } else {
            // Versiones anteriores de Laravel
            $this->app->when(ChannelManager::class)
                ->needs('$channels')
                ->give(function () {
                    return ['smsup' => SmsUpChannel::class];
                });
        }
    }

    /**
     * Register the SmsUp manager.
     *
     * @return void
     */
    protected function registerSmsUpManager()
    {
        $this->app->singleton('smsup', function (Application $app) {
            $config = $app['config']['smsup'];
            
            if (empty($config['api_key'])) {
                throw CouldNotSendNotification::missingApiKey();
            }

            return new SmsUpManager($config);
        });

        // Alias para backwards compatibility
        $this->app->alias('smsup', SmsUpManager::class);
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        if (!$this->app->routesAreCached()) {
            Route::group($this->routeConfiguration(), function () {
                $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
            });
        }
    }

    /**
     * Get the SmsUp route group configuration array.
     *
     * @return array
     */
    protected function routeConfiguration()
    {
        $config = $this->app['config']['smsup'] ?? [];
        
        return [
            'domain' => $config['route_domain'] ?? null,
            'namespace' => 'SquareetLabs\LaravelSmsUp\Http\Controllers',
            'prefix' => $config['route_prefix'] ?? 'smsup',
            'middleware' => $config['route_middleware'] ?? [],
        ];
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['smsup', SmsUpManager::class];
    }
}