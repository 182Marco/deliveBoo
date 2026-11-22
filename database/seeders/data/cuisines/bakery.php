<?php

// Bakeries.
return [
    'types' => ['Bakery'],
    'concepts' => [
        ['Forno del Quartiere', 'budget', 'Bakery with a wood oven: sourdough loaves, focaccia and pizza by the slice from 6 am.'],
        ['Panificio Grano Antico', 'budget', 'Bread from ancient grains and stone-ground flours, long fermentations and whole wheat pastries.'],
    ],
    'recipes' => [
        ['Sourdough Loaf · 1kg', 'bakery', 'bread', [
            'base' => [6, 'sourdough bread, sea salt', 1, 'Country sourdough with a dark crust, fermented 36 hours.'],
        ]],
        ['Wholewheat Loaf · 500g', 'bakery', 'bread', [
            'base' => [4, 'wholewheat flour, sea salt', 1, 'Stone-ground whole wheat bread.'],
        ]],
        ['Focaccia with Olives', 'bakery', 'bread', [
            'base' => [3.5, 'focaccia, olives, extra virgin olive oil, rosemary', 1, 'Focaccia with Taggiasca olives and rosemary.'],
        ]],
        ['Focaccia with Tomatoes', 'bakery', 'bread', [
            'base' => [3.5, 'focaccia, cherry tomatoes, oregano, extra virgin olive oil', 1, 'Apulian focaccia with cherry tomatoes.'],
        ]],
        ['Pizza Rossa by the Slice', 'bakery', 'pizza', [
            'base' => [2, 'pizza dough, tomato sauce, oregano', 1, 'Thin bakery pizza with tomato.'],
        ]],
        ['Schiacciata with Mortadella', 'sandwich', 'sandwich', [
            'base' => [6, 'focaccia, mortadella, stracciatella', 2, 'Tuscan flatbread with mortadella and stracciatella.'],
        ]],
        ['Grissini · 250g', 'bakery', 'bread', [
            'base' => [4, 'grissini, extra virgin olive oil', 1, 'Hand-stretched breadsticks.'],
        ]],
        ['Gluten-Free Bread · 400g', 'bakery', 'bread', [
            'base' => [5, 'rice flour, buckwheat, sea salt', 2, 'Gluten-free loaf baked in a separate lab.'],
        ]],
        ['Cinnamon Roll', 'bakery', 'pastry', [
            'base' => [3, 'brioche dough, cinnamon, brown sugar, cream cheese', 2, 'Soft roll swirled with cinnamon and glazed.'],
        ]],
        ['Crostata with Jam · slice', 'dessert', 'pastry', [
            'base' => [2.5, 'shortcrust pastry, sour cherry jam', 2, 'Shortcrust tart with sour cherry jam.'],
        ]],
        ['Biscotti Mix · 300g', 'dessert', 'pastry', [
            'base' => [7, 'wheat flour, butter, sugar, egg, almonds, dark chocolate', 2, 'Butter biscuits, almond cantucci and chocolate drops.'],
        ]],
    ],
];
