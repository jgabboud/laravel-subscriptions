<?php

namespace Jgabboud\Subscriptions;

use Illuminate\Support\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Jgabboud\Subscriptions\Http\Controllers\SubscriptionController');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');
        
        if ($this->app->runningInConsole()) {
            // Export the migration
            if (! class_exists('CreateSubscriptionsTable')) {
              $this->publishes([
                __DIR__ . '/../database/migrations/create_subscriptions_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_subscriptions_table.php'),
                // you can add any number of migrations here
              ], 'migrations');
            }
          }
    }
}
