<?php

// Spanish tapas bars and arrocerías.
return [
    'types' => ['Spanish', 'Mediterranean'],
    'concepts' => [
        ['Tapería La Bodeguita', 'ethnic', 'Tapas bar with jamón carved by hand, croquetas and vermouth on tap.'],
        ['Arrocería Valencia', 'traditional', 'Paella cooked over orange wood in wide pans, for two or more, as they do in Valencia.'],
    ],
    'recipes' => [
        ['Patatas Bravas', 'starter', 'side', [
            'mid' => [6.5, 'potato, salsa brava, aioli, smoked paprika', 1, 'Fried potatoes with spicy brava sauce and aioli.'],
        ]],
        ['Croquetas de Jamón · 4 pieces', 'starter', 'antipasti', [
            'mid' => [7, 'jamón ibérico, béchamel, breadcrumbs, egg', 2, 'Creamy béchamel croquettes with Ibérico ham.'],
        ]],
        ['Jamón Ibérico de Bellota · 80g', 'starter', 'antipasti', [
            'mid' => [22, 'jamón ibérico, country bread, tomato', 1, 'Acorn-fed Ibérico ham carved by hand, with pan con tomate.'],
        ]],
        ['Pan con Tomate', 'starter', 'bruschetta', [
            'mid' => [4, 'country bread, tomato, garlic, extra virgin olive oil', 2, 'Toasted bread rubbed with ripe tomato and garlic.'],
        ]],
        ['Gambas al Ajillo', 'starter', 'seafood', [
            'mid' => [12, 'prawns, garlic, chilli pepper, extra virgin olive oil, parsley', 1, 'Prawns sizzling in garlic and chilli oil.'],
        ]],
        ['Pimientos de Padrón', 'starter', 'vegetables', [
            'mid' => [6, 'padron peppers, sea salt, extra virgin olive oil', 1, 'Blistered Padrón peppers with flaky salt: some are hot.'],
        ], ['season' => [6, 10]]],
        ['Tortilla Española', 'starter', 'brunch', [
            'mid' => [7, 'potato, egg, onion, extra virgin olive oil', 2, 'Thick potato and onion omelette, runny at the centre.'],
        ]],
        ['Pulpo a la Gallega', 'starter', 'seafood', [
            'mid' => [16, 'octopus, potato, smoked paprika, sea salt, extra virgin olive oil', 1, 'Galician octopus on potatoes with paprika and olive oil.'],
        ]],
        ['Chorizo al Vino', 'starter', 'meat', [
            'mid' => [8, 'chorizo, red wine, bay leaf', 1, 'Chorizo simmered in red wine.'],
        ]],
        ['Gazpacho', 'soup', 'soup', [
            'mid' => [7, 'tomato, cucumber, bell pepper, garlic, red wine vinegar, extra virgin olive oil', 2, 'Chilled Andalusian tomato soup.'],
        ], ['season' => [6, 9]]],
        ['Paella Valenciana · for two', 'main', 'paella', [
            'mid' => [36, 'bomba rice, chicken, rabbit, green beans, saffron, tomato', 3, 'The original: chicken, rabbit, beans and saffron, with socarrat.'],
        ], ['serves' => 2]],
        ['Paella de Marisco · for two', 'main', 'paella', [
            'mid' => [42, 'bomba rice, prawns, mussels, squid, clams, saffron, fish stock', 3, 'Seafood paella with prawns, mussels, squid and clams.'],
        ], ['serves' => 2]],
        ['Arroz Negro · for two', 'main', 'paella', [
            'mid' => [38, 'bomba rice, cuttlefish, squid ink, aioli, fish stock', 3, 'Black rice with cuttlefish and ink, served with aioli.'],
        ], ['serves' => 2]],
        ['Vegetable Paella · for two', 'main', 'paella', [
            'mid' => [32, 'bomba rice, artichoke, green beans, red pepper, saffron, vegetable stock', 3, 'Seasonal vegetables and saffron, vegan.'],
        ], ['serves' => 2]],
        ['Crema Catalana', 'dessert', 'dessert', [
            'mid' => [6, 'milk, egg yolk, sugar, lemon, cinnamon', 2, 'Custard with a burnt sugar crust, lemon and cinnamon.'],
        ]],
        ['Churros con Chocolate', 'dessert', 'dessert', [
            'mid' => [6, 'churros dough, sugar, dark chocolate, milk', 1, 'Madrid churros with thick hot chocolate.'],
        ]],
    ],
];
