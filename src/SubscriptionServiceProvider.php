<?php

namespace Jgabboud\Subscriptions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;

class SubscriptionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Jgabboud\Subscriptions\Http\Controllers\PlanController');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        // $this->loadMigrationsFrom(__DIR__.'/Database/migrations');
        
        // if ($this->app->runningInConsole()) {
        //     // Export the migration

        //     if (! class_exists('CreatePlansTable')) {
              
        //     }

        //   }

        $this->publishes([
          __DIR__ . '/../database/migrations/create_plans_table.php.stub' => $this->getMigrationFileName('create_plans_tables.php'),
        ], 'migrations');
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @return string
     */
    protected function getMigrationFileName($migrationFileName)
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $migrationFileName) {
                return $filesystem->glob($path.'*_'.$migrationFileName);
            })
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
