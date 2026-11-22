<?php

// Moroccan restaurants.
return [
    'types' => ['Moroccan', 'African'],
    'extra_types' => ['ethnic' => ['Ethnic']],
    'concepts' => [
        ['Riad Marrakech', 'ethnic', 'Moroccan tagines slow-cooked in clay, hand-rolled couscous on Fridays and mint tea poured from high.'],
        ['Couscous Fès', 'budget', 'Couscous and tagine takeaway with generous portions and homemade harissa.'],
    ],
    'recipes' => [
        ['Chicken Tagine with Preserved Lemon', 'main', 'stew', [
            'mid' => [16, 'halal chicken, lemon, olives, onion, ginger, saffron', 2, 'Chicken slow-cooked with preserved lemon and green olives.'],
        ]],
        ['Lamb Tagine with Prunes', 'main', 'stew', [
            'mid' => [18, 'halal lamb, dates, almonds, onion, cinnamon, ras el hanout', 2, 'Lamb with sweet dates, almonds and cinnamon.'],
        ]],
        ['Vegetable Couscous', 'main', 'stew', [
            'base' => [10, 'couscous, carrot, zucchini, chickpeas, pumpkin, ras el hanout', 2, 'Seven-vegetable couscous with a spiced broth, vegan.'],
        ]],
        ['Royal Couscous', 'main', 'stew', [
            'mid' => [19, 'couscous, halal lamb, halal chicken, merguez, chickpeas, carrot, zucchini', 3, 'Couscous with lamb, chicken, merguez and vegetables.'],
        ]],
        ['Harira', 'soup', 'soup', [
            'base' => [5.5, 'lentils, chickpeas, tomato, halal lamb, coriander, celery', 3, 'Tomato, lentil and chickpea soup with lamb.'],
        ]],
        ['Zaalouk', 'starter', 'mezze', [
            'mid' => [6, 'aubergine, tomato, garlic, cumin, paprika', 2, 'Cooked aubergine and tomato salad with cumin.'],
        ]],
        ['Briouats · 4 pieces', 'starter', 'pastry', [
            'mid' => [7, 'filo pastry, halal beef, onion, coriander, cinnamon', 2, 'Crisp triangles filled with spiced beef.'],
        ]],
        ['Pastilla', 'main', 'pastry', [
            'mid' => [17, 'filo pastry, halal chicken, almonds, egg, cinnamon, icing sugar', 3, 'Sweet and savoury chicken and almond pie dusted with cinnamon.'],
        ]],
        ['Merguez Sandwich', 'sandwich', 'sandwich', [
            'base' => [6.5, 'country bread, merguez, harissa, onion, potato', 2, 'Grilled merguez with harissa and fries in a baguette.'],
        ]],
        ['Msemen with Honey', 'dessert', 'pastry', [
            'base' => [4, 'wheat flour, semolina, butter, honey', 2, 'Layered griddled flatbread with honey.'],
        ]],
        ['Orange and Cinnamon Salad', 'dessert', 'dessert', [
            'mid' => [5, 'orange, cinnamon, mint', 1, 'Sliced oranges with cinnamon and mint.'],
        ], ['season' => [11, 4]]],
    ],
];
