<?php

// American burger restaurants, smokehouses and diners.
return [
    'types' => ['American', 'Hamburger'],
    'extra_types' => ['traditional' => ['Steakhouse']],
    'concepts' => [
        ['Smokestack BBQ', 'traditional', 'Texas-style smokehouse: brisket smoked fourteen hours over oak, ribs and pulled pork by weight.'],
        ['Route 66 Diner', 'budget', 'American diner with milkshakes, chilli dogs and all-day pancakes.'],
        ['Bun & Butcher', 'ethnic', 'Gourmet burger joint grinding dry-aged beef in house, with potato buns from a local bakery.'],
        ['Brooklyn Burger Lab', 'ethnic', 'Smash burgers, crisp fries and craft beer on tap.'],
    ],
    'recipes' => [
        ['Classic Cheeseburger', 'burger', 'burger', [
            'base' => [10, 'burger bun, beef patty, cheddar, lettuce, tomato, pickles, burger sauce', 3, 'Beef patty, cheddar, lettuce, tomato, pickles and house sauce.'],
            'mid' => [14, 'potato bun, beef patty, cheddar, lettuce, tomato, pickles, red onion, burger sauce', 3, 'Dry-aged chuck patty with aged cheddar on a toasted potato bun.'],
        ]],
        ['Double Smash Burger', 'burger', 'burger', [
            'base' => [12, 'burger bun, beef patty, cheddar, onion, pickles, mustard', 3, 'Two thin smashed patties with caramelised edges and melted cheddar.'],
            'mid' => [15, 'potato bun, beef patty, cheddar, onion, pickles, burger sauce', 3, 'Double smash with grilled onions and our special sauce.'],
        ]],
        ['Bacon BBQ Burger', 'burger', 'burger', [
            'mid' => [16, 'brioche bun, beef patty, bacon, cheddar, onion, bbq sauce', 3, 'Crispy bacon, cheddar, onion rings and smoky barbecue sauce.'],
        ]],
        ['Blue Cheese Burger', 'burger', 'burger', [
            'mid' => [16, 'brioche bun, beef patty, gorgonzola, rocket, red onion, walnuts', 3, 'Gorgonzola, rocket, caramelised red onion and walnuts.'],
        ]],
        ['Crispy Chicken Burger', 'burger', 'burger', [
            'base' => [11, 'burger bun, chicken breast, buttermilk, lettuce, mayonnaise, pickles', 2, 'Buttermilk fried chicken with lettuce and mayo.'],
        ]],
        ['Plant-Based Burger', 'burger', 'burger', [
            'base' => [12, 'vegan bun, plant-based patty, lettuce, tomato, red onion, vegan mayonnaise', 2, 'Plant-based patty with vegan mayo, fully vegan.'],
            'mid' => [15, 'vegan bun, pea protein patty, vegan cheese, lettuce, tomato, pickles, ketchup', 2, 'Pea protein patty with melted vegan cheese.'],
        ]],
        ['Gluten-Free Burger', 'burger', 'burger', [
            'mid' => [15, 'gluten-free bun, beef patty, cheddar, lettuce, tomato', 2, 'Our classic on a gluten-free bun, cooked on a separate grill.'],
        ]],
        ['Texas Brisket Plate', 'main', 'meat', [
            'mid' => [22, 'beef, black pepper, sea salt, pickles, country bread, bbq sauce', 1, 'Sliced oak-smoked brisket with pickles and white bread.'],
        ]],
        ['Pork Ribs Half Rack', 'main', 'meat', [
            'mid' => [20, 'pork ribs, bbq sauce, paprika, brown sugar', 1, 'Dry-rubbed ribs smoked six hours, glazed with sauce.'],
        ]],
        ['Pulled Pork Sandwich', 'sandwich', 'sandwich', [
            'mid' => [13, 'brioche bun, pork shoulder, bbq sauce, cabbage, carrot, mayonnaise', 2, 'Smoked pulled pork with coleslaw in a brioche bun.'],
        ]],
        ['Buffalo Wings · 8 pieces', 'starter', 'chicken', [
            'base' => [9, 'chicken wings, tabasco, butter, celery, blue cheese dressing', 2, 'Wings tossed in hot sauce and butter, celery and blue cheese dip.'],
        ]],
        ['Mac and Cheese', 'side', 'pasta', [
            'base' => [7, 'penne, cheddar, milk, butter, breadcrumbs', 2, 'Baked macaroni with a cheddar sauce and crunchy top.'],
        ]],
        ['Chilli Dog', 'sandwich', 'streetfood', [
            'base' => [8, 'hot dog sausage, burger bun, ground beef, kidney beans, cheddar, onion', 2, 'Beef frank topped with chilli con carne and cheddar.'],
        ]],
        ['Fries', 'side', 'fries', [
            'base' => [4, 'potato, sunflower oil, sea salt', 1, 'Skin-on fries.'],
        ]],
        ['Sweet Potato Fries', 'side', 'fries', [
            'base' => [5, 'sweet potato, sunflower oil, sea salt', 1, 'Crispy sweet potato fries.'],
        ]],
        ['Onion Rings', 'side', 'fries', [
            'base' => [5, 'onion, wheat flour, lager beer, sunflower oil', 1, 'Beer-battered onion rings.'],
        ]],
        ['Coleslaw', 'side', 'salad', [
            'base' => [3.5, 'cabbage, carrot, mayonnaise, apple cider vinegar', 1, 'Creamy cabbage and carrot slaw.'],
        ]],
        ['Caesar Salad', 'salad', 'salad', [
            'mid' => [12, 'romaine lettuce, chicken breast, croutons, parmigiano reggiano, caesar dressing', 2, 'Romaine, grilled chicken, croutons and Caesar dressing.'],
        ]],
        ['Pancakes with Maple Syrup', 'dessert', 'brunch', [
            'base' => [7, 'wheat flour, egg, milk, butter, maple syrup', 1, 'Stack of three fluffy pancakes with maple syrup.'],
        ]],
        ['New York Cheesecake', 'dessert', 'cake', [
            'mid' => [7, 'cream cheese, graham crackers, egg, sugar, mixed berries', 2, 'Baked cheesecake with a berry compote.'],
        ]],
        ['Vanilla Milkshake', 'dessert', 'smoothie', [
            'base' => [5.5, 'vanilla gelato, milk, whipped cream', 1, 'Thick vanilla shake with whipped cream.'],
        ]],
        ['Brownie with Ice Cream', 'dessert', 'dessert', [
            'base' => [6, 'brownie, vanilla gelato, dark chocolate', 1, 'Warm brownie, vanilla ice cream and hot fudge.'],
        ]],
    ],
];
