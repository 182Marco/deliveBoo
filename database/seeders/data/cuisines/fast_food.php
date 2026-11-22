<?php

// Budget fast food: burgers, fried chicken, kebab and hot dogs.
return [
    'types' => ['Fast Food'],
    'extra_types' => ['fast_food' => ['Hamburger']],
    'concepts' => [
        ['Burger Station', 'fast_food', 'Fast burgers made to order, combo menus and late-night opening.'],
        ['Crispy Bird', 'fast_food', 'Southern fried chicken: buckets, tenders and spicy wings, with honest prices.'],
        ['Grill & Go', 'fast_food', 'Quick grill for lunch breaks: burgers, wraps and salads in under ten minutes.'],
        ['Hot Dog Factory', 'fast_food', 'Hot dogs and sausages with toppings from around the world.'],
    ],
    'recipes' => [
        ['Hamburger', 'burger', 'burger', [
            'base' => [5.5, 'burger bun, beef patty, ketchup, mustard, pickles, onion', 2, 'Grilled beef patty with ketchup, mustard, pickles and onion.'],
        ]],
        ['Cheeseburger', 'burger', 'burger', [
            'base' => [6.5, 'burger bun, beef patty, cheddar, ketchup, pickles, onion', 2, 'Beef patty and melted cheddar.'],
        ]],
        ['Double Bacon Cheeseburger', 'burger', 'burger', [
            'base' => [9.5, 'burger bun, beef patty, bacon, cheddar, lettuce, burger sauce', 3, 'Two patties, two slices of cheddar and crispy bacon.'],
        ]],
        ['Chicken Burger', 'burger', 'burger', [
            'base' => [7, 'burger bun, chicken breast, wheat flour, lettuce, mayonnaise', 2, 'Crispy chicken fillet with lettuce and mayo.'],
        ]],
        ['Veggie Burger', 'burger', 'burger', [
            'base' => [7.5, 'vegan bun, plant-based patty, lettuce, tomato, ketchup', 2, 'Plant-based patty, lettuce and tomato.'],
        ]],
        ['Burger Combo Menu', 'burger', 'burger', [
            'base' => [11, 'burger bun, beef patty, cheddar, potato, sunflower oil, cola', 2, 'Cheeseburger, medium fries and a soft drink.'],
        ]],
        ['Fried Chicken Bucket · 8 pieces', 'main', 'chicken', [
            'base' => [[16, 'chicken, wheat flour, paprika, garlic, sunflower oil', 1, 'Eight pieces of crunchy fried chicken, for two or three.'],
                       [18, 'chicken, wheat flour, chilli pepper, paprika, sunflower oil', 1, 'Spicy bucket with cayenne-dusted chicken.']],
        ], ['serves' => 3]],
        ['Chicken Tenders · 5 pieces', 'main', 'chicken', [
            'base' => [7.5, 'chicken breast, breadcrumbs, egg, honey mustard', 1, 'Breaded chicken strips with honey mustard dip.'],
        ]],
        ['Hot Wings · 10 pieces', 'starter', 'chicken', [
            'base' => [8, 'chicken wings, chilli pepper, paprika, ranch dressing', 1, 'Spicy wings with a ranch dip.'],
        ]],
        ['Chicken Wrap', 'sandwich', 'wrap', [
            'base' => [7, 'flour tortilla, chicken breast, lettuce, tomato, mayonnaise', 2, 'Grilled chicken wrap with lettuce and tomato.'],
        ]],
        ['Classic Hot Dog', 'sandwich', 'streetfood', [
            'base' => [5, 'hot dog sausage, burger bun, ketchup, mustard, onion', 2, 'Grilled frankfurter with ketchup, mustard and crispy onions.'],
        ]],
        ['Bratwurst with Sauerkraut', 'sandwich', 'streetfood', [
            'base' => [6.5, 'italian sausage, burger bun, cabbage, mustard', 2, 'Grilled bratwurst with sauerkraut and mustard.'],
        ]],
        ['Fries', 'side', 'fries', [
            'base' => [3, 'potato, sunflower oil, sea salt', 1, 'Golden fries.'],
        ]],
        ['Cheese Fries', 'side', 'fries', [
            'base' => [4.5, 'potato, cheese sauce, bacon, sunflower oil', 2, 'Fries with cheese sauce and bacon bits.'],
        ]],
        ['Mozzarella Sticks · 6 pieces', 'starter', 'fries', [
            'base' => [5, 'mozzarella fior di latte, breadcrumbs, egg, tomato salsa', 1, 'Breaded mozzarella sticks with tomato dip.'],
        ]],
        ['Side Salad', 'side', 'salad', [
            'base' => [3.5, 'lettuce, tomato, carrot, sweetcorn', 1, 'Lettuce, tomato, carrot and sweetcorn.'],
        ]],
        ['Soft Serve Cone', 'dessert', 'gelato', [
            'base' => [2, 'ice cream base, waffle cone', 1, 'Vanilla soft serve in a cone.'],
        ]],
        ['Chocolate Sundae', 'dessert', 'gelato', [
            'base' => [3.5, 'ice cream base, dark chocolate, peanuts', 1, 'Soft serve with hot chocolate sauce and peanuts.'],
        ]],
        ['Apple Pie', 'dessert', 'pastry', [
            'base' => [2.5, 'shortcrust pastry, apple, cinnamon, sugar', 2, 'Hot fried apple turnover with cinnamon.'],
        ]],
    ],
];
