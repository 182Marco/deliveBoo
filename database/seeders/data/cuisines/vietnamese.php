<?php

// Vietnamese restaurants.
return [
    'types' => ['Vietnamese'],
    'concepts' => [
        ['Phở Hà Nội', 'ethnic', 'Northern Vietnamese phở with a beef broth simmered overnight, star anise and charred ginger.'],
        ['Bánh Mì Corner', 'budget', 'Vietnamese sandwich shop: crusty bánh mì, fresh rolls and iced coffee.'],
    ],
    'recipes' => [
        ['Phở Bò', 'soup', 'vietnamese', [
            'base' => [11, 'rice noodles, pho broth, beef, onion, coriander, bean sprouts, lime', 3, 'Beef phở with rare slices, herbs and bean sprouts.'],
            'mid' => [14, 'rice noodles, pho broth, beef fillet, beef short ribs, thai basil, bean sprouts, lime', 3, 'Phở with rare fillet and braised short rib.'],
        ]],
        ['Phở Gà', 'soup', 'vietnamese', [
            'base' => [10, 'rice noodles, chicken stock, chicken, ginger, spring onion, coriander', 3, 'Chicken phở with ginger and spring onion.'],
        ]],
        ['Bún Chả', 'main', 'vietnamese', [
            'mid' => [13, 'rice noodles, pork, nuoc cham, lettuce, mint, coriander', 2, 'Grilled pork patties and belly with cold noodles and herbs.'],
        ]],
        ['Bánh Mì Thịt', 'sandwich', 'sandwich', [
            'base' => [[7.5, 'country bread, pork belly, chicken liver, pickles, carrot, coriander, chilli pepper', 2, 'Baguette with roast pork, pâté, pickled carrot and coriander.'],
                       [7, 'country bread, tofu, pickles, carrot, coriander, vegan mayonnaise', 2, 'Vegan bánh mì with lemongrass tofu.']],
        ]],
        ['Fresh Rolls · 2 pieces', 'starter', 'vietnamese', [
            'base' => [6, 'rice paper, prawns, rice noodles, lettuce, mint, peanut sauce', 2, 'Rice paper rolls with prawns, herbs and peanut dip.'],
        ]],
        ['Crispy Imperial Rolls', 'starter', 'vietnamese', [
            'mid' => [7, 'rice paper, pork, glass noodles, carrot, nuoc cham', 2, 'Fried rice paper rolls with pork and glass noodles.'],
        ]],
        ['Bò Lúc Lắc', 'main', 'steak', [
            'mid' => [18, 'beef fillet, onion, bell pepper, watercress, soy sauce, garlic', 1, 'Shaking beef: cubed fillet seared with peppers, on watercress.'],
        ]],
        ['Lemongrass Chicken Rice Bowl', 'main', 'asian_bowl', [
            'base' => [11, 'chicken thighs, lemongrass, jasmine rice, cucumber, carrot, nuoc cham', 2, 'Grilled lemongrass chicken on rice with pickles.'],
        ]],
        ['Vietnamese Iced Coffee', 'hot_drink', 'coffee', [
            'base' => [3.5, 'coffee, milk, ice', 1, 'Strong drip coffee with condensed milk over ice.'],
        ]],
        ['Chè Ba Màu', 'dessert', 'dessert', [
            'mid' => [5, 'mung beans, red bean paste, coconut milk, agar agar', 2, 'Three-colour dessert of beans, jelly and coconut milk.'],
        ]],
    ],
];
