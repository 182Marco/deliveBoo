<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // RefreshDatabase migrates the in-memory database once per process: seeding it there (and
    // not in every setUp) keeps the suite fast; each test then runs inside a transaction.
    // DatabaseSeederTest empties the tables first, inside its own transaction.
    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        // views use @vite: tests must not depend on "npm run build"
        $this->withoutVite();
    }
}
