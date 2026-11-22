<?php

// Turkish kebab houses and ocakbaşı grills.
return [
    'types' => ['Turkish', 'Middle Eastern'],
    'concepts' => [
        ['Istanbul Kebap Evi', 'budget', 'Döner kebab sliced from the spit, lahmacun and ayran, open until late.'],
        ['Ocakbaşı Anatolia', 'ethnic', 'Anatolian charcoal grill: Adana kebab, pide from the wood oven and meze.'],
    ],
    'recipes' => [
        ['Döner Kebab Wrap', 'sandwich', 'wrap', [
            'base' => [[6.5, 'flour tortilla, kebab meat, lettuce, tomato, onion, yogurt', 2, 'Döner sliced from the spit in a durum wrap with salad and yogurt sauce.'],
                       [7, 'pita bread, halal chicken, lettuce, tomato, red cabbage, garlic', 2, 'Chicken döner in a toasted pita.']],
        ]],
        ['Döner Plate', 'main', 'mideast', [
            'base' => [10, 'kebab meat, basmati rice, lettuce, tomato, yogurt, pita bread', 2, 'Döner kebab with rice, salad and bread.'],
        ]],
        ['Adana Kebab', 'main', 'skewers', [
            'mid' => [16, 'halal lamb, chilli flakes, onion, sumac, pita bread', 1, 'Spicy minced lamb skewer from the charcoal grill, with sumac onions.'],
        ]],
        ['İskender Kebab', 'main', 'mideast', [
            'mid' => [17, 'kebab meat, pita bread, tomato sauce, butter, yogurt', 2, 'Döner on bread cubes with tomato sauce, sizzling butter and yogurt.'],
        ]],
        ['Lahmacun', 'starter', 'pizza', [
            'base' => [5, 'wheat flour, ground beef, tomato, onion, parsley, lemon', 2, 'Thin flatbread with spiced minced meat, rolled with parsley and lemon.'],
        ]],
        ['Pide with Cheese and Egg', 'main', 'pizza', [
            'mid' => [11, 'wheat flour, feta, egg, butter', 2, 'Boat-shaped bread with white cheese and egg from the wood oven.'],
        ]],
        ['Mercimek Çorbası', 'soup', 'soup', [
            'base' => [5, 'red lentils, onion, carrot, cumin, lemon', 1, 'Red lentil soup with cumin and lemon.'],
        ]],
        ['Meze Selection', 'starter', 'mezze', [
            'mid' => [14, 'yogurt, aubergine, red pepper, walnuts, cucumber, garlic', 3, 'Haydari, ezme, muhammara and cacık with bread.'],
        ], ['serves' => 2]],
        ['Shepherd Salad', 'salad', 'salad', [
            'base' => [5, 'tomato, cucumber, bell pepper, red onion, parsley, lemon', 2, 'Chopped tomato, cucumber and pepper salad.'],
        ]],
        ['Halloumi Wrap', 'sandwich', 'wrap', [
            'base' => [7, 'flour tortilla, halloumi, tomato, lettuce, mint', 2, 'Grilled halloumi with tomato and mint.'],
        ]],
        ['Künefe', 'dessert', 'dessert', [
            'mid' => [7, 'filo pastry, mozzarella fior di latte, sugar, pistachios, butter', 2, 'Hot shredded pastry with melted cheese in syrup, topped with pistachio.'],
        ]],
        ['Baklava with Pistachio', 'dessert', 'pastry', [
            'base' => [5, 'filo pastry, pistachios, butter, sugar', 2, 'Antep pistachio baklava, four pieces.'],
        ]],
    ],
];
