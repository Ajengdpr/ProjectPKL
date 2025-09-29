<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        // arahkan ke halaman login jika belum login
        return $request->expectsJson() ? null : route('login');
    }
}