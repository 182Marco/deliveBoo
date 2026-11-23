<?php

// Size of the generated demo catalog (database/seeders). "full" is the development dataset,
// "test" a small one with the same generator, forced by phpunit.xml to keep the suite fast.
return [
    'profile' => env('SEED_PROFILE', 'full'),

    // password of every generated restaurateur (the historic accounts keep their own)
    'owner_password' => env('SEED_OWNER_PASSWORD', 'foodsy-demo'),
    // password of every registered synthetic customer (CustomerProfilesSeeder)
    'customer_password' => env('SEED_CUSTOMER_PASSWORD', 'foodsy-demo'),

    'profiles' => [
        'full' => [
            'new_owners' => 48,
            'romiti_new' => 30,
            'menu_min' => null,
            'menu_max' => null,
            'orders_scale' => 1.0,
            'rates_scale' => 1.0,
            'customers' => 1500,
            // the most frequent synthetic customers that get an account (CustomerProfilesSeeder)
            'registered_customers' => 300,
            // Mario Rossi's history: probability of a lunch or dinner order on a day (1.0 = about 250 orders)
            'mario_scale' => 1.0,
        ],
        'test' => [
            'new_owners' => 2,
            'romiti_new' => 2,
            'menu_min' => 6,
            'menu_max' => 12,
            'orders_scale' => 0.03,
            'rates_scale' => 0.05,
            'customers' => 20,
            'registered_customers' => 5,
            'mario_scale' => 0.15,
            // one budget pizzeria, one vegan kitchen and a starred restaurant (with champagne)
            'concepts' => [['Pizzeria Il Vicoletto', 'Germoglio Vegan Kitchen'], ['Aurora Cucina d\'Autore']],
        ],
    ],
];
