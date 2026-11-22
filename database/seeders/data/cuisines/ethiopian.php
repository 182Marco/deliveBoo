<?php

// Ethiopian and Eritrean restaurants.
return [
    'types' => ['Ethiopian', 'African'],
    'extra_types' => ['ethnic' => ['Ethnic']],
    'concepts' => [
        ['Addis Injera House', 'ethnic', 'Ethiopian restaurant serving everything on injera, the sour teff flatbread, to eat with your hands, coffee ceremony included.'],
        ['Asmara Kitchen', 'ethnic', 'Eritrean and Ethiopian home cooking with many vegan fasting dishes.'],
    ],
    'recipes' => [
        ['Doro Wat', 'main', 'african', [
            'mid' => [15, 'chicken, onion, berbere, clarified butter, egg, injera', 3, 'Chicken stewed for hours in berbere and onions, with a boiled egg, on injera.'],
        ]],
        ['Beef Tibs', 'main', 'african', [
            'mid' => [15, 'beef, onion, rosemary, chilli pepper, clarified butter, injera', 2, 'Sautéed beef cubes with rosemary and green chilli.'],
        ]],
        ['Kitfo', 'main', 'african', [
            'mid' => [16, 'beef, clarified butter, chilli flakes, cardamom, injera', 1, 'Ethiopian minced beef tartare with spiced butter, served rare or lightly warmed.'],
        ]],
        ['Misir Wat', 'main', 'curry', [
            'base' => [10, 'red lentils, berbere, onion, garlic, injera', 2, 'Red lentils in berbere sauce, vegan.'],
        ]],
        ['Shiro', 'main', 'curry', [
            'mid' => [11, 'chickpea flour, berbere, onion, garlic, injera', 2, 'Silky spiced chickpea stew, vegan.'],
        ]],
        ['Vegan Beyaynetu Platter', 'main', 'african', [
            'mid' => [16, 'injera, red lentils, split peas, cabbage, collard greens, carrot, berbere', 4, 'Five fasting dishes on a large injera: lentils, split peas, cabbage and greens.'],
        ]],
        ['Mixed Platter for Two', 'main', 'african', [
            'mid' => [32, 'injera, chicken, beef, red lentils, collard greens, berbere, clarified butter', 4, 'Doro wat, tibs and three vegetable stews on a shared injera.'],
        ], ['serves' => 2]],
        ['Sambusa · 3 pieces', 'starter', 'indian', [
            'base' => [5, 'wheat flour, lentils, onion, chilli pepper, sunflower oil', 2, 'Crisp pastries filled with spiced lentils.'],
        ]],
        ['Gomen', 'side', 'vegetables', [
            'base' => [6, 'collard greens, garlic, ginger, sunflower oil', 1, 'Collard greens with garlic and ginger.'],
        ]],
        ['Ethiopian Coffee Ceremony', 'hot_drink', 'coffee', [
            'mid' => [6, 'coffee', 1, 'Beans roasted and brewed in a jebena, served in three rounds.'],
        ]],
    ],
];
