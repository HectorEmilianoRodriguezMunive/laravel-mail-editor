<?php

namespace Qoraiche\MailEclipse;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Qoraiche\MailEclipse\Command\VendorPublishCommand;

class MailEclipseServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        // Configuración de middleware directamente en las rutas
        $this->registerRoutes();

        // Carga de vistas y traducciones
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'maileclipse');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'maileclipse');

        // Console-specific
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register the package routes with middleware protection.
     */
    private function registerRoutes()
    {
        Route::group([
            'prefix' => config('maileclipse.path', 'maileclipse'),
            'middleware' => ['web', 'auth'],
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
        });
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/maileclipse.php', 'maileclipse');

        $this->app->singleton('maileclipse', function ($app) {
            return new MailEclipse;
        });
    }

    /**
     * Console-specific booting.
     */
    protected function bootForConsole()
    {
        $this->publishes([
            __DIR__.'/../config/maileclipse.php' => config_path('maileclipse.php'),
        ], 'maileclipse.config');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/maileclipse'),
        ], 'public');

        $this->publishes([
            __DIR__.'/../resources/views/templates' => $this->app->resourcePath('views/vendor/maileclipse/templates'),
        ], 'maileclipse.templates');

        $this->commands([
            VendorPublishCommand::class,
        ]);
    }
}
