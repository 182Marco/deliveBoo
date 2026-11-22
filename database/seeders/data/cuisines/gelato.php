<?php

// Gelaterias.
return [
    'types' => ['Gelato'],
    'concepts' => [
        ['Gelateria Polare', 'budget', 'Artisan gelato made every morning with fresh milk from the Alps and no added colourings.'],
        ['Cremeria Pistacchio d\'Oro', 'budget', 'Gelato and sorbets with Bronte pistachio, Piedmont hazelnut and seasonal fruit.'],
        ['Sorbetteria Agrumi', 'budget', 'Dairy-free sorbets and vegan gelato made with oat and almond milk.'],
    ],
    'recipes' => [
        ['Gelato Cup · 2 flavours', 'gelato', 'gelato', [
            'base' => [[3, 'fior di latte gelato, chocolate gelato', 2, 'Fior di latte and dark chocolate.'],
                       [3.5, 'pistachio gelato, hazelnut gelato', 2, 'Pistachio and Piedmont hazelnut.']],
        ]],
        ['Gelato Cone · 3 flavours', 'gelato', 'gelato', [
            'base' => [4, 'waffle cone, stracciatella gelato, vanilla gelato, strawberry sorbet', 3, 'Crisp waffle cone with three scoops.'],
        ]],
        ['Gelato Tub 500g', 'gelato', 'gelato', [
            'base' => [[13, 'pistachio gelato, hazelnut gelato, chocolate gelato', 3, 'Half kilo with up to three flavours, packed in a thermal bag.'],
                       [12, 'fior di latte gelato, vanilla gelato, stracciatella gelato', 3, 'Half kilo of the milk-based classics.']],
        ], ['serves' => 3]],
        ['Gelato Tub 1kg', 'gelato', 'gelato', [
            'base' => [24, 'pistachio gelato, hazelnut gelato, chocolate gelato, fior di latte gelato', 3, 'One kilo with up to four flavours.'],
        ], ['serves' => 6]],
        ['Sorbet Cup · 2 flavours', 'gelato', 'gelato', [
            'base' => [3, 'lemon sorbet, mango sorbet', 2, 'Lemon and mango sorbet, dairy-free and vegan.'],
        ]],
        ['Vegan Gelato Tub 500g', 'gelato', 'gelato', [
            'base' => [13, 'dark chocolate sorbet, coconut sorbet, oat milk gelato', 3, 'Dark chocolate, coconut and oat milk vanilla, all vegan.'],
        ], ['serves' => 3]],
        ['Gluten-Free Cone · 2 flavours', 'gelato', 'gelato', [
            'base' => [3.5, 'gluten-free cone, fior di latte gelato, strawberry sorbet', 2, 'Gluten-free cone, scooped with dedicated spatulas.'],
        ]],
        ['Affogato al Caffè', 'dessert', 'gelato', [
            'base' => [4.5, 'fior di latte gelato, espresso', 2, 'Fior di latte drowned in a hot espresso.'],
        ]],
        ['Ice Cream Sandwich', 'dessert', 'gelato', [
            'base' => [4.5, 'vanilla gelato, cookie crumble, dark chocolate', 2, 'Vanilla gelato between two chocolate chip cookies.'],
        ]],
        ['Semifreddo al Torroncino · slice', 'dessert', 'dessert', [
            'base' => [5, 'cream, egg, sugar, almonds, honey', 2, 'Frozen nougat parfait with toasted almonds.'],
        ]],
        ['Gelato Cake · 8 people', 'dessert', 'cake', [
            'base' => [30, 'sponge cake, pistachio gelato, chocolate gelato, whipped cream', 3, 'Layered gelato cake, made to order.'],
        ], ['serves' => 8]],
        ['Lemon Granita', 'dessert', 'gelato', [
            'base' => [3.5, 'lemon, sugar', 1, 'Coarse Sicilian lemon granita.'],
        ], ['season' => [5, 9]]],
    ],
];
