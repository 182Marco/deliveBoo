<?php

// Poke bars.
return [
    'types' => ['Poke'],
    'concepts' => [
        ['Aloha Poke', 'budget', 'Hawaiian poke bowls with marinated raw fish, sushi rice and fresh toppings, built your way.'],
        ['Mahalo Bowl', 'budget', 'Poke, salads and smoothies for a light lunch.'],
    ],
    'recipes' => [
        ['Salmon Poke', 'poke', 'poke', [
            'base' => [[12, 'sushi rice, salmon, avocado, edamame, cucumber, soy sauce, sesame seeds', 2, 'Salmon marinated in shoyu with avocado, edamame and cucumber.'],
                       [13, 'brown rice, salmon, mango, red cabbage, spring onion, spicy mayo', 2, 'Salmon on brown rice with mango and spicy mayo.']],
        ], ['cal' => 640]],
        ['Tuna Poke', 'poke', 'poke', [
            'base' => [13, 'sushi rice, tuna, seaweed salad, cucumber, avocado, ponzu', 2, 'Ahi tuna with seaweed salad and ponzu.'],
        ], ['cal' => 590]],
        ['Spicy Prawn Poke', 'poke', 'poke', [
            'base' => [12.5, 'sushi rice, prawns, sweetcorn, pineapple, sriracha, spring onion', 2, 'Cooked prawns with pineapple and sriracha mayo.'],
        ], ['cal' => 610]],
        ['Chicken Teriyaki Poke', 'poke', 'poke', [
            'base' => [11, 'jasmine rice, chicken breast, teriyaki sauce, edamame, carrot, sesame seeds', 2, 'Grilled chicken with teriyaki glaze.'],
        ], ['cal' => 680]],
        ['Tofu Poke', 'poke', 'poke', [
            'base' => [10.5, 'brown rice, tofu, avocado, edamame, carrot, red cabbage, tamari', 2, 'Marinated tofu with vegetables and tamari, vegan.'],
        ], ['cal' => 540]],
        ['Quinoa Green Bowl', 'poke', 'vegan_bowl', [
            'base' => [10.5, 'quinoa, baby spinach, avocado, cucumber, chickpeas, pumpkin seeds, lemon', 3, 'Quinoa, spinach, avocado, chickpeas and seeds with lemon, vegan and gluten free.'],
        ], ['cal' => 520]],
        ['Edamame with Sea Salt', 'side', 'asian_bowl', [
            'base' => [4, 'edamame, sea salt', 1, 'Steamed edamame.'],
        ]],
        ['Mango Smoothie', 'soft_drink', 'smoothie', [
            'base' => [5, 'mango, banana, coconut water', 1, 'Mango, banana and coconut water.'],
        ]],
        ['Açaí Bowl', 'dessert', 'smoothie', [
            'base' => [8, 'acai, banana, granola, blueberries, coconut', 2, 'Açaí with granola, blueberries and coconut.'],
        ]],
    ],
];
