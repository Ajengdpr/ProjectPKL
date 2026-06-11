<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Database::class, function ($app) {
            $factory = (new Factory)
                ->withServiceAccount(storage_path('app/firebase_credentials.json'))
                ->withDatabaseUri('https://project-pkl-b57bf-default-rtdb.firebaseio.com/');

            return $factory->createDatabase();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        \Carbon\Carbon::setLocale('id');
    }
}