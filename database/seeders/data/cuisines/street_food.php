<?php

// Italian street food: piadineria, friggitoria, sandwiches.
return [
    'types' => ['Street Food'],
    'extra_types' => ['street_food' => ['Italian']],
    'concepts' => [
        ['Piadineria Romagna Mia', 'street_food', 'Piadine rolled out and cooked on the terracotta testo, Romagna style.'],
        ['Friggitoria \'O Cuoppo', 'street_food', 'Neapolitan fried street food served in paper cones: arancini, crocchè and zeppole.'],
        ['Panino Mondo', 'street_food', 'Italian sandwich bar: porchetta, lampredotto and mortadella panini in crusty bread.'],
        ['Arancineria Etna', 'street_food', 'Sicilian arancine in twenty flavours, sweet and savoury.'],
    ],
    'recipes' => [
        ['Piadina Prosciutto, Squacquerone and Rocket', 'sandwich', 'sandwich', [
            'base' => [7, 'piadina, prosciutto crudo, cream cheese, rocket', 3, 'The Romagna classic: raw ham, soft squacquerone and rocket.'],
        ]],
        ['Piadina Sausage and Onion', 'sandwich', 'sandwich', [
            'base' => [7.5, 'piadina, italian sausage, onion, cheddar', 2, 'Grilled sausage, caramelised onion and melted cheese.'],
        ]],
        ['Piadina Vegetariana', 'sandwich', 'sandwich', [
            'base' => [6.5, 'piadina, zucchini, aubergine, mozzarella fior di latte, tomato', 3, 'Grilled vegetables and mozzarella.'],
        ]],
        ['Cassone with Greens', 'sandwich', 'sandwich', [
            'base' => [6, 'piadina, swiss chard, garlic, extra virgin olive oil', 2, 'Folded and sealed piadina filled with sautéed greens, vegan.'],
        ]],
        ['Cuoppo Fritto', 'starter', 'fries', [
            'base' => [7, 'potato, arborio rice, mozzarella fior di latte, pizza dough, wheat flour', 3, 'Paper cone of crocchè, rice balls, mozzarella bites and zeppoline.'],
        ]],
        ['Arancina al Ragù', 'starter', 'antipasti', [
            'base' => [3.5, 'arborio rice, ragù bolognese, peas, caciocavallo, saffron, breadcrumbs', 2, 'Saffron rice ball with ragù and peas.'],
        ]],
        ['Arancina Burro e Prosciutto', 'starter', 'antipasti', [
            'base' => [3.5, 'arborio rice, prosciutto cotto, mozzarella fior di latte, béchamel, breadcrumbs', 2, 'Round arancina with ham, mozzarella and béchamel.'],
        ]],
        ['Arancina Pistacchio', 'starter', 'antipasti', [
            'base' => [4, 'arborio rice, pistachio pesto, mortadella, breadcrumbs', 2, 'Arancina with pistachio cream and mortadella.'],
        ]],
        ['Porchetta Sandwich', 'sandwich', 'sandwich', [
            'base' => [7, 'country bread, pork belly, rosemary, black pepper', 1, 'Crusty roll stuffed with sliced porchetta and its crackling.'],
        ]],
        ['Lampredotto Sandwich', 'sandwich', 'sandwich', [
            'base' => [6, 'country bread, tripe, salsa verde, chilli oil', 1, 'Florentine tripe sandwich with green sauce, bread dipped in broth.'],
        ]],
        ['Mortadella and Pistachio Panino', 'sandwich', 'sandwich', [
            'base' => [7, 'focaccia, mortadella, burrata, pistachio pesto', 2, 'Focaccia with mortadella, stracciatella and pistachio pesto.'],
        ]],
        ['Focaccia Genovese', 'starter', 'bread', [
            'base' => [3, 'focaccia, extra virgin olive oil, sea salt', 1, 'A square of oily, salty Ligurian focaccia.'],
        ]],
        ['Cannolo Siciliano', 'dessert', 'pastry', [
            'base' => [3.5, 'cannoli shells, sheep ricotta, sugar, candied orange peel', 2, 'Crisp shell filled to order with sheep ricotta.'],
        ]],
        ['Zeppole with Sugar', 'dessert', 'pastry', [
            'base' => [4, 'pizza dough, sugar, sunflower oil', 1, 'Fried dough balls rolled in sugar.'],
        ]],
    ],
];
