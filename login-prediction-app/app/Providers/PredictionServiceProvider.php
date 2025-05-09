<?php

namespace App\Providers;

use App\Services\PredictionService;
use Illuminate\Support\ServiceProvider;

class PredictionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PredictionService::class, function ($app, $parameters) {
            $user = isset($parameters['user']) ? $parameters['user'] : null;
            return new PredictionService($user);
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