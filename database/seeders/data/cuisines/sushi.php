<?php

// Japanese sushi restaurants: all-you-can-eat style delivery, sushi bars and omakase counters.
return [
    'types' => ['Japanese', 'Sushi'],
    'extra_types' => ['premium' => ['Fine Dining']],
    'concepts' => [
        ['Sakura Sushi Express', 'budget', 'Fast sushi delivery with combo boxes, maki and poke at accessible prices.'],
        ['Hoshi Sushi', 'ethnic', 'Neighbourhood sushi bar with a Japanese-trained chef: nigiri, uramaki, temaki and a few warm dishes.'],
        ['Kaito Sushi Bar', 'ethnic', 'Counter sushi bar with fish cut to order and a long list of creative uramaki.'],
        ['Omakase Kintsugi', 'premium', 'Twelve-seat omakase counter: aged fish, rice seasoned with red vinegar and courses served one at a time.', [
            ['Omakase 15 Pieces', 'tasting_menu', 'sushi', 95, 'sushi rice, bluefin tuna, yellowtail, sea urchin, scallops, salmon roe, eel', 4, 'Fifteen pieces chosen by the chef, including otoro, uni and aged yellowtail.'],
            ['Otoro Nigiri · 2 pieces', 'sushi', 'sushi', 22, 'sushi rice, bluefin tuna, soy sauce', 2, 'Fatty bluefin belly brushed with nikiri soy.'],
        ]],
    ],
    'recipes' => [
        ['Edamame', 'starter', 'asian_bowl', [
            'base' => [4.5, 'edamame, sea salt', 1, 'Steamed soy beans with sea salt.'],
            'mid' => [5.5, 'edamame, sea salt, chilli flakes, garlic', 1, 'Edamame tossed with garlic and chilli.'],
        ]],
        ['Miso Soup', 'soup', 'soup', [
            'base' => [3.5, 'miso broth, tofu, wakame, spring onion', 2, 'Miso soup with tofu, wakame and spring onion.'],
            'mid' => [4.5, 'miso broth, tofu, wakame, enoki mushrooms, spring onion', 2, 'Red and white miso blend with tofu and enoki.'],
        ]],
        ['Seaweed Salad', 'salad', 'salad', [
            'base' => [5, 'seaweed salad, sesame seeds', 1, 'Marinated wakame with sesame.'],
        ]],
        ['Gyoza · 5 pieces', 'starter', 'dumplings', [
            'base' => [6, 'dumpling wrappers, pork, cabbage, ginger, soy sauce', 2, 'Pan-fried pork and cabbage dumplings.'],
            'mid' => [7, 'dumpling wrappers, chicken, shiitake mushrooms, ginger, ponzu', 2, 'Chicken and shiitake gyoza with ponzu dip.'],
        ]],
        ['Vegetable Gyoza · 5 pieces', 'starter', 'dumplings', [
            'mid' => [6.5, 'dumpling wrappers, cabbage, carrot, shiitake mushrooms, soy sauce', 2, 'Steamed then seared dumplings filled with vegetables and shiitake.'],
        ]],
        ['Ebi Tempura', 'starter', 'seafood', [
            'base' => [8, 'prawns, tempura batter, sunflower oil, soy sauce', 1, 'Four prawns in light tempura batter.'],
            'mid' => [10, 'tiger prawns, tempura batter, daikon, dashi', 1, 'Tiger prawns in tempura with tentsuyu dipping sauce and grated daikon.'],
        ]],
        ['Vegetable Tempura', 'starter', 'asian_bowl', [
            'mid' => [8, 'sweet potato, zucchini, aubergine, tempura batter, sunflower oil', 3, 'Seasonal vegetables fried in tempura.'],
        ]],
        ['Sashimi Moriawase · 12 slices', 'sushi', 'sushi', [
            'mid' => [18, 'salmon, tuna, sea bass, soy sauce', 3, 'Twelve slices of salmon, tuna and sea bass.'],
            'high' => [32, 'bluefin tuna, yellowtail, sea bream, scallops, soy sauce', 4, 'Bluefin, hamachi, madai and hokkaido scallop.'],
        ]],
        ['Salmon Sashimi · 8 slices', 'sushi', 'sushi', [
            'base' => [10, 'salmon, soy sauce', 1, 'Eight slices of Norwegian salmon.'],
        ]],
        ['Nigiri Mix · 8 pieces', 'sushi', 'sushi', [
            'base' => [11, 'sushi rice, salmon, tuna, prawns, sea bass', 3, 'Two each of salmon, tuna, prawn and sea bass.'],
            'mid' => [16, 'sushi rice, salmon, tuna, sea bream, eel, prawns', 3, 'Eight nigiri including eel and sweet prawn.'],
        ]],
        ['Salmon Nigiri · 2 pieces', 'sushi', 'sushi', [
            'base' => [3.5, 'sushi rice, salmon', 2, 'Two salmon nigiri.'],
            'mid' => [4.5, 'sushi rice, salmon, lemon', 2, 'Seared salmon nigiri with lemon and salt.'],
        ]],
        ['Tuna Nigiri · 2 pieces', 'sushi', 'sushi', [
            'mid' => [5, 'sushi rice, tuna', 2, 'Two lean tuna nigiri.'],
        ]],
        ['Unagi Nigiri · 2 pieces', 'sushi', 'sushi', [
            'mid' => [6, 'sushi rice, eel, unagi sauce, sesame seeds', 2, 'Glazed grilled eel on rice.'],
        ]],
        ['Hosomaki Salmon · 6 pieces', 'sushi', 'sushi', [
            'base' => [4.5, 'sushi rice, nori, salmon', 3, 'Thin salmon rolls.'],
        ]],
        ['Hosomaki Cucumber · 6 pieces', 'sushi', 'sushi', [
            'base' => [3.5, 'sushi rice, nori, cucumber, sesame seeds', 3, 'Thin cucumber rolls, vegan.'],
        ]],
        ['California Uramaki · 8 pieces', 'sushi', 'sushi', [
            'base' => [8, 'sushi rice, nori, crab, avocado, cucumber, mayonnaise, sesame seeds', 3, 'Inside-out roll with crab, avocado and cucumber.'],
        ]],
        ['Spicy Tuna Uramaki · 8 pieces', 'sushi', 'sushi', [
            'base' => [9, 'sushi rice, nori, tuna, spicy mayo, spring onion', 3, 'Tuna with spicy mayo and spring onion.'],
            'mid' => [11, 'sushi rice, nori, tuna, sriracha, cucumber, shichimi togarashi', 3, 'Chopped tuna with sriracha, cucumber and seven-spice.'],
        ]],
        ['Dragon Roll · 8 pieces', 'sushi', 'sushi', [
            'mid' => [14, 'sushi rice, nori, prawns, tempura batter, avocado, eel, unagi sauce', 3, 'Prawn tempura inside, avocado and grilled eel on top.'],
        ]],
        ['Philadelphia Roll · 8 pieces', 'sushi', 'sushi', [
            'base' => [9, 'sushi rice, nori, salmon, cream cheese, avocado', 3, 'Salmon, cream cheese and avocado.'],
        ]],
        ['Crunchy Salmon Roll · 8 pieces', 'sushi', 'sushi', [
            'mid' => [12, 'sushi rice, nori, salmon, avocado, panko, teriyaki sauce', 3, 'Salmon and avocado roll rolled in crunchy panko with teriyaki.'],
        ]],
        ['Veggie Rainbow Roll · 8 pieces', 'sushi', 'sushi', [
            'mid' => [10, 'sushi rice, nori, avocado, cucumber, carrot, mango, sesame seeds', 3, 'Avocado, cucumber, carrot and mango, fully vegan.'],
        ]],
        ['Temaki Salmon Avocado', 'sushi', 'sushi', [
            'base' => [5, 'sushi rice, nori, salmon, avocado', 3, 'Hand roll cone with salmon and avocado.'],
        ]],
        ['Chirashi Bowl', 'sushi', 'poke', [
            'mid' => [18, 'sushi rice, salmon, tuna, sea bass, salmon roe, tamago, pickled ginger', 3, 'Sushi rice bowl topped with sliced fish and salmon roe.'],
        ]],
        ['Sushi Combo Box · 24 pieces', 'sushi', 'sushi', [
            'base' => [22, 'sushi rice, nori, salmon, tuna, avocado, cucumber, cream cheese', 3, 'Mixed nigiri, hosomaki and uramaki, for one or two.'],
        ], ['serves' => 2]],
        ['Sushi Party Boat · 48 pieces', 'sushi', 'sushi', [
            'mid' => [58, 'sushi rice, nori, salmon, tuna, sea bass, prawns, avocado, eel', 4, 'Forty-eight pieces for three to four people.'],
        ], ['serves' => 4]],
        ['Chicken Teriyaki', 'main', 'chicken', [
            'base' => [11, 'chicken thighs, teriyaki sauce, jasmine rice, sesame seeds', 2, 'Grilled chicken thigh glazed with teriyaki, with steamed rice.'],
        ]],
        ['Black Cod Miso', 'main', 'fish', [
            'high' => [36, 'cod, miso paste, sake, sugar', 2, 'Cod marinated three days in sweet miso, then broiled.'],
        ]],
        ['Wagyu Tataki', 'starter', 'steak', [
            'high' => [34, 'wagyu beef, ponzu, garlic, spring onion', 1, 'Seared wagyu slices with ponzu and crisp garlic.'],
        ]],
        ['Yellowtail Jalapeño', 'starter', 'sashimi', [
            'high' => [24, 'yellowtail, jalapeño, ponzu, coriander', 2, 'Thin yellowtail with jalapeño slivers and yuzu ponzu.'],
        ]],
        ['Steamed Rice', 'side', 'asian_bowl', [
            'base' => [2.5, 'sushi rice', 1, 'A bowl of Japanese rice.'],
        ]],
        ['Mochi Ice Cream · 3 pieces', 'dessert', 'gelato', [
            'base' => [5.5, 'mochi dough, ice cream base, matcha, mango', 2, 'Matcha, mango and vanilla mochi.'],
        ]],
        ['Matcha Cheesecake', 'dessert', 'cake', [
            'mid' => [7, 'cream cheese, matcha, egg, sugar, graham crackers', 2, 'Baked cheesecake with ceremonial matcha.'],
        ]],
        ['Dorayaki', 'dessert', 'dessert', [
            'mid' => [5, 'wheat flour, egg, honey, red bean paste', 2, 'Two honey pancakes sandwiching sweet red bean paste.'],
        ]],
    ],
];
