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
            'customers' => 1500,
            // the most frequent synthetic customers that get an account (CustomerProfilesSeeder)
            'registered_customers' => 300,
            // Mario Rossi's history: probability of a lunch or dinner order on a day (1.0 = about 250 orders)
            'mario_scale' => 1.0,
            // the demo restaurateur's group (DemoOwnerHistorySeeder): history from this date, at least
            // min_monthly orders a month in every restaurant, customers per restaurant in the city pools
            'owner_history' => ['from' => '2024-01-01', 'min_monthly' => 100, 'scale' => 1.0, 'pool_per_restaurant' => 1100],
        ],
        'test' => [
            'new_owners' => 2,
            'romiti_new' => 2,
            'menu_min' => 6,
            'menu_max' => 12,
            'orders_scale' => 0.1,
            'customers' => 20,
            'registered_customers' => 5,
            'mario_scale' => 0.15,
            'owner_history' => ['from' => '2026-09-01', 'min_monthly' => 4, 'scale' => 0.03, 'pool_per_restaurant' => 12],
            // one budget pizzeria, one vegan kitchen and a starred restaurant (with champagne)
            'concepts' => [['Pizzeria Il Vicoletto', 'Germoglio Vegan Kitchen'], ['Aurora Cucina d\'Autore']],
        ],
    ],
];
