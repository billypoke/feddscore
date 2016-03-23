<?php

namespace FeddScore\Providers;

use DateTime;
use FeddScore\DesignDay;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(DesignDay::class, function ($app) {
            return new DesignDay(new DateTime());
        });
    }
}
