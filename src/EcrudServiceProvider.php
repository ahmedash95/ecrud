<?php

namespace Ahmedash95\Ecrud;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class EcrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/ecrud.php' => config_path('ecrud.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ecrud.php', 'ecrud');

        $this->app->bind(Manager::class, function () {
            return new Manager(
                new Filesystem(),
                $this->app['config']['ecrud'],
                $this->app['config']['view.paths']
            );
        });

        $this->commands([
            \Ahmedash95\Ecrud\Commands\CreateFromMigrationCommand::class,
        ]);
    }
}
