<?php

namespace App\Providers;

use App\Services\TT\TT;
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
        $this->app->singleton(TT::class, function ($app) {
            return new TT(); //config('riak')
        });
    }
}
