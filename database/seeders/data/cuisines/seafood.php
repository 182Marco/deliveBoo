<?php

// Mediterranean seafood restaurants, from the harbour trattoria to the premium fish restaurant.
return [
    'types' => ['Seafood', 'Mediterranean'],
    'extra_types' => ['trattoria' => ['Italian']],
    'concepts' => [
        ['Il Pescatore di Cetara', 'trattoria', 'Harbour trattoria run by a fishing family: fried paranza, spaghetti with colatura and fish of the day sold by weight.'],
        ['Lampara Fish Bar', 'budget', 'Fish street food: paper cones of fried calamari, fish burgers and seafood salads at fair prices.'],
        ['Scoglio Azzurro', 'traditional', 'Seafood restaurant with an ice counter of whole fish: grilled, baked in foil or in acqua pazza, as you choose.'],
        ['Ostrica & Bollicine', 'premium', 'Oyster bar and raw fish counter with fifteen oyster varieties and a champagne list.', [
            ['Oyster Flight · 9 varieties', 'starter', 'seafood', 48, 'oysters, lemon, shallot, red wine vinegar', 1, 'Nine oysters from Brittany, Normandy, Ireland and Sardinia, with tasting notes.'],
        ]],
        ['Maestrale Crudo Bar', 'premium', 'Mediterranean crudo bar with a daily raw menu, catalan-style shellfish and Sardinian whites.'],
    ],
    'recipes' => [
        ['Fried Calamari Cone', 'starter', 'seafood', [
            'base' => [8, 'squid, wheat flour, lemon, sunflower oil', 1, 'A paper cone of crisp fried squid rings with lemon.'],
            'mid' => [13, 'squid, semolina, lemon, sunflower oil', 1, 'Tender squid dusted in semolina and fried to order.'],
        ]],
        ['Fish Burger', 'burger', 'burger', [
            'base' => [10, 'burger bun, cod, tartare sauce, lettuce, lemon', 2, 'Crisp cod fillet in a soft bun with tartare sauce and lettuce.'],
        ]],
        ['Seafood Salad', 'salad', 'seafood', [
            'base' => [11, 'octopus, squid, prawns, celery, lemon, parsley', 3, 'Octopus, squid and prawns with celery, lemon and parsley.'],
            'mid' => [15, 'octopus, mussels, prawns, potato, celery, lemon', 3, 'Warm seafood salad with potato and celery, dressed with lemon oil.'],
        ]],
        ['Impepata di Cozze', 'starter', 'seafood', [
            'mid' => [12, 'mussels, black pepper, lemon, parsley, country bread', 1, 'Mussels steamed open with lots of black pepper, served with grilled bread.'],
        ]],
        ['Sauté di Vongole', 'starter', 'seafood', [
            'mid' => [14, 'clams, garlic, white wine, parsley, chilli pepper', 1, 'Clams sautéed with garlic, white wine and chilli.'],
        ]],
        ['Grilled Octopus with Potatoes', 'starter', 'seafood', [
            'mid' => [16, 'octopus, potato, olives, paprika, extra virgin olive oil', 1, 'Octopus tentacle grilled crisp on crushed potatoes with olives.'],
        ]],
        ['Anchovies Marinated in Lemon', 'starter', 'fish', [
            'mid' => [10, 'anchovies, lemon, garlic, parsley, extra virgin olive oil', 1, 'Fresh anchovies cured in lemon juice for a night.'],
        ]],
        ['Tuna Tartare, Avocado and Sesame', 'starter', 'sashimi', [
            'mid' => [16, 'tuna, avocado, sesame seeds, soy sauce, lime', 2, 'Diced tuna with avocado, lime and toasted sesame.'],
            'high' => [24, 'bluefin tuna, avocado, black sesame seeds, ponzu, chives', 2, 'Bluefin tuna, avocado cream, ponzu and black sesame.'],
        ]],
        ['Raw Selection', 'starter', 'sashimi', [
            'high' => [[42, 'red prawns, langoustines, sea bream, tuna, oysters, lemon', 4, 'Two red prawns, two langoustines, sea bream, tuna and two oysters.'],
                       [36, 'sea bass, tuna, salmon, scallops, lime, maldon salt', 4, 'Thin slices of sea bass, tuna, salmon and scallop with lime and salt.']],
        ]],
        ['Spaghetti alle Vongole', 'pasta', 'pasta', [
            'mid' => [15, 'spaghetti, clams, garlic, parsley, white wine, extra virgin olive oil', 2, 'The classic: clams, garlic, parsley and a splash of white wine.'],
            'high' => [26, 'spaghettoni, razor clams, clams, garlic, bottarga', 3, 'Spaghettoni with clams and razor clams, grated mullet bottarga.'],
        ]],
        ['Linguine allo Scoglio', 'pasta', 'pasta', [
            'mid' => [18, 'linguine, mussels, clams, prawns, squid, cherry tomatoes', 3, 'Linguine with mussels, clams, prawns and squid in a light tomato sauce.'],
        ]],
        ['Spaghetti with Colatura di Cetara', 'pasta', 'pasta', [
            'mid' => [14, 'spaghetti, colatura di alici, garlic, parsley, breadcrumbs', 2, 'Spaghetti with Cetara anchovy colatura, garlic and toasted breadcrumbs.'],
        ]],
        ['Black Squid Ink Risotto', 'risotto', 'risotto', [
            'mid' => [17, 'carnaroli rice, cuttlefish, squid ink, white wine, fish stock', 3, 'Creamy risotto with cuttlefish cooked in its own ink.'],
        ]],
        ['Paccheri with Swordfish and Aubergine', 'pasta', 'pasta', [
            'mid' => [16, 'paccheri, swordfish, aubergine, cherry tomatoes, mint', 3, 'Paccheri with diced swordfish, fried aubergine and mint.'],
        ], ['season' => [5, 10]]],
        ['Fregola with Clams', 'pasta', 'pasta', [
            'mid' => [17, 'semolina, clams, cherry tomatoes, parsley, saffron', 2, 'Toasted Sardinian fregola cooked like a risotto with clams.'],
        ]],
        ['Fish of the Day in Acqua Pazza', 'main', 'fish', [
            'mid' => [24, 'sea bream, cherry tomatoes, garlic, parsley, white wine', 1, 'Whole sea bream poached in "crazy water" with cherry tomatoes and parsley.'],
        ]],
        ['Grilled Sea Bass', 'main', 'fish', [
            'mid' => [22, 'sea bass, lemon, rosemary, extra virgin olive oil', 1, 'Line-caught sea bass grilled whole and filleted for you.'],
        ]],
        ['Mixed Grilled Fish', 'main', 'seafood', [
            'mid' => [28, 'prawns, squid, swordfish, langoustines, lemon', 4, 'Prawns, squid, swordfish and langoustines from the grill.'],
        ]],
        ['Gran Fritto Misto', 'main', 'seafood', [
            'mid' => [22, 'squid, prawns, anchovies, zucchini, wheat flour', 3, 'Big mixed fry of squid, prawns, anchovies and courgette sticks.'],
        ]],
        ['Tuna Steak, Sesame Crust', 'main', 'fish', [
            'mid' => [24, 'tuna, sesame seeds, soy sauce, ginger, rocket', 2, 'Seared tuna steak in a sesame crust, pink at the centre.'],
        ]],
        ['Catalan Lobster', 'main', 'seafood', [
            'high' => [60, 'lobster, tomato, red onion, celery, extra virgin olive oil', 1, 'Half a kilogram lobster, raw tomatoes, red onion and celery.'],
        ], ['season' => [5, 9]]],
        ['Seafood Platter Royale · for two', 'main', 'seafood', [
            'high' => [140, 'oysters, langoustines, king crab, red prawns, clams, lemon', 4, 'Oysters, langoustines, king crab, red prawns and clams on ice, for two.'],
        ], ['serves' => 2]],
        ['Monkfish, Saffron and Mussels', 'main', 'fish', [
            'high' => [38, 'monkfish, saffron, mussels, fennel, white wine', 2, 'Roasted monkfish with a saffron and mussel broth.'],
        ]],
        ['Roast Potatoes', 'side', 'side', [
            'mid' => [5, 'potato, rosemary, extra virgin olive oil', 1, 'Oven-roasted potatoes.'],
        ]],
        ['Grilled Vegetables', 'side', 'vegetables', [
            'mid' => [6, 'zucchini, aubergine, bell pepper, extra virgin olive oil', 3, 'Courgettes, aubergines and peppers from the grill.'],
        ]],
        ['Lemon Sorbet', 'dessert', 'gelato', [
            'mid' => [5, 'lemon sorbet, mint', 1, 'Amalfi lemon sorbet.'],
            'high' => [9, 'lemon sorbet, sparkling wine, mint', 1, 'Lemon sorbet with a pour of Franciacorta.'],
        ]],
        ['Delizia al Limone', 'dessert', 'dessert', [
            'mid' => [7, 'sponge cake, lemon curd, cream, limoncello', 2, 'Sorrento sponge dome with lemon cream and limoncello.'],
        ]],
    ],
];
