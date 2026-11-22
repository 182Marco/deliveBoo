<?php

// Pastry shops.
return [
    'types' => ['Pastry Shop'],
    'concepts' => [
        ['Pasticceria Zucchero e Cannella', 'budget', 'Neighbourhood pastry shop: breakfast pastries, cakes to order and Sunday trays of mignon.'],
        ['Dolce Sicilia', 'budget', 'Sicilian pastry: cannoli, cassata, granita and brioche.'],
        ['Maison Praliné', 'premium', 'Fine French-Italian pâtisserie with entremets, macarons and single-origin chocolate.', [
            ['Entremet "Noisette" · 6 people', 'dessert', 'cake', 42, 'hazelnuts, milk chocolate, sponge cake, cream, butter', 3, 'Hazelnut dacquoise, gianduja cremeux and milk chocolate mousse, for six.'],
        ]],
    ],
    'recipes' => [
        ['Croissant', 'bakery', 'pastry', [
            'base' => [[1.5, 'croissant dough, butter', 1, 'Butter croissant, plain.'],
                       [1.8, 'croissant dough, custard', 1, 'Croissant filled with vanilla custard.']],
            'high' => [3.5, 'croissant dough, butter, maldon salt', 1, 'Laminated with French AOP butter, 27 layers.'],
        ]],
        ['Pain au Chocolat', 'bakery', 'pastry', [
            'base' => [1.8, 'croissant dough, dark chocolate', 1, 'Two bars of dark chocolate in laminated dough.'],
        ]],
        ['Cannolo', 'dessert', 'pastry', [
            'base' => [3, 'cannoli shells, sheep ricotta, sugar, candied orange peel, dark chocolate', 2, 'Filled to order with sheep ricotta and chocolate chips.'],
        ]],
        ['Cassata Siciliana · slice', 'dessert', 'cake', [
            'base' => [5, 'sponge cake, sheep ricotta, marzipan, candied fruit', 2, 'Ricotta, sponge and green marzipan, with candied fruit.'],
        ]],
        ['Almond Granita with Brioche', 'dessert', 'gelato', [
            'base' => [5, 'almonds, sugar, brioche dough', 1, 'Almond granita served with a warm brioche col tuppo.'],
        ], ['season' => [5, 9]]],
        ['Tiramisù Cup', 'dessert', 'tiramisu', [
            'base' => [4.5, 'mascarpone, savoiardi biscuits, espresso, egg, cocoa powder', 2, 'Single-portion tiramisù.'],
        ]],
        ['Millefoglie Slice', 'dessert', 'pastry', [
            'base' => [4, 'puff pastry, custard, icing sugar', 2, 'Puff pastry with custard.'],
        ]],
        ['Mixed Mignon · 12 pieces', 'dessert', 'pastry', [
            'base' => [14, 'shortcrust pastry, custard, chocolate gelato, mixed berries, cream', 2, 'Twelve small pastries: tartlets, bignè and cannoncini.'],
        ], ['serves' => 4]],
        ['Sachertorte · slice', 'dessert', 'cake', [
            'base' => [4.5, 'dark chocolate, butter, egg, apricot, sugar', 2, 'Chocolate cake with apricot jam and glaze.'],
        ]],
        ['Lemon Meringue Tart', 'dessert', 'dessert', [
            'base' => [4.5, 'shortcrust pastry, lemon curd, meringue', 2, 'Lemon curd tart topped with torched meringue.'],
        ]],
        ['Macarons · 6 pieces', 'dessert', 'pastry', [
            'high' => [12, 'almonds, egg, sugar, raspberries, pistachios, dark chocolate', 2, 'Raspberry, pistachio and chocolate macarons.'],
        ]],
        ['Paris-Brest', 'dessert', 'pastry', [
            'high' => [8, 'wheat flour, egg, butter, hazelnuts, cream', 2, 'Choux ring filled with hazelnut praline mousseline.'],
        ]],
        ['Saint-Honoré · 4 people', 'dessert', 'cake', [
            'high' => [32, 'puff pastry, wheat flour, egg, cream, caramel', 2, 'Puff pastry base, caramelised choux and Chantilly, for four.'],
        ]],
        ['Single-Origin Chocolate Bonbons · 9 pieces', 'dessert', 'chocolate', [
            'high' => [18, 'dark chocolate, milk chocolate, cream, hazelnuts, passion fruit', 2, 'Nine bonbons with ganaches from three origins.'],
        ]],
    ],
];
