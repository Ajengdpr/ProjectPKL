<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * Daftar atribut yang tidak di-trim.
     */
    protected $except = [
        'password',
        'password_confirmation',
    ];
}