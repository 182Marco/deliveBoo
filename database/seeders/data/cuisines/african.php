<?php

// West African restaurants.
return [
    'types' => ['African'],
    'extra_types' => ['ethnic' => ['Ethnic']],
    'concepts' => [
        ['Teranga Dakar', 'ethnic', 'Senegalese cooking: thieboudienne, yassa and mafé, with bissap and ginger juice.'],
        ['Lagos Pepper Pot', 'ethnic', 'Nigerian and Ghanaian dishes: jollof rice, suya skewers and fried plantain.'],
    ],
    'recipes' => [
        ['Jollof Rice with Chicken', 'main', 'african', [
            'mid' => [14, 'basmati rice, tomato, red pepper, onion, chicken thighs, chilli pepper', 3, 'Smoky tomato and pepper rice with grilled chicken.'],
        ]],
        ['Vegetable Jollof', 'main', 'african', [
            'base' => [10, 'basmati rice, tomato, red pepper, onion, peas, carrot', 3, 'Party jollof with vegetables, vegan.'],
        ]],
        ['Chicken Yassa', 'main', 'stew', [
            'mid' => [14, 'chicken, onion, lemon, mustard, olives, basmati rice', 2, 'Chicken marinated in lemon and onions, then slowly cooked.'],
        ]],
        ['Thieboudienne', 'main', 'african', [
            'mid' => [17, 'basmati rice, sea bream, tomato, cassava, cabbage, carrot, aubergine', 3, 'Senegal\'s national dish: fish, broken rice and vegetables in tomato.'],
        ]],
        ['Beef Mafé', 'main', 'stew', [
            'mid' => [15, 'beef, peanut butter, tomato, sweet potato, onion, basmati rice', 2, 'Beef stewed in a rich peanut sauce with sweet potato.'],
        ]],
        ['Suya Skewers', 'starter', 'skewers', [
            'mid' => [11, 'beef, peanuts, chilli pepper, ginger, onion', 2, 'Grilled beef skewers in spicy peanut suya spice.'],
        ]],
        ['Fried Plantain', 'side', 'side', [
            'base' => [5, 'plantain, sunflower oil, sea salt', 1, 'Sweet ripe plantain, fried.'],
        ]],
        ['Black-Eyed Pea Fritters', 'starter', 'falafel', [
            'base' => [5, 'black-eyed peas, onion, chilli pepper, sunflower oil', 1, 'Akara: crisp bean fritters, vegan.'],
        ]],
        ['Bissap Juice', 'soft_drink', 'juice', [
            'base' => [3.5, 'hibiscus, sugar, mint, ice', 1, 'Iced hibiscus flower infusion with mint.'],
        ]],
    ],
];
