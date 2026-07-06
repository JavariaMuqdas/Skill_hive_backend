<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        // API / JSON requests => no redirect, return null so it responds 401 JSON
        if ($request->expectsJson()) {
            return null;
        }

        // If you don't have web login route, don't call route('login')
        return null;
    }
}
