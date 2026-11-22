<?php

// Japanese sushi restaurants: all-you-can-eat style delivery, sushi bars and omakase counters.
// A sushi menu is long (80-150 items, Support\MenuPlan): next to the hand-written dishes below,
// the classic formats are listed per fish, each fish with its own description.

// fish => [display name, ingredients, base price of 2 nigiri (null = only in mid), mid price, what makes it special]
$fish = [
    'salmon' => ['Salmon', 'salmon', 3.5, 4.5, 'Norwegian salmon, buttery and mild'],
    'seared salmon' => ['Seared Salmon', 'salmon, lemon', 4, 5, 'salmon torched with a blowtorch until the fat melts'],
    'tuna' => ['Tuna', 'tuna', 4, 5.5, 'lean akami tuna, deep red and clean'],
    'otoro' => ['Otoro', 'bluefin tuna', null, 14, 'fatty bluefin belly, marbled and melting'],
    'sea bass' => ['Sea Bass', 'sea bass', 3.5, 4.5, 'Mediterranean sea bass, firm and delicate'],
    'sea bream' => ['Sea Bream', 'sea bream', 3.5, 5, 'sea bream with a hint of shiso'],
    'yellowtail' => ['Yellowtail', 'yellowtail', 4.5, 6, 'hamachi, rich and slightly citrusy'],
    'mackerel' => ['Mackerel', 'mackerel, rice vinegar', 3.5, 4.5, 'saba cured in salt and rice vinegar'],
    'eel' => ['Eel', 'eel, unagi sauce, sesame seeds', 4.5, 6, 'grilled freshwater eel brushed with sweet kabayaki glaze'],
    'prawn' => ['Prawn', 'prawns', 3.5, 4.5, 'butterflied cooked prawn'],
    'red prawn' => ['Red Prawn', 'red prawns', null, 9, 'raw Mazara red prawn, sweet as candy'],
    'scallop' => ['Scallop', 'scallops, lemon', 5, 7, 'Hokkaido scallop, raw and sweet'],
    'octopus' => ['Octopus', 'octopus', 3.5, 4.5, 'tender boiled octopus, sliced thin'],
    'squid' => ['Squid', 'squid, lemon', 3.5, 4.5, 'raw squid scored and finished with lemon zest'],
    'tamago' => ['Tamago', 'tamago, sugar', 3, 3.5, 'sweet layered Japanese omelette'],
];
$extra = [];
foreach ($fish as $key => [$name, $ing, $base, $mid, $what]) {
    $tiers = fn ($b, $m, $list, $main, $desc) => array_filter(['base' => $b === null ? null : [$b, $list, $main, $desc], 'mid' => [$m, $list, $main, $desc]]);
    $extra[] = ["{$name} Nigiri · 2 pieces", 'sushi', 'sushi', $tiers($base, $mid, "sushi rice, {$ing}, wasabi", 2, ucfirst($what) . ' on hand-pressed rice with a touch of wasabi.')];
    if (! in_array($key, ['tamago', 'seared salmon', 'eel'], true)) {
        $extra[] = ["{$name} Sashimi · 5 slices", 'sushi', 'sushi', $tiers($base === null ? null : $base * 2.4, $mid * 2.4, "{$ing}, daikon, shiso", 1, ucfirst($what) . ', five slices on shredded daikon.')];
    }
}
foreach ([
    ['Salmon Tartare Gunkan · 2 pieces', 'sushi rice, nori, salmon, spring onion, kewpie mayonnaise', 4, 5, 'Nori-wrapped rice boats topped with chopped salmon and mayo.'],
    ['Spicy Tuna Gunkan · 2 pieces', 'sushi rice, nori, tuna, sriracha, spring onion', 4.5, 5.5, 'Chopped tuna with chilli sauce on a nori boat.'],
    ['Ikura Gunkan · 2 pieces', 'sushi rice, nori, salmon roe', 5.5, 7, 'Salmon roe marinated in soy and sake, bursting on the tongue.'],
    ['Tobiko Gunkan · 2 pieces', 'sushi rice, nori, tobiko', 4, 5, 'Crunchy flying fish roe on a nori boat.'],
    ['Uni Gunkan · 2 pieces', 'sushi rice, nori, sea urchin', null, 14, 'Sea urchin tongues, briny and creamy.'],
    ['Inari · 2 pieces', 'inari tofu, sushi rice, sesame seeds', 3, 3.5, 'Sweet fried tofu pockets filled with sushi rice, vegan.'],
] as [$name, $ing, $b, $m, $desc]) {
    $extra[] = [$name, 'sushi', 'sushi', array_filter(['base' => $b ? [$b, $ing, 2, $desc] : null, 'mid' => [$m, $ing, 2, $desc]])];
}
foreach ([
    ['Hosomaki Tuna · 6 pieces', 'sushi rice, nori, tuna', 5, 6, 'Thin rolls with a strip of lean tuna.'],
    ['Hosomaki Avocado · 6 pieces', 'sushi rice, nori, avocado', 3.5, 4.5, 'Thin avocado rolls, vegan.'],
    ['Hosomaki Takuan · 6 pieces', 'sushi rice, nori, takuan', 3.5, 4, 'Crunchy yellow pickled daikon rolls, vegan.'],
    ['Hosomaki Shrimp Tempura · 6 pieces', 'sushi rice, nori, prawns, tempura batter', 5.5, 6.5, 'Thin rolls with a crisp tempura prawn.'],
    ['Temaki Spicy Tuna', 'sushi rice, nori, tuna, spicy mayo, cucumber', 5.5, 6.5, 'Hand roll cone with spicy chopped tuna and cucumber.'],
    ['Temaki Ebi Tempura', 'sushi rice, nori, prawns, tempura batter, avocado, kewpie mayonnaise', 6, 7, 'Hand roll with a tempura prawn and avocado.'],
    ['Temaki Veggie', 'sushi rice, nori, avocado, cucumber, carrot, sesame seeds', 4, 5, 'Hand roll with avocado, cucumber and carrot, vegan.'],
    ['Temaki Eel and Cucumber', 'sushi rice, nori, eel, cucumber, unagi sauce', 6.5, 7.5, 'Hand roll with glazed eel and cucumber.'],
    ['Temaki Salmon Skin', 'sushi rice, nori, salmon, cucumber, spring onion', 4.5, 5.5, 'Crisp grilled salmon skin with cucumber and spring onion.'],
] as [$name, $ing, $b, $m, $desc]) {
    $extra[] = [$name, 'sushi', 'sushi', ['base' => [$b, $ing, 3, $desc], 'mid' => [$m, $ing, 3, $desc]]];
}
// uramaki: inside-out rolls, 8 pieces
foreach ([
    ['Rainbow Roll', 'sushi rice, nori, crab, avocado, cucumber, salmon, tuna, sea bass', 12, 'Crab and avocado inside, a rainbow of salmon, tuna and sea bass on top.'],
    ['Tiger Roll', 'sushi rice, nori, tiger prawns, tempura batter, avocado, salmon, spicy mayo', 13, 'Tempura tiger prawn inside, striped with salmon and spicy mayo.'],
    ['Volcano Roll', 'sushi rice, nori, salmon, cream cheese, spicy mayo, tobiko', 12, 'Baked salmon and spicy mayo poured over a cream cheese roll.'],
    ['Spider Roll', 'sushi rice, nori, soft-shell crab, tempura batter, cucumber, kewpie mayonnaise', 14, 'Whole fried soft-shell crab with cucumber and mayo.'],
    ['Salmon Avocado Roll', 'sushi rice, nori, salmon, avocado, sesame seeds', 9, 'Salmon and avocado rolled in sesame.'],
    ['Ebi Tempura Roll', 'sushi rice, nori, prawns, tempura batter, avocado, teriyaki sauce', 10, 'Tempura prawn and avocado with a teriyaki drizzle.'],
    ['Spicy Salmon Roll', 'sushi rice, nori, salmon, sriracha, spring onion, cucumber', 9.5, 'Chopped salmon with chilli and spring onion.'],
    ['Truffle Salmon Roll', 'sushi rice, nori, salmon, avocado, truffle oil, panko', 14, 'Seared salmon on top, truffle oil and crunchy panko.'],
    ['Mango Tuna Roll', 'sushi rice, nori, tuna, mango, avocado, sesame seeds', 11, 'Tuna inside, ripe mango slices on top.'],
    ['Crispy Chicken Roll', 'sushi rice, nori, chicken breast, panko, lettuce, teriyaki sauce', 9, 'Panko chicken with lettuce and teriyaki, for those who skip raw fish.'],
    ['Futomaki Vegetable', 'sushi rice, nori, tamago, cucumber, carrot, takuan, shiitake mushrooms', 9, 'Thick traditional roll with omelette, pickles and simmered shiitake.'],
    ['Avocado Cucumber Roll', 'sushi rice, nori, avocado, cucumber, sesame seeds', 7.5, 'Simple vegan roll with avocado and cucumber.'],
    ['Sweet Potato Tempura Roll', 'sushi rice, nori, sweet potato, tempura batter, teriyaki sauce', 8, 'Tempura sweet potato with a sweet soy glaze, vegan.'],
    ['Scallop Yuzu Roll', 'sushi rice, nori, scallops, yuzu kosho, cucumber, chives', 15, 'Raw scallop dressed with yuzu kosho, cucumber inside.'],
    ['Eel Avocado Roll', 'sushi rice, nori, eel, avocado, unagi sauce, sesame seeds', 13, 'Glazed eel and avocado.'],
    ['Tuna Tataki Roll', 'sushi rice, nori, tuna, ponzu, spring onion, garlic', 13, 'Seared tuna on top with ponzu and crisp garlic.'],
    ['Philadelphia Crunch Roll', 'sushi rice, nori, salmon, cream cheese, panko, unagi sauce', 11, 'Salmon and cream cheese, fried in panko and glazed.'],
    ['Alaska Roll', 'sushi rice, nori, smoked salmon, cream cheese, cucumber', 10, 'Smoked salmon, cream cheese and cucumber.'],
    ['Boston Roll', 'sushi rice, nori, prawns, avocado, cucumber, kewpie mayonnaise', 9.5, 'Cooked prawn, avocado and cucumber with mayo.'],
    ['Hamachi Jalapeño Roll', 'sushi rice, nori, yellowtail, jalapeño, coriander, ponzu', 14, 'Yellowtail with jalapeño and coriander.'],
    ['Wagyu Aburi Roll', 'sushi rice, nori, wagyu beef, asparagus, black truffle', 22, 'Torched wagyu over an asparagus roll, a shaving of truffle.'],
    ['Red Dragon Roll', 'sushi rice, nori, prawns, tempura batter, tuna, sriracha', 14, 'Tempura prawn inside, tuna and sriracha on top.'],
    ['Green Dragon Roll', 'sushi rice, nori, cucumber, asparagus, avocado, sesame seeds', 10, 'Avocado scales over a cucumber and asparagus roll, vegan.'],
    ['Kani Salad Roll', 'sushi rice, nori, crab, cucumber, kewpie mayonnaise, tobiko', 10, 'Crab salad with cucumber, rolled in tobiko.'],
    ['Crispy Rice Spicy Tuna · 4 pieces', 'sushi rice, tuna, spicy mayo, jalapeño, sunflower oil', 11, 'Pan-fried rice squares topped with spicy tuna and jalapeño.'],
] as [$name, $ing, $mid, $desc]) {
    $extra[] = [$name . (str_contains($name, '·') ? '' : ' · 8 pieces'), 'sushi', 'sushi', ['base' => [round($mid * 0.85 * 2) / 2, $ing, 3, $desc], 'mid' => [$mid, $ing, 3, $desc]]];
}
// starters, fried, salads, rice bowls
foreach ([
    ['Spicy Edamame', 'starter', 'asian_bowl', 'edamame, chilli flakes, garlic, sesame oil', 5, 'Edamame tossed in chilli, garlic and sesame oil.'],
    ['Chicken Karaage', 'starter', 'chicken', 'chicken thighs, rice flour, ginger, soy sauce, kewpie mayonnaise', 7, 'Ginger and soy fried chicken with Kewpie mayo.'],
    ['Takoyaki · 6 pieces', 'starter', 'streetfood', 'octopus, wheat flour, egg, tonkatsu sauce, kewpie mayonnaise, bonito flakes', 7, 'Octopus dumplings with sauce, mayo and bonito flakes.'],
    ['Harumaki · 3 pieces', 'starter', 'vietnamese', 'filo pastry, cabbage, carrot, shiitake mushrooms, sweet chilli sauce', 5, 'Japanese crispy spring rolls with vegetables.'],
    ['Ebi Fry · 4 pieces', 'starter', 'seafood', 'prawns, panko, egg, tonkatsu sauce', 8, 'Panko-crumbed prawns with tonkatsu sauce.'],
    ['Soft-Shell Crab Tempura', 'starter', 'seafood', 'soft-shell crab, tempura batter, ponzu, daikon', 12, 'Whole soft-shell crab in tempura with ponzu.'],
    ['Ika Fry', 'starter', 'seafood', 'squid, rice flour, lemon, shichimi togarashi', 8, 'Fried squid rings with seven-spice.'],
    ['Pork Tonkatsu', 'main', 'meat', 'pork, panko, egg, cabbage, tonkatsu sauce', 13, 'Breaded pork cutlet with shredded cabbage and tonkatsu sauce.'],
    ['Salmon Tartare', 'starter', 'sashimi', 'salmon, avocado, spring onion, ponzu, sesame seeds', 11, 'Hand-cut salmon with avocado and ponzu.'],
    ['Tuna Tartare with Avocado', 'starter', 'sashimi', 'tuna, avocado, sesame oil, soy sauce, chives', 13, 'Hand-cut tuna and avocado with sesame and chives.'],
    ['Sea Bass Carpaccio New Style', 'starter', 'sashimi', 'sea bass, ponzu, sesame oil, ginger, chives', 12, 'Thin sea bass seared by hot sesame oil, ponzu and ginger.'],
    ['Salmon Carpaccio with Yuzu', 'starter', 'sashimi', 'salmon, yuzu, extra virgin olive oil, black pepper', 11, 'Salmon carpaccio with yuzu and olive oil.'],
    ['Tuna Tataki', 'starter', 'sashimi', 'tuna, sesame seeds, ponzu, spring onion', 13, 'Sesame-crusted tuna seared on the outside.'],
    ['Wakame and Cucumber Sunomono', 'salad', 'salad', 'wakame, cucumber, rice vinegar, sesame seeds', 5, 'Vinegared cucumber and seaweed salad.'],
    ['Salmon Skin Salad', 'salad', 'salad', 'mixed leaves, salmon, cucumber, ponzu, sesame seeds', 9, 'Leaves with crispy salmon skin and ponzu.'],
    ['Clear Soup with Clams', 'soup', 'soup', 'clams, dashi, spring onion', 6, 'Light dashi broth with clams.'],
    ['Salmon Don', 'main', 'poke', 'sushi rice, salmon, salmon roe, nori, spring onion', 15, 'Rice bowl covered with salmon slices and ikura.'],
    ['Tekka Don', 'main', 'poke', 'sushi rice, tuna, nori, wasabi', 16, 'Rice bowl with marinated tuna.'],
    ['Unagi Don', 'main', 'poke', 'sushi rice, eel, unagi sauce, sesame seeds', 18, 'Grilled eel over rice with its glaze.'],
    ['Yasai Udon', 'main', 'noodles', 'udon noodles, dashi, shiitake mushrooms, spinach, spring onion', 11, 'Thick udon in dashi with vegetables.'],
    ['Yaki Udon with Prawns', 'main', 'noodles', 'udon noodles, prawns, cabbage, carrot, soy sauce', 13, 'Stir-fried udon with prawns and vegetables.'],
    ['Sushi Box · 12 pieces', 'sushi', 'sushi', 'sushi rice, nori, salmon, tuna, avocado, cucumber', 14, 'Four nigiri, four hosomaki and four uramaki.'],
    ['Sushi Box · 36 pieces', 'sushi', 'sushi', 'sushi rice, nori, salmon, tuna, sea bass, prawns, avocado, cream cheese', 39, 'Nigiri, sashimi and rolls for two or three people.'],
    ['Salmon Lovers Box · 20 pieces', 'sushi', 'sushi', 'sushi rice, nori, salmon, avocado, cream cheese', 24, 'Salmon nigiri, sashimi, hosomaki and uramaki.'],
    ['Vegan Sushi Box · 18 pieces', 'sushi', 'sushi', 'sushi rice, nori, avocado, cucumber, inari tofu, takuan, sweet potato', 17, 'Inari, hosomaki and vegetable uramaki, fully vegan.'],
    ['Yuzu Sorbet', 'dessert', 'gelato', 'lemon sorbet, yuzu', 5, 'Sorbet scented with yuzu.'],
    ['Mochi Matcha · 2 pieces', 'dessert', 'dessert', 'mochi dough, matcha, red bean paste', 5, 'Soft rice cakes with matcha and red bean.'],
] as [$name, $course, $img, $ing, $mid, $desc]) {
    // boxes of many pieces are for sharing: one person every 12 pieces
    $pieces = preg_match('/(\d+) pieces/', $name, $m) ? (int) $m[1] : 0;
    $extra[] = [$name, $course, $img, ['base' => [round($mid * 0.85 * 2) / 2, $ing, 2, $desc], 'mid' => [$mid, $ing, 2, $desc]], $pieces >= 18 ? ['serves' => intdiv($pieces, 12)] : []];
}

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
        ['Nigiri Mix · 8 pieces', 'sushi', 'sushi', [
            'base' => [11, 'sushi rice, salmon, tuna, prawns, sea bass', 3, 'Two each of salmon, tuna, prawn and sea bass.'],
            'mid' => [16, 'sushi rice, salmon, tuna, sea bream, eel, prawns', 3, 'Eight nigiri including eel and sweet prawn.'],
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
        ...$extra,
    ],
];
