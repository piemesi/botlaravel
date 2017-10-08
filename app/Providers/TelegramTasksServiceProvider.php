<?php

namespace App\Providers;

use App\Services\TT\ITT;
use App\Services\TT\TT;
use App\Services\TT\TTController;
use App\Services\TT\TTRepository;
use Illuminate\Support\ServiceProvider;

class TelegramTasksServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ITT::class, function(){
            return new TTController(new TTRepository());
        });
    }
}
