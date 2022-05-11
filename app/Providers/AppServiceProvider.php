<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     */
    public function boot()
    {
        Carbon::setLocale(config('app.locale'));

        Blade::directive('convertTime', function($expression) {
            return '<?php
                $dt = new \DateTime('. $expression .');
                $dt->setTimezone(new \DateTimeZone("Europe/Amsterdam"));
                echo $dt->format("H:i")
            ?>';
        });

        Blade::directive('priceFormat', function($expression) {
            return '<?php
                echo number_format(' . $expression . ',2, ",", ".");
            ?>';
        });

        view()->composer(
            'components.layout',
            function ($view) {
                $view->with('person', Auth::user());
            }
        );
    }
}
