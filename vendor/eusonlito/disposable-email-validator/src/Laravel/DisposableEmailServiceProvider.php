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
    public function boot(EmailChecker $checker)
    {
        /*
         * Added a custom validator filter.
         */
        $check = function ($attr, $value, $param, $validator) use ($checker) {
            return Check::domain(explode('@', $value)[1]);
        };

        Validator::extend('disposable_email', $check, 'The :attribute domain is not allowed.');
    }
}
