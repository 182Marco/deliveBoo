<?php

namespace Tests\Feature;

use App\I18n\Strings;
use Tests\TestCase;

/**
 * One object for every static string (resources/js/utils/strings.json), five languages,
 * English by default; the foodsy_locale cookie switches pages, API answers and mails.
 */
class LocaleTest extends TestCase
{
    /** @return array<string, array> dotted key => leaf, for every leaf of the object */
    private function leaves(array $node, string $prefix = ''): array
    {
        $leaves = [];
        foreach ($node as $key => $child) {
            if (Strings::isLeaf($child)) {
                $leaves[$prefix . $key] = $child;
            } elseif (is_array($child)) {
                $leaves += $this->leaves($child, "$prefix$key.");
            }
        }

        return $leaves;
    }

    private function placeholders(string $text): array
    {
        preg_match_all('/:[A-Za-z]+/', $text, $matches);
        $found = array_unique($matches[0]);
        sort($found);

        return $found;
    }

    public function testEveryStringExistsInEveryLanguageWithTheSamePlaceholders()
    {
        $codes = array_keys(Strings::locales());
        $this->assertSame(['en', 'it', 'de', 'fr', 'es'], $codes);

        $leaves = $this->leaves(array_diff_key(Strings::all(), ['locales' => true]));
        $this->assertGreaterThan(400, count($leaves));

        foreach ($leaves as $key => $leaf) {
            $this->assertSame([], array_diff(array_keys($leaf), $codes), "$key: unknown language");
            foreach ($codes as $code) {
                $this->assertNotSame('', trim($leaf[$code] ?? ''), "$key: missing $code");
                // ":names" in English is ":name" + "s" (see site.cuisine.titleStarred)
                $this->assertSame(
                    $this->placeholders(str_replace(':names', ':name', $leaf['en'])),
                    $this->placeholders(str_replace(':names', ':name', $leaf[$code])),
                    "$key: placeholders of $code differ from English"
                );
                if (str_contains($leaf['en'], '|')) {
                    $this->assertStringContainsString('|', $leaf[$code], "$key: plural forms missing in $code");
                }
            }
        }
    }

    public function testEnglishIsTheDefaultLanguage()
    {
        $this->get('/login')->assertOk()->assertSee('<html lang="en">', false)->assertSee('Welcome back');
        // an unknown cookie value is ignored
        $this->withUnencryptedCookie(Strings::COOKIE, 'xx')->get('/login')->assertSee('Welcome back');
    }

    public function testTheCookieTranslatesThePartnerArea()
    {
        $this->withUnencryptedCookie(Strings::COOKIE, 'it')->get('/login')
            ->assertOk()
            ->assertSee('<html lang="it">', false)
            ->assertSee('Bentornato')
            ->assertSee('ui-lang-option--current', false)
            ->assertSee('Deutsch');
    }

    public function testTheCookieTranslatesTheSpaShell()
    {
        $this->withUnencryptedCookie(Strings::COOKIE, 'fr')->get('/')
            ->assertOk()
            ->assertSee('<html lang="fr">', false)
            ->assertSee('Foodsy · La bonne cuisine, livrée avec soin');
    }

    public function testTheSwitchRouteRemembersTheLanguageAndGoesBack()
    {
        $this->from('/login')->get(route('locale', 'de'))
            ->assertRedirect('/login')
            ->assertPlainCookie(Strings::COOKIE, 'de');

        $this->get(route('locale', 'xx'))->assertNotFound();
    }

    public function testApiValidationAnswersInTheChosenLanguage()
    {
        $this->postJson('/api/orders', [])
            ->assertJsonPath('errors.customer_name.0', 'The first name field is required.');

        // withCredentials: JSON test requests carry cookies only on demand (a browser always sends them)
        $this->withCredentials()->withUnencryptedCookie(Strings::COOKIE, 'de')->postJson('/api/orders', [])
            ->assertOk()
            ->assertJsonPath('errors.customer_name.0', 'Das Feld Vorname ist erforderlich.');
    }
}
