<?php

namespace App\Http\Controllers;

use App\I18n\Strings;
use Illuminate\Http\RedirectResponse;

// language switch of the partner hub (a plain link, no script): remembers the choice and goes back.
// The Vue site writes the same cookie itself (resources/js/utils/i18n.js).
class LocaleController extends Controller
{
    private const ONE_YEAR = 60 * 24 * 365;

    public function __invoke(string $locale): RedirectResponse
    {
        abort_unless(Strings::supports($locale), 404);

        // not httpOnly: the Vue site must be able to overwrite it
        return redirect()->back(fallback: url('/'))
            ->withCookie(cookie(Strings::COOKIE, $locale, self::ONE_YEAR, httpOnly: false, sameSite: 'lax'));
    }
}
