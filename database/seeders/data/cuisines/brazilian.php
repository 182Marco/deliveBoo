<?php

// Brazilian churrascarias and botecos.
return [
    'types' => ['Brazilian'],
    'extra_types' => ['traditional' => ['Steakhouse']],
    'concepts' => [
        ['Churrascaria Gaúcha', 'traditional', 'Brazilian rodízio-style churrasco boxed for delivery: picanha, linguiça and grilled pineapple.'],
        ['Boteco Rio', 'ethnic', 'Rio bar food: coxinhas, pão de queijo, feijoada on Saturdays and caipirinhas.'],
    ],
    'recipes' => [
        ['Picanha', 'main', 'steak', [
            'mid' => [24, 'picanha, sea salt, cassava flour, tomato, onion', 1, 'Rump cap grilled on skewers, with farofa and vinaigrette.'],
        ]],
        ['Churrasco Mix for Two', 'main', 'roast', [
            'mid' => [48, 'picanha, linguiça sausage, chicken thighs, pork ribs, pineapple, cassava flour', 4, 'Picanha, sausage, chicken, ribs and cinnamon pineapple, for two.'],
        ], ['serves' => 2]],
        ['Feijoada', 'main', 'stew', [
            'mid' => [18, 'black beans, carne seca, pork ribs, linguiça sausage, basmati rice, orange', 3, 'Black bean stew with dried beef, pork and sausage, rice, greens and orange.'],
        ]],
        ['Moqueca de Peixe', 'main', 'curry', [
            'mid' => [19, 'cod, coconut milk, tomato, red pepper, coriander, lime', 2, 'Bahian fish stew with coconut milk and dendê.'],
        ]],
        ['Coxinha · 4 pieces', 'starter', 'fries', [
            'base' => [6, 'wheat flour, chicken, cream cheese, breadcrumbs', 2, 'Teardrop croquettes filled with shredded chicken.'],
        ]],
        ['Pão de Queijo · 6 pieces', 'starter', 'bread', [
            'base' => [5, 'cassava flour, parmigiano reggiano, egg, milk', 2, 'Chewy cheese bread rolls, naturally gluten free.'],
        ]],
        ['Pastel de Carne', 'starter', 'pastry', [
            'base' => [5, 'wheat flour, ground beef, onion, olives, sunflower oil', 2, 'Crisp fried pastry with spiced beef.'],
        ]],
        ['Farofa', 'side', 'side', [
            'mid' => [4, 'cassava flour, butter, bacon, onion', 1, 'Toasted cassava flour with bacon.'],
        ]],
        ['Brigadeiros · 4 pieces', 'dessert', 'chocolate', [
            'base' => [4, 'milk, cocoa powder, butter, sprinkles', 2, 'Chocolate fudge truffles.'],
        ]],
        ['Açaí Bowl', 'dessert', 'smoothie', [
            'base' => [8, 'acai, banana, granola, strawberries, honey', 2, 'Frozen açaí with banana, granola and strawberries.'],
        ]],
    ],
];
