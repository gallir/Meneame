<?php
namespace Eusonlito\DisposableEmail\Laravel;

use Eusonlito\DisposableEmail\Check;
use Illuminate\Support\ServiceProvider;
use Validator;

class DisposableEmailServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        /*
         * Added a custom validator filter.
         */
        $check = function ($attr, $value) {
            return Check::domain(explode('@', $value)[1]);
        };

        Validator::extend('disposable_email', $check, 'The :attribute domain is not allowed.');
    }
}
