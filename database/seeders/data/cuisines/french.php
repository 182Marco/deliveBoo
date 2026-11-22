<?php

// French bistros and haute cuisine.
return [
    'types' => ['French'],
    'extra_types' => ['starred' => ['Starred Restaurant', 'Fine Dining'], 'fine_dining' => ['Fine Dining']],
    'concepts' => [
        ['Bistrot du Marché', 'traditional', 'Parisian bistro: steak frites, duck confit, onion soup and a blackboard of daily dishes.'],
        ['Le Petit Zinc', 'traditional', 'Wine bistro with charcuterie, cheese boards and Burgundy by the glass.'],
        ['Maison Lumière', 'starred', 'French haute cuisine with a garden-to-table philosophy: classic sauces, precise cooking and a cellar of Burgundy and Champagne.', [
            ['Lobster Thermidor, Our Way', 'main', 'seafood', 72, 'blue lobster, cream, dijon mustard, cognac, gruyère', 2, 'Blue lobster glazed with a light mustard and cognac sabayon.'],
            ['Grand Menu Lumière · 10 courses', 'tasting_menu', 'finedining', 210, 'foie gras, langoustines, turbot, pigeon, black truffle, comté, dark chocolate', 4, 'Ten courses with the best of the season. Price per person.'],
        ]],
    ],
    'recipes' => [
        ['French Onion Soup', 'soup', 'soup', [
            'mid' => [10, 'onion, beef broth, country bread, gruyère, butter, white wine', 3, 'Slow-caramelised onions in beef broth, gratinated with Gruyère.'],
        ], ['season' => [10, 4]]],
        ['Escargots de Bourgogne · 6 pieces', 'starter', 'french', [
            'mid' => [12, 'snails, garlic butter, parsley, country bread', 1, 'Burgundy snails baked in garlic and parsley butter.'],
            'high' => [18, 'snails, garlic butter, parsley, black garlic', 1, 'Snails with a black garlic and parsley butter.'],
        ]],
        ['Duck Liver Pâté', 'starter', 'antipasti', [
            'mid' => [11, 'chicken liver, butter, cognac, country bread, pickles', 2, 'Smooth liver pâté with cornichons and toast.'],
        ]],
        ['Steak Frites', 'main', 'steak', [
            'mid' => [24, 'beef sirloin, potato, butter, shallot, red wine reduction', 2, 'Grilled entrecôte with fries and a shallot butter.'],
        ]],
        ['Duck Confit', 'main', 'roast', [
            'mid' => [22, 'duck leg, potato, garlic, thyme', 1, 'Duck leg confit with garlic sautéed potatoes.'],
        ]],
        ['Boeuf Bourguignon', 'main', 'stew', [
            'mid' => [21, 'beef, red wine, mushrooms, bacon, onion, carrot', 2, 'Beef braised in Burgundy wine with mushrooms and lardons.'],
        ], ['season' => [10, 4]]],
        ['Moules Marinières', 'main', 'seafood', [
            'mid' => [17, 'mussels, white wine, shallot, cream, parsley, potato', 1, 'Mussels in white wine and cream with frites.'],
        ]],
        ['Quiche Lorraine', 'main', 'french', [
            'mid' => [12, 'shortcrust pastry, egg, cream, bacon, gruyère', 2, 'Deep quiche with smoked bacon and Gruyère, green salad.'],
        ]],
        ['Croque Monsieur', 'sandwich', 'sandwich', [
            'mid' => [11, 'country bread, prosciutto cotto, gruyère, béchamel', 2, 'Toasted ham and cheese sandwich gratinated with béchamel.'],
        ]],
        ['Salade Niçoise', 'salad', 'salad', [
            'mid' => [14, 'tuna, green beans, egg, potato, olives, anchovies, tomato', 2, 'Tuna, beans, eggs, olives and anchovies.'],
        ]],
        ['Sole Meunière', 'main', 'fish', [
            'high' => [52, 'turbot, butter, lemon, parsley', 1, 'Whole flatfish cooked in brown butter, filleted at the table.'],
        ]],
        ['Foie Gras Poêlé', 'starter', 'finedining', [
            'high' => [36, 'foie gras, apple, calvados, brioche dough', 1, 'Seared foie gras with caramelised apple and calvados jus.'],
        ]],
        ['Langoustines, Beurre Blanc', 'starter', 'seafood', [
            'high' => [38, 'langoustines, beurre blanc, chives', 1, 'Roasted langoustines with beurre blanc and chives.'],
        ]],
        ['Pigeon en Croûte', 'main', 'finedining', [
            'high' => [56, 'pigeon, foie gras, puff pastry, mushrooms, demi-glace', 2, 'Pigeon and foie gras in pastry with a rich jus.'],
        ]],
        ['Beef Wellington · for two', 'main', 'steak', [
            'high' => [110, 'beef fillet, mushrooms, prosciutto crudo, puff pastry, demi-glace', 2, 'Beef fillet wrapped in mushroom duxelles and pastry, for two.'],
        ], ['serves' => 2]],
        ['Comté 24 Months and Walnut Bread', 'side', 'antipasti', [
            'mid' => [12, 'comté, walnuts, country bread', 1, 'Aged Comté with walnut bread.'],
        ]],
        ['Crème Brûlée', 'dessert', 'dessert', [
            'mid' => [7, 'cream, egg yolk, sugar, vanilla', 2, 'Vanilla custard with a caramelised top.'],
        ]],
        ['Tarte Tatin', 'dessert', 'dessert', [
            'mid' => [8, 'apple, puff pastry, butter, sugar, crème fraîche', 2, 'Upside-down caramelised apple tart with crème fraîche.'],
        ]],
        ['Profiteroles', 'dessert', 'pastry', [
            'mid' => [8, 'wheat flour, egg, butter, vanilla gelato, dark chocolate', 3, 'Choux filled with vanilla ice cream, hot chocolate sauce.'],
        ]],
        ['Grand Marnier Soufflé', 'dessert', 'dessert', [
            'high' => [19, 'egg, sugar, orange, sweet liqueur, butter', 2, 'Orange liqueur soufflé baked to order.'],
        ]],
        ['Cheese Cart', 'side', 'antipasti', [
            'high' => [26, 'brie, comté, goat cheese, gruyère, walnuts', 3, 'Seven French cheeses from an affineur.'],
        ]],
    ],
];
