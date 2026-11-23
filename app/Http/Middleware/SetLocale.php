<?php

namespace App\Http\Middleware;

use App\I18n\Strings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

// language of the request: the foodsy_locale cookie (set by the header switch), otherwise English.
// The browser language is ignored on purpose: English is always the default.
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie(Strings::COOKIE);
        App::setLocale(Strings::supports($locale) ? $locale : Strings::DEFAULT_LOCALE);

        return $next($request);
    }
}
