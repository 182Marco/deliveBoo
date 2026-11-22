<?php

// Peruvian and Nikkei restaurants.
return [
    'types' => ['Peruvian'],
    'extra_types' => ['premium' => ['Fusion']],
    'concepts' => [
        ['Cevichería Lima', 'ethnic', 'Peruvian cevichería with tiger\'s milk made to order, causas and lomo saltado.'],
        ['Nikkei Andes', 'premium', 'Peruvian-Japanese Nikkei cuisine: tiraditos, maki with Andean chillies and pisco cocktails.', [
            ['Tiradito Nikkei', 'starter', 'sashimi', 22, 'yellowtail, lime, soy sauce, chilli pepper, sesame oil', 2, 'Yellowtail sliced sashimi-style in a soy and ají amarillo tiger\'s milk.'],
        ]],
    ],
    'recipes' => [
        ['Ceviche Clásico', 'starter', 'ceviche', [
            'mid' => [16, 'sea bass, lime, red onion, chilli pepper, coriander, sweet potato, sweetcorn', 2, 'Sea bass cured in lime with red onion, ají and sweet potato.'],
            'high' => [24, 'sea bass, lime, red onion, chilli pepper, coriander, sweet potato', 2, 'Line-caught sea bass, tiger\'s milk with ají limo.'],
        ]],
        ['Ceviche Mixto', 'starter', 'ceviche', [
            'mid' => [18, 'sea bass, prawns, octopus, squid, lime, red onion, chilli pepper', 3, 'Fish and seafood ceviche.'],
        ]],
        ['Causa Limeña', 'starter', 'antipasti', [
            'mid' => [11, 'potato, chicken, mayonnaise, avocado, lime, chilli pepper', 2, 'Layered yellow potato terrine with chicken and avocado.'],
        ]],
        ['Lomo Saltado', 'main', 'steak', [
            'mid' => [18, 'beef fillet, onion, tomato, soy sauce, potato, basmati rice', 2, 'Beef stir-fried with onion and tomato, served with fries and rice.'],
        ]],
        ['Ají de Gallina', 'main', 'curry', [
            'mid' => [15, 'chicken, chilli pepper, milk, walnuts, parmigiano reggiano, basmati rice', 2, 'Creamy chicken in yellow chilli sauce with walnuts.'],
        ]],
        ['Anticuchos', 'starter', 'skewers', [
            'mid' => [12, 'beef, chilli pepper, garlic, cumin, potato', 1, 'Marinated beef heart skewers, grilled.'],
        ]],
        ['Arroz Chaufa', 'main', 'asian_bowl', [
            'mid' => [14, 'jasmine rice, chicken, egg, spring onion, soy sauce, ginger', 2, 'Peruvian-Chinese fried rice.'],
        ]],
        ['Suspiro Limeño', 'dessert', 'dessert', [
            'mid' => [6.5, 'dulce de leche, meringue, cinnamon', 2, 'Dulce de leche custard with port meringue.'],
        ]],
    ],
];
