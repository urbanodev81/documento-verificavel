<?php

namespace UrbanoDev\DocumentoVerificavel;

use Illuminate\Support\ServiceProvider;

class DocumentoVerificavelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/documento-verificavel.php', 'documento-verificavel');
    }

    public function boot(): void
    {
        // Banco por tenant (stancl): desligue e publique em database/migrations/tenant.
        if (config('documento-verificavel.carregar_migrations')) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'documento-verificavel');

        $this->publishesMigrations([__DIR__.'/../database/migrations' => database_path('migrations')], 'documento-verificavel-migrations');
        $this->publishes([__DIR__.'/../config/documento-verificavel.php' => config_path('documento-verificavel.php')], 'documento-verificavel-config');
        $this->publishes([__DIR__.'/../resources/views' => resource_path('views/vendor/documento-verificavel')], 'documento-verificavel-views');
    }
}
