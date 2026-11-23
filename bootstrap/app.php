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
        // logged-in users hitting /login or /register go to the home of their role (User::homePath)
        $middleware->redirectUsersTo(fn (Illuminate\Http\Request $request) => $request->user()?->homePath() ?? '/');
        // trust the proxy in front of the app (Codespaces, Render) so asset() / url() use the real host and https
        $middleware->trustProxies(at: '*');
        // language of every page and API answer (cookie written by the header switch, plain text
        // because the Vue site writes it too)
        $middleware->encryptCookies(except: [App\I18n\Strings::COOKIE]);
        $middleware->web(append: [App\Http\Middleware\SetLocale::class, App\Http\Middleware\EnsureActive::class]);
        // role:<roles> opens an area only to its roles; active ends the session of a banned account
        // (already on every web request; added by hand to the API groups that need it)
        $middleware->alias([
            'role' => App\Http\Middleware\EnsureRole::class,
            'active' => App\Http\Middleware\EnsureActive::class,
        ]);
        $middleware->api(append: App\Http\Middleware\SetLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
