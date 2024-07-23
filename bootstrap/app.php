<?php

use App\Http\Middleware\NotAuthenticated;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->appendToGroup('guest', [
            RedirectIfAuthenticated::class,        
        ]);

        $middleware->appendToGroup('auth_check', [
            NotAuthenticated::class,        
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
