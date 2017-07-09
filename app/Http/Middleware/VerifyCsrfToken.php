<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
         '*',
         'http://0.0.0.0',
        'http://localhost',
         '/0.0.0.0:3009',
        '/0.0.0.0',
        'http://0.0.0.0:3009',
         'http://localhost:3009',
         'api/*'
    ];
}
