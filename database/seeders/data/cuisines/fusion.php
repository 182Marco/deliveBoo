<?php

// Fusion restaurants: Asian-Mediterranean and modern fine dining.
return [
    'types' => ['Fusion'],
    'extra_types' => ['fine_dining' => ['Fine Dining'], 'starred' => ['Starred Restaurant', 'Fine Dining']],
    'concepts' => [
        ['Umami Bistrot', 'premium', 'Asian-Mediterranean small plates: Italian produce meets Japanese and Korean technique.', [
            ['Carbonara Udon', 'pasta', 'noodles', 18, 'udon noodles, guanciale, egg yolk, pecorino romano, black pepper, nori', 3, 'Thick udon in a carbonara sauce with crisp guanciale and nori powder.'],
        ]],
        ['Kōen Tokyo-Milano', 'fine_dining', 'Japanese-Italian tasting menus: kaiseki structure, Italian ingredients and a sake and Barolo pairing.', [
            ['Kaiseki-Milano Menu · 8 courses', 'tasting_menu', 'finedining', 150, 'bluefin tuna, carnaroli rice, saffron, wagyu beef, miso paste, matcha', 4, 'Eight courses between Tokyo and Milan, from saffron risotto with miso to wagyu. Price per person.'],
        ]],
        ['Astra Laboratorio', 'starred', 'Experimental kitchen with fermentation lab: koji-aged meats, garums and desserts made from vegetables.', [
            ['Koji-Aged Duck, Plum and Sansho', 'main', 'finedining', 52, 'duck breast, miso paste, apricot, sichuan pepper', 2, 'Duck aged with koji for three weeks, glazed with fermented plum.'],
            ['Tasting Menu "Ferments" · 12 courses', 'tasting_menu', 'finedining', 220, 'scallops, langoustines, duck breast, miso paste, black garlic, beetroot, white chocolate', 4, 'Twelve courses built around our fermentation lab. Price per person.'],
        ]],
    ],
    'recipes' => [
        ['Tuna Tataki, Ponzu and Burrata', 'starter', 'sashimi', [
            'high' => [24, 'tuna, ponzu, burrata, sesame seeds, spring onion', 2, 'Seared tuna, cold burrata and yuzu ponzu.'],
        ]],
        ['Salmon Crudo, Miso and Fennel', 'starter', 'sashimi', [
            'high' => [20, 'salmon, miso paste, fennel, orange, sesame oil', 2, 'Raw salmon, white miso dressing and shaved fennel.'],
        ]],
        ['Gyoza with Nduja', 'starter', 'dumplings', [
            'high' => [14, 'dumpling wrappers, pork, nduja, cabbage, ponzu', 3, 'Pork gyoza spiked with Calabrian nduja.'],
        ]],
        ['Wagyu Bao', 'starter', 'sandwich', [
            'high' => [16, 'bao bun, wagyu beef, kimchi, kewpie mayonnaise', 2, 'Steamed bun with seared wagyu and kimchi.'],
        ]],
        ['Risotto, Miso and Black Garlic', 'risotto', 'risotto', [
            'high' => [26, 'carnaroli rice, miso paste, black garlic, butter, parmigiano reggiano', 3, 'Carnaroli creamed with white miso and Parmigiano, black garlic purée.'],
        ]],
        ['Spaghetti, Sea Urchin and Yuzu', 'pasta', 'pasta', [
            'high' => [34, 'spaghetti, sea urchin, yuzu, garlic, extra virgin olive oil', 2, 'Spaghetti with raw sea urchin and yuzu zest.'],
        ], ['season' => [11, 4]]],
        ['Black Cod, Saikyo Miso and Pumpkin', 'main', 'fish', [
            'high' => [38, 'cod, miso paste, pumpkin, sake', 2, 'Miso-marinated cod with pumpkin purée.'],
        ]],
        ['Lamb, Gochujang and Yogurt', 'main', 'meat', [
            'high' => [36, 'lamb chops, gochujang, greek yogurt, mint', 2, 'Lamb chops glazed with gochujang, cooling mint yogurt.'],
        ]],
        ['Wagyu Striploin, Truffle Ponzu', 'main', 'steak', [
            'high' => [68, 'wagyu beef, ponzu, black truffle, spring onion', 1, 'Australian wagyu striploin with truffle ponzu.'],
        ]],
        ['Tofu Steak, Shiitake and Sesame', 'main', 'asian_bowl', [
            'high' => [22, 'tofu, shiitake mushrooms, sesame seeds, teriyaki sauce, bok choy', 2, 'Crisp-seared tofu with shiitake and sesame, vegan.'],
        ]],
        ['Matcha Tiramisù', 'dessert', 'tiramisu', [
            'high' => [12, 'mascarpone, matcha, savoiardi biscuits, egg, sugar', 2, 'Tiramisù made with ceremonial matcha instead of coffee.'],
        ]],
        ['Yuzu Cheesecake', 'dessert', 'cake', [
            'high' => [12, 'cream cheese, yuzu, graham crackers, sugar', 2, 'Light baked cheesecake with yuzu curd.'],
        ]],
        ['Miso Caramel and Chocolate Tart', 'dessert', 'chocolate', [
            'high' => [13, 'dark chocolate, miso paste, caramel, shortcrust pastry', 3, 'Dark chocolate tart with salted miso caramel.'],
        ]],
    ],
];
