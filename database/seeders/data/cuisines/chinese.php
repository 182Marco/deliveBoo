<?php

// Chinese restaurants: Cantonese dim sum, Sichuan and neighbourhood takeaway.
return [
    'types' => ['Chinese'],
    'concepts' => [
        ['Drago d\'Oro', 'budget', 'Neighbourhood Chinese takeaway with the classics: spring rolls, Cantonese rice and sweet-and-sour chicken.'],
        ['Dim Sum Jade Garden', 'ethnic', 'Cantonese dim sum house with baskets steamed to order and Hong Kong-style roast meats.'],
        ['Sichuan Hot Pepper', 'ethnic', 'Sichuan cooking with real heat and numbing peppercorns: mapo tofu, dan dan noodles and fish in chilli oil.'],
    ],
    'recipes' => [
        ['Spring Rolls · 2 pieces', 'starter', 'vietnamese', [
            'base' => [4, 'filo pastry, cabbage, carrot, bean sprouts, sweet chilli sauce', 2, 'Crisp vegetable spring rolls with sweet chilli.'],
        ]],
        ['Har Gow · 4 pieces', 'starter', 'dumplings', [
            'mid' => [7, 'rice flour, prawns, bamboo shoots, sesame oil', 2, 'Translucent prawn dumplings, pleated by hand.'],
        ]],
        ['Siu Mai · 4 pieces', 'starter', 'dumplings', [
            'mid' => [6.5, 'dumpling wrappers, pork, prawns, shiitake mushrooms, flying fish roe', 3, 'Open pork and prawn dumplings topped with roe.'],
        ]],
        ['Xiaolongbao · 6 pieces', 'starter', 'dumplings', [
            'mid' => [9, 'dumpling wrappers, pork, ginger, spring onion, chicken stock', 2, 'Soup dumplings: bite, sip the broth, then eat.'],
        ]],
        ['Char Siu Bao · 3 pieces', 'starter', 'dumplings', [
            'mid' => [6.5, 'bao bun, pork shoulder, hoisin sauce, oyster sauce', 2, 'Fluffy steamed buns filled with barbecued pork.'],
        ]],
        ['Steamed Vegetable Dumplings · 6 pieces', 'starter', 'dumplings', [
            'base' => [5, 'dumpling wrappers, cabbage, carrot, shiitake mushrooms, ginger', 2, 'Vegetable dumplings with ginger and shiitake.'],
        ]],
        ['Hot and Sour Soup', 'soup', 'soup', [
            'base' => [4.5, 'chicken stock, tofu, bamboo shoots, egg, rice vinegar, chilli pepper', 3, 'Peppery, tangy soup with tofu and ribbons of egg.'],
        ]],
        ['Wonton Soup', 'soup', 'soup', [
            'mid' => [8, 'dumpling wrappers, pork, prawns, chicken stock, bok choy', 3, 'Pork and prawn wontons in a clear broth with bok choy.'],
        ]],
        ['Cantonese Fried Rice', 'side', 'asian_bowl', [
            'base' => [5, 'jasmine rice, egg, peas, prosciutto cotto, spring onion', 2, 'Wok-fried rice with egg, ham and peas.'],
            'mid' => [8, 'jasmine rice, egg, prawns, pork shoulder, spring onion', 2, 'Fried rice with prawns and barbecued pork, smoky from the wok.'],
        ]],
        ['Sweet and Sour Chicken', 'main', 'chinese', [
            'base' => [8, 'chicken breast, wheat flour, pineapple, bell pepper, ketchup, rice vinegar', 2, 'Crispy chicken in sweet and sour sauce with pineapple and peppers.'],
        ]],
        ['Kung Pao Chicken', 'main', 'chicken', [
            'mid' => [13, 'chicken thighs, peanuts, chilli pepper, sichuan pepper, soy sauce, spring onion', 2, 'Diced chicken with roasted peanuts, dried chillies and Sichuan pepper.'],
        ]],
        ['Mapo Tofu', 'main', 'asian_bowl', [
            'mid' => [12, 'silken tofu, ground beef, chilli oil, sichuan pepper, garlic, spring onion', 2, 'Silken tofu in a fiery, numbing sauce of chilli bean paste and minced beef.'],
        ]],
        ['Vegan Mapo Tofu', 'main', 'asian_bowl', [
            'mid' => [11, 'silken tofu, shiitake mushrooms, chilli oil, sichuan pepper, garlic', 2, 'The Sichuan classic made with minced shiitake instead of meat.'],
        ]],
        ['Dan Dan Noodles', 'main', 'noodles', [
            'mid' => [12, 'egg noodles, ground beef, sesame seeds, chilli oil, peanuts, bok choy', 2, 'Noodles with spicy minced pork, sesame paste and chilli oil.'],
        ]],
        ['Beef Chow Mein', 'main', 'noodles', [
            'base' => [9, 'egg noodles, beef, bean sprouts, onion, soy sauce, oyster sauce', 2, 'Stir-fried noodles with beef and bean sprouts.'],
        ]],
        ['Fish in Sichuan Chilli Oil', 'main', 'curry', [
            'mid' => [18, 'sea bass, chilli oil, sichuan pepper, bean sprouts, garlic', 2, 'Tender fish slices poached in a broth crowned with sizzling chilli oil.'],
        ]],
        ['Crispy Duck, Pancakes and Hoisin', 'main', 'roast', [
            'mid' => [24, 'duck leg, wheat flour, hoisin sauce, cucumber, spring onion', 2, 'Half a crispy aromatic duck to roll in thin pancakes, for two.'],
        ], ['serves' => 2]],
        ['Beef with Black Bean Sauce', 'main', 'meat', [
            'mid' => [14, 'beef, bell pepper, onion, soy sauce, garlic', 1, 'Flank steak stir-fried with peppers in a fermented black bean sauce.'],
        ]],
        ['Stir-Fried Bok Choy with Garlic', 'side', 'vegetables', [
            'mid' => [7, 'bok choy, garlic, sesame oil', 1, 'Baby bok choy flash-fried with garlic.'],
        ]],
        ['Steamed Jasmine Rice', 'side', 'asian_bowl', [
            'base' => [2.5, 'jasmine rice', 1, 'Plain steamed rice.'],
        ]],
        ['Sesame Balls · 4 pieces', 'dessert', 'dessert', [
            'mid' => [5, 'rice flour, red bean paste, sesame seeds, sugar', 2, 'Fried glutinous rice balls with red bean filling.'],
        ]],
        ['Fried Banana with Honey', 'dessert', 'dessert', [
            'base' => [4.5, 'banana, wheat flour, honey, sesame seeds', 1, 'Battered banana with honey and sesame.'],
        ]],
    ],
];
