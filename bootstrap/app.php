<?php

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
        // Honor HTTPS forwarded by local reverse proxies such as the ngrok agent.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
        $middleware->web(append: [\App\Http\Middleware\SetPublicLocale::class,\App\Http\Middleware\SecurityHeaders::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
