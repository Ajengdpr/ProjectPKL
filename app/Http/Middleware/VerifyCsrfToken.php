<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Daftar route yang tidak diverifikasi CSRF.
     */
    protected $except = [
        //
    ];
}