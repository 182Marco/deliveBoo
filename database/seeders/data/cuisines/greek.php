<?php

// Greek tavernas and souvlaki bars.
return [
    'types' => ['Greek', 'Mediterranean'],
    'concepts' => [
        ['Taverna Mykonos', 'ethnic', 'Greek taverna with grilled octopus, moussaka from the clay oven and a short list of island wines.'],
        ['Souvlaki Bros', 'budget', 'Souvlaki and gyros wrapped in fluffy pita, Athens street style.'],
    ],
    'recipes' => [
        ['Gyros Pita', 'sandwich', 'wrap', [
            'base' => [[7, 'pita bread, pork shoulder, tzatziki, tomato, onion, potato', 2, 'Pork gyros in pita with tzatziki, tomato, onion and fries.'],
                       [7, 'pita bread, chicken thighs, tzatziki, tomato, onion, potato', 2, 'Chicken gyros, Athens style.']],
        ]],
        ['Souvlaki Plate', 'main', 'skewers', [
            'base' => [11, 'pork, lemon, oregano, pita bread, tzatziki, potato', 2, 'Two pork skewers with pita, tzatziki and fries.'],
            'mid' => [15, 'lamb, lemon, oregano, pita bread, tzatziki, tomato, cucumber', 2, 'Lamb souvlaki from the grill with salad.'],
        ]],
        ['Greek Salad', 'salad', 'salad', [
            'base' => [8, 'tomato, cucumber, feta, kalamata olives, red onion, oregano', 3, 'Horiatiki: tomato, cucumber, olives and a slab of feta.'],
            'mid' => [10, 'tomato, cucumber, feta, kalamata olives, capers, red onion, bell pepper', 3, 'Village salad with barrel-aged feta and capers.'],
        ], ['season' => [5, 10]]],
        ['Moussaka', 'main', 'lasagna', [
            'mid' => [15, 'aubergine, ground beef, potato, béchamel, tomato, cinnamon', 3, 'Layers of aubergine, spiced beef and béchamel.'],
        ]],
        ['Grilled Octopus', 'starter', 'seafood', [
            'mid' => [17, 'octopus, lemon, oregano, extra virgin olive oil, fava beans', 1, 'Charred octopus on yellow split pea fava.'],
        ]],
        ['Spanakopita', 'starter', 'pastry', [
            'mid' => [7, 'filo pastry, spinach, feta, dill, egg', 2, 'Spinach and feta filo pie.'],
        ]],
        ['Saganaki', 'starter', 'mezze', [
            'mid' => [8, 'halloumi, wheat flour, lemon', 1, 'Fried cheese with lemon.'],
        ]],
        ['Tzatziki with Pita', 'starter', 'mezze', [
            'base' => [5, 'tzatziki, cucumber, pita bread', 1, 'Yogurt, cucumber and garlic dip with warm pita.'],
        ]],
        ['Lamb Kleftiko', 'main', 'roast', [
            'mid' => [19, 'lamb shoulder, potato, garlic, lemon, oregano', 1, 'Lamb slow-baked in parchment with potatoes and lemon.'],
        ]],
        ['Gemista', 'main', 'vegetables', [
            'mid' => [12, 'tomato, bell pepper, arborio rice, mint, parsley', 2, 'Tomatoes and peppers stuffed with herb rice, vegan.'],
        ], ['season' => [6, 9]]],
        ['Greek Yogurt with Honey and Walnuts', 'dessert', 'dessert', [
            'base' => [5, 'greek yogurt, honey, walnuts', 1, 'Thick strained yogurt with thyme honey and walnuts.'],
        ]],
        ['Galaktoboureko', 'dessert', 'pastry', [
            'mid' => [6, 'filo pastry, semolina, milk, egg, sugar', 2, 'Semolina custard pie in crisp filo with syrup.'],
        ]],
    ],
];
