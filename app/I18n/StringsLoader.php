<?php

namespace App\I18n;

use Illuminate\Contracts\Translation\Loader;

/**
 * Translation loader of the app (bound in AppServiceProvider): the groups of strings.json
 * (site, partner, mail, api, validation, auth, passwords, pagination) win over the PHP files.
 * Everything else, e.g. a validation rule the app never uses, still comes from Laravel's
 * own English files.
 */
class StringsLoader implements Loader
{
    public function __construct(private Loader $files)
    {
    }

    public function load($locale, $group, $namespace = null)
    {
        $lines = $this->files->load($locale, $group, $namespace);

        if ($group === '*' || ($namespace !== null && $namespace !== '*')) {
            return $lines;
        }

        $strings = Strings::group($group, $locale);

        return $strings === null ? $lines : array_replace_recursive($lines, $strings);
    }

    public function addNamespace($namespace, $hint)
    {
        $this->files->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path)
    {
        $this->files->addJsonPath($path);
    }

    public function namespaces()
    {
        return $this->files->namespaces();
    }
}
