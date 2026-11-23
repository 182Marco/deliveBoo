<?php

namespace Tests\Feature;

use App\I18n\Strings;
use Illuminate\Support\Arr;
use Tests\TestCase;

/**
 * Every static key used by the code exists in resources/js/utils/strings.json: t("…") in the Vue site
 * (branch "site") and __('group.…') in Blade/PHP for the groups of strings.json. A missing key would
 * show up as raw text (e.g. "header.login") instead of a translation.
 * Keys built at runtime (ending with a dot, e.g. 'partner.plates.courses.'.$course) are skipped.
 */
class StringKeysTest extends TestCase
{
    private function strings(): array
    {
        return json_decode(file_get_contents(resource_path('js/utils/strings.json')), true);
    }

    private function files(string $dir, string $pattern): array
    {
        $out = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if (preg_match($pattern, $file->getFilename())) {
                $out[] = $file->getPathname();
            }
        }

        return $out;
    }

    private function isLeaf(mixed $node): bool
    {
        return is_array($node) && is_string($node[Strings::DEFAULT_LOCALE] ?? null);
    }

    public function testEveryKeyOfTheVueSiteExists()
    {
        $site = $this->strings()['site'];
        $missing = [];
        foreach ($this->files(resource_path('js'), '/\.(vue|js)$/') as $file) {
            $code = file_get_contents($file);
            preg_match_all('/\bt\(\s*["\']([A-Za-z0-9_.]+)["\']/', $code, $calls);
            // link lists keep the key in a "text" property and translate it in the template
            preg_match_all('/\btext:\s*["\']([A-Za-z]+\.[A-Za-z0-9_.]+)["\']/', $code, $texts);
            foreach ([...$calls[1], ...$texts[1]] as $key) {
                if (! str_ends_with($key, '.') && ! $this->isLeaf(Arr::get($site, $key))) {
                    $missing[] = basename($file).": {$key}";
                }
            }
        }

        $this->assertSame([], array_values(array_unique($missing)));
    }

    public function testEveryKeyOfBladeAndPhpExists()
    {
        $strings = $this->strings();
        $groups = array_diff(array_keys($strings), ['locales']);
        $missing = [];
        foreach ([...$this->files(resource_path('views'), '/\.php$/'), ...$this->files(app_path(), '/\.php$/')] as $file) {
            preg_match_all('/\b(?:__|trans_choice)\(\s*["\']([A-Za-z0-9_.]+)["\']/', file_get_contents($file), $calls);
            foreach ($calls[1] as $key) {
                if (str_ends_with($key, '.') || ! in_array(strtok($key, '.'), $groups, true)) {
                    continue;
                }
                // a whole branch is allowed too (e.g. __('partner.avatar.editor') hands an array to JS)
                if (! Arr::has($strings, $key)) {
                    $missing[] = basename($file).": {$key}";
                }
            }
        }

        $this->assertSame([], array_values(array_unique($missing)));
    }
}
