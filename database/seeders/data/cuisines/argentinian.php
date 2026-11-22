<?php

// Argentinian parrillas and steakhouses.
return [
    'types' => ['Argentinian', 'Steakhouse'],
    'concepts' => [
        ['Parrilla La Pampa', 'traditional', 'Argentinian grill: grass-fed cuts cooked over quebracho embers, empanadas and Malbec.'],
        ['Estancia Bife', 'premium', 'Premium steakhouse with a dry-aging room, Argentinian, Uruguayan and Italian beef and a long Malbec list.', [
            ['Tomahawk 1.2 kg · for two', 'main', 'steak', 98, 'rib-eye steak, sea salt, chimichurri, potato', 1, 'Thirty-day dry-aged tomahawk with chimichurri and potatoes, for two.'],
        ]],
    ],
    'recipes' => [
        ['Bife de Chorizo', 'main', 'steak', [
            'mid' => [26, 'beef sirloin, sea salt, chimichurri', 1, 'Thick-cut sirloin, 400 g, grilled and served with chimichurri.'],
            'high' => [38, 'beef sirloin, maldon salt, chimichurri, bone marrow', 1, 'Dry-aged sirloin with roasted bone marrow.'],
        ]],
        ['Ojo de Bife', 'main', 'steak', [
            'mid' => [28, 'rib-eye steak, sea salt, chimichurri', 1, 'Rib-eye, 350 g, with chimichurri.'],
            'high' => [42, 'rib-eye steak, maldon salt, red wine reduction', 1, 'Grain-finished rib-eye with Malbec reduction.'],
        ]],
        ['Entraña', 'main', 'steak', [
            'mid' => [22, 'beef, sea salt, chimichurri', 1, 'Skirt steak, juicy and full of flavour.'],
        ]],
        ['Asado de Tira', 'main', 'meat', [
            'mid' => [24, 'beef short ribs, sea salt, chimichurri', 1, 'Cross-cut beef short ribs from the grill.'],
        ]],
        ['Parrillada for Two', 'main', 'meat', [
            'mid' => [56, 'beef, beef short ribs, chorizo, morcilla, chicken, chimichurri', 4, 'Mixed grill of steak, ribs, chorizo, morcilla and chicken.'],
        ], ['serves' => 2]],
        ['Empanadas Criollas · 3 pieces', 'starter', 'pastry', [
            'mid' => [9, 'wheat flour, beef empanada filling, onion, olives, egg', 2, 'Baked beef empanadas with olives and egg.'],
        ]],
        ['Provoleta', 'starter', 'mezze', [
            'mid' => [9, 'provola, oregano, chilli flakes, extra virgin olive oil', 1, 'Grilled provolone with oregano.'],
        ]],
        ['Choripán', 'sandwich', 'sandwich', [
            'mid' => [9, 'chorizo, country bread, chimichurri', 2, 'Grilled chorizo in crusty bread with chimichurri.'],
        ]],
        ['Grilled Provençal Potatoes', 'side', 'side', [
            'mid' => [6, 'potato, garlic, parsley', 1, 'Fries with garlic and parsley.'],
        ]],
        ['Mixed Salad', 'side', 'salad', [
            'mid' => [5, 'lettuce, tomato, onion', 1, 'Lettuce, tomato and onion.'],
        ]],
        ['Panqueques con Dulce de Leche', 'dessert', 'dessert', [
            'mid' => [7, 'wheat flour, egg, milk, dulce de leche', 2, 'Crêpes filled with dulce de leche.'],
        ]],
        ['Flan Mixto', 'dessert', 'dessert', [
            'mid' => [6.5, 'egg, milk, sugar, dulce de leche, whipped cream', 2, 'Caramel flan with dulce de leche and cream.'],
        ]],
    ],
];
