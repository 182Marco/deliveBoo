<?php

// Thai restaurants.
return [
    'types' => ['Thai'],
    'extra_types' => ['ethnic' => ['Ethnic']],
    'concepts' => [
        ['Baan Siam', 'ethnic', 'Thai family kitchen with curry pastes pounded in a mortar every morning and herbs from a local grower.'],
        ['Tuk Tuk Street Wok', 'budget', 'Bangkok street wok: pad thai, fried rice and curries in boxes, fast and fiery.'],
    ],
    'recipes' => [
        ['Pad Thai', 'main', 'thai', [
            'base' => [[10, 'rice noodles, prawns, egg, bean sprouts, peanuts, tamarind, fish sauce', 3, 'Stir-fried rice noodles with prawns, egg, tamarind and crushed peanuts.'],
                       [9, 'rice noodles, tofu, egg, bean sprouts, peanuts, tamarind, soy sauce', 3, 'Vegetarian pad thai with tofu and soy instead of fish sauce.']],
            'mid' => [14, 'rice noodles, tiger prawns, egg, bean sprouts, peanuts, tamarind, fish sauce, lime', 3, 'Pad thai with tiger prawns, wrapped in a thin omelette.'],
        ]],
        ['Green Curry with Chicken', 'main', 'curry', [
            'base' => [11, 'chicken breast, thai green curry paste, coconut milk, aubergine, thai basil, jasmine rice', 3, 'Spicy green curry with chicken, Thai aubergine and jasmine rice.'],
            'mid' => [14, 'chicken thighs, thai green curry paste, coconut milk, bamboo shoots, kaffir lime leaves, thai basil, jasmine rice', 3, 'Fresh green curry paste, chicken thigh and bamboo shoots.'],
        ]],
        ['Red Curry with Duck', 'main', 'curry', [
            'mid' => [17, 'duck breast, thai red curry paste, coconut milk, pineapple, cherry tomatoes, thai basil', 3, 'Roasted duck in red curry with pineapple and cherry tomatoes.'],
        ]],
        ['Massaman Beef Curry', 'main', 'curry', [
            'mid' => [16, 'beef, massaman curry paste, coconut milk, potato, peanuts, onion', 3, 'Mild, slow-braised beef curry with potato and peanuts.'],
        ]],
        ['Vegetable Yellow Curry', 'main', 'curry', [
            'mid' => [12, 'tofu, vegan curry paste, coconut milk, potato, carrot, jasmine rice', 3, 'Vegan yellow curry with tofu, potato and carrots.'],
        ]],
        ['Tom Yum Goong', 'soup', 'soup', [
            'mid' => [10, 'prawns, tom yum broth, lemongrass, galangal, kaffir lime leaves, mushrooms, lime', 3, 'Hot and sour prawn soup with lemongrass and galangal.'],
        ]],
        ['Tom Kha Gai', 'soup', 'soup', [
            'mid' => [10, 'chicken breast, coconut milk, galangal, lemongrass, mushrooms, lime', 3, 'Coconut and galangal soup with chicken.'],
        ]],
        ['Som Tam', 'salad', 'thai', [
            'mid' => [9, 'papaya, cherry tomatoes, green beans, peanuts, dried shrimp, fish sauce, lime', 2, 'Green papaya salad pounded with lime, chilli and dried shrimp.'],
        ], ['season' => [4, 10]]],
        ['Larb Gai', 'salad', 'thai', [
            'mid' => [10, 'chicken, mint, coriander, red onion, lime, fish sauce, rice flour', 2, 'Minced chicken salad with herbs, lime and toasted rice powder.'],
        ]],
        ['Chicken Satay', 'starter', 'skewers', [
            'base' => [7, 'chicken breast, turmeric, coconut milk, peanut sauce', 2, 'Grilled turmeric chicken skewers with peanut sauce.'],
        ]],
        ['Thai Fish Cakes', 'starter', 'fish', [
            'mid' => [8, 'cod, thai red curry paste, green beans, kaffir lime leaves, sweet chilli sauce', 2, 'Springy fish cakes with red curry and lime leaf.'],
        ]],
        ['Khao Pad Fried Rice', 'main', 'asian_bowl', [
            'base' => [9, 'jasmine rice, chicken, egg, onion, tomato, fish sauce, lime', 2, 'Thai fried rice with chicken and egg.'],
        ]],
        ['Pad Krapow Moo', 'main', 'thai', [
            'mid' => [12, 'pork, thai basil, chilli pepper, garlic, oyster sauce, egg, jasmine rice', 2, 'Minced pork with holy basil and chilli, a fried egg on top.'],
        ]],
        ['Mango Sticky Rice', 'dessert', 'dessert', [
            'mid' => [7, 'mango, jasmine rice, coconut milk, sugar, sesame seeds', 2, 'Sweet coconut sticky rice with ripe mango.'],
        ], ['season' => [4, 8]]],
        ['Coconut Ice Cream', 'dessert', 'gelato', [
            'base' => [4.5, 'coconut sorbet, peanuts', 1, 'Coconut sorbet with roasted peanuts.'],
        ]],
    ],
];
