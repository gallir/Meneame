# Simple Disposable Email Validator

[![Downloads](https://img.shields.io/packagist/dt/eusonlito/disposable-email-validator.svg)](https://packagist.org/packages/eusonlito/disposable-email-validator)
[![Packagist](http://img.shields.io/packagist/v/eusonlito/disposable-email-validator.svg)](https://packagist.org/packages/eusonlito/disposable-email-validator)
[![License MIT](http://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/eusonlito/disposable-email-validator/blob/master/LICENSE)

Validate emails against multiple databases with disposable email domains.

Current databases (domains, wildcard and whitelist):

* https://github.com/ivolo/disposable-email-domains
* https://github.com/MattKetmo/EmailChecker
* https://github.com/fgribreau/mailchecker
* https://github.com/martenson/disposable-email-domains

## Installation

Via [Composer](http://getcomposer.org/):

```
composer require eusonlito/disposable-email-validator
```

## Usage

Basic use of EmailChecker with built-in throwaway email list:

```php
<?php

require __DIR__.'/vendor/autoload.php';

use Eusonlito\DisposableEmail\Check;

// Simple
// Validate emailFilter, domain and wildcard

Check::email('me@my-email.com'); // true
Check::email('me@10minutemail.com'); // false

// Other methods

Check::emailFilter('me@my-email.com');     // Validate email with filter_var
Check::emailExpression('me@my-email.com'); // Validate email with regular expression
Check::domain('my-email.com');             // Validate domain and wildcard domains
Check::wildcard('my-email.com');           // Validate only wildcard domains

```

## Integration with Laravel 5

To integrate this library with your Laravel 5.x project add the following
line to the `providers` key within your `config/app.php` file:

```php
'providers' => [
    ...

    Eusonlito\DisposableEmail\Laravel\DisposableEmailServiceProvider::class

    ...
];
```

You can then use the library within your project like so:

```php
<?php
use InvalidArgumentException;
use Eusonlito\DisposableEmail\Check;

class Signup
{
    public function validate(Request $request)
    {
        if (!Check::email($request->input('email'))) {
            throw new InvalidArgumentException('Invalid email');
        }
    }

    public function getValidator(array $data)
    {
        return Validator::make($data, [
             'email' => 'required|email|disposable_email' // Use after email validator
        ]);
    }
}
```
