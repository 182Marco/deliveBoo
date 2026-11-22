<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        // no "health" route: every unknown path must reach the SPA catch-all in web.php
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // logged-in users hitting /login or /register go to the admin home (ex RouteServiceProvider::HOME)
        $middleware->redirectUsersTo('/admin');
        // trust the proxy in front of the app (Codespaces, Render) so asset() / url() use the real host and https
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
