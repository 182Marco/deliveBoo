<?php

// Lebanese and Middle Eastern restaurants.
return [
    'types' => ['Lebanese', 'Middle Eastern'],
    'concepts' => [
        ['Beirut Mezze House', 'ethnic', 'Lebanese mezze for sharing, charcoal-grilled meats and fresh flatbread from the saj.'],
        ['Falafel Fairuz', 'budget', 'Falafel and shawarma wraps, fried to order, with tahini and pickles made in house.'],
        ['Cedar & Saffron', 'premium', 'Contemporary Levantine dining with mezze tasting menus, slow-roasted lamb and Lebanese wines.', [
            ['Lamb Ouzi for Two', 'main', 'mideast', 58, 'lamb shoulder, basmati rice, almonds, pine nuts, cinnamon, yogurt', 3, 'Lamb shoulder roasted for seven hours on spiced rice with toasted nuts.'],
        ]],
    ],
    'recipes' => [
        ['Hummus', 'starter', 'hummus', [
            'base' => [5.5, 'chickpeas, tahini, lemon, garlic, extra virgin olive oil', 2, 'Smooth chickpea and tahini dip with olive oil.'],
            'mid' => [7, 'chickpeas, tahini, lemon, garlic, pine nuts, paprika, extra virgin olive oil', 2, 'Hummus topped with toasted pine nuts and paprika butter.'],
        ]],
        ['Hummus with Lamb', 'starter', 'hummus', [
            'mid' => [10, 'chickpeas, tahini, lamb, pine nuts, lemon', 3, 'Warm minced lamb and pine nuts over hummus.'],
        ]],
        ['Baba Ganoush', 'starter', 'mezze', [
            'mid' => [7, 'aubergine, tahini, garlic, lemon, pomegranate', 2, 'Smoky charred aubergine with tahini and pomegranate seeds.'],
        ]],
        ['Tabbouleh', 'salad', 'salad', [
            'mid' => [7, 'parsley, bulgur, tomato, mint, lemon, extra virgin olive oil', 2, 'Parsley salad with a little bulgur, tomato and mint.'],
        ]],
        ['Fattoush', 'salad', 'salad', [
            'mid' => [8, 'lettuce, tomato, cucumber, radish, pita bread, sumac', 3, 'Crunchy salad with toasted pita and sumac.'],
        ]],
        ['Falafel · 5 pieces', 'starter', 'falafel', [
            'base' => [5, 'falafel mix, tahini sauce, pickles', 1, 'Herby chickpea and fava fritters with tahini.'],
        ]],
        ['Falafel Wrap', 'sandwich', 'wrap', [
            'base' => [6.5, 'pita bread, falafel mix, tahini sauce, tomato, lettuce, pickles', 2, 'Falafel rolled in thin bread with tahini and pickles, vegan.'],
        ]],
        ['Chicken Shawarma Wrap', 'sandwich', 'wrap', [
            'base' => [7.5, 'pita bread, chicken thighs, garlic, pickles, potato', 2, 'Shawarma chicken with toum garlic sauce, pickles and fries inside.'],
        ]],
        ['Beef Shawarma Plate', 'main', 'mideast', [
            'mid' => [15, 'beef, tahini sauce, onion, parsley, basmati rice, pita bread', 2, 'Spiced beef shawarma with rice, tahini and pickled onions.'],
        ]],
        ['Shish Taouk', 'main', 'skewers', [
            'mid' => [15, 'chicken breast, yogurt, garlic, lemon, paprika, basmati rice', 1, 'Marinated chicken skewers from the charcoal grill.'],
        ]],
        ['Kafta Skewers', 'main', 'skewers', [
            'mid' => [16, 'lamb, beef, onion, parsley, cumin', 2, 'Minced lamb and beef skewers with parsley and onion.'],
        ]],
        ['Mixed Grill', 'main', 'meat', [
            'mid' => [24, 'lamb, chicken breast, beef, onion, tomato, pita bread', 3, 'Kafta, shish taouk and lamb cubes with grilled vegetables.'],
        ]],
        ['Kibbeh · 4 pieces', 'starter', 'mezze', [
            'mid' => [8, 'bulgur, lamb, onion, pine nuts, cinnamon', 2, 'Fried bulgur shells stuffed with spiced lamb.'],
        ]],
        ['Fatayer with Spinach · 3 pieces', 'starter', 'mezze', [
            'mid' => [6, 'wheat flour, spinach, onion, sumac, lemon', 2, 'Triangular pies filled with spinach and sumac.'],
        ]],
        ['Mezze Platter for Two', 'starter', 'mezze', [
            'mid' => [22, 'hummus, aubergine, tahini, falafel mix, parsley, bulgur, pita bread', 3, 'Hummus, baba ganoush, tabbouleh, falafel and warm bread.'],
        ], ['serves' => 2]],
        ['Manakish Za\'atar', 'starter', 'bread', [
            'base' => [4.5, 'wheat flour, za\'atar, extra virgin olive oil', 1, 'Flatbread baked with za\'atar and olive oil.'],
        ]],
        ['Baklava · 4 pieces', 'dessert', 'pastry', [
            'mid' => [6, 'filo pastry, pistachios, walnuts, butter, sugar', 2, 'Layers of filo, pistachios and walnuts in orange blossom syrup.'],
        ]],
        ['Mouhallabieh', 'dessert', 'dessert', [
            'mid' => [5, 'milk, rice flour, sugar, pistachios', 1, 'Milk pudding with rose water and pistachios.'],
        ]],
    ],
];
