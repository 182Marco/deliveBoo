<?php

// Demo accounts of the portfolio (dev/PIANO_AUTH_CLIENTI.md, "Account demo").
// Shown on the login page and protected from edits, bans and password resets while enabled.
// The defaults are development values only: a public deploy sets its own DEMO_* in the environment.
return [
    // on by default outside production; a public portfolio deploy turns it on explicitly
    'enabled' => (bool) env('DEMO_ACCOUNTS', env('APP_ENV', 'production') !== 'production'),

    // one per role, in the order of the login box; created by Database\Seeders\DemoAccountsSeeder
    'accounts' => [
        'admin' => [
            'role' => 'admin',
            'name' => 'Foodsy',
            'last_name' => 'Admin',
            'email' => env('DEMO_ADMIN_EMAIL', 'admin@foodsy.test'),
            'password' => env('DEMO_ADMIN_PASSWORD', 'foodsy-admin'),
        ],
        'restaurateur' => [
            // historic seed account (UsersTableSeeder): its password is the one of the seed
            'role' => 'restaurateur',
            'name' => 'Federico',
            'last_name' => 'Romiti',
            'email' => env('DEMO_RESTAURATEUR_EMAIL', 'federico.romiti@foodsy.test'),
            'password' => env('DEMO_RESTAURATEUR_PASSWORD', '12345678'),
        ],
        'customer' => [
            'role' => 'customer',
            'name' => 'Mario',
            'last_name' => 'Rossi',
            'email' => env('DEMO_CUSTOMER_EMAIL', 'mario.rossi@foodsy.test'),
            'password' => env('DEMO_CUSTOMER_PASSWORD', 'foodsy-demo'),
        ],
    ],
];
