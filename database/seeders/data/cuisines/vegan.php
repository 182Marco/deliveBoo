<?php

// Vegan restaurants: everything plant-based.
return [
    'types' => ['Vegan', 'Vegetarian'],
    'concepts' => [
        ['Germoglio Vegan Kitchen', 'trattoria', '100% plant-based kitchen: legumes, whole grains, fermented vegetables and gluten-free options on request.'],
        ['Planta Burger', 'budget', 'Vegan burgers, plant-based wings and loaded fries.'],
        ['Verde Bowl Bar', 'budget', 'Vegan bowls, wraps and cold-pressed juices.'],
    ],
    'recipes' => [
        ['Buddha Bowl', 'poke', 'vegan_bowl', [
            'base' => [11, 'brown rice, chickpeas, sweet potato, kale, avocado, tahini sauce', 3, 'Brown rice, roasted chickpeas, sweet potato, kale and tahini.'],
            'mid' => [13, 'quinoa, tempeh, sweet potato, kale, avocado, pumpkin seeds, tahini sauce', 3, 'Quinoa with marinated tempeh and roasted vegetables.'],
        ], ['cal' => 620]],
        ['Vegan Smash Burger', 'burger', 'burger', [
            'base' => [11, 'vegan bun, pea protein patty, vegan cheese, pickles, onion, vegan mayonnaise', 2, 'Smashed pea protein patty with vegan cheese and pickles.'],
        ]],
        ['BBQ Seitan Sandwich', 'sandwich', 'sandwich', [
            'base' => [10, 'vegan bun, seitan, bbq sauce, cabbage, carrot', 2, 'Pulled seitan in barbecue sauce with slaw.'],
        ]],
        ['Cauliflower Wings', 'starter', 'vegetables', [
            'base' => [7, 'cauliflower, rice flour, sriracha, vegan mayonnaise', 1, 'Crispy cauliflower in hot sauce with vegan ranch.'],
        ]],
        ['Lentil Bolognese', 'pasta', 'pasta', [
            'mid' => [12, 'durum wheat pasta, lentils, tomato sauce, carrot, celery, onion', 2, 'Rigatoni with a slow-cooked lentil ragù.'],
        ]],
        ['Gluten-Free Pasta with Pesto', 'pasta', 'pasta', [
            'mid' => [12, 'gluten-free pasta, basil, pine nuts, garlic, extra virgin olive oil', 2, 'Corn and rice pasta with a dairy-free basil pesto.'],
        ]],
        ['Chickpea Curry', 'main', 'curry', [
            'mid' => [12, 'chickpeas, coconut milk, spinach, tomato, garam masala, basmati rice', 2, 'Chickpeas and spinach in coconut curry with rice.'],
        ]],
        ['Tofu Scramble', 'main', 'brunch', [
            'base' => [9, 'tofu, turmeric, spinach, cherry tomatoes, sourdough bread', 2, 'Scrambled tofu with turmeric, spinach and sourdough.'],
        ]],
        ['Raw Courgette Spaghetti', 'salad', 'salad', [
            'mid' => [11, 'zucchini, cherry tomatoes, cashews, basil, lemon', 2, 'Courgette noodles with cashew pesto and tomatoes.'],
        ], ['season' => [6, 9]]],
        ['Fruit Salad', 'dessert', 'fruit', [
            'base' => [5, 'strawberries, mango, kiwi, orange, mint', 2, 'Seasonal fruit salad with mint.'],
        ]],
        ['Mixed Nuts and Dried Fruit', 'side', 'fruit', [
            'base' => [4.5, 'almonds, cashews, walnuts, dried apricots, raisins', 3, 'A handful of nuts and dried fruit.'],
        ]],
        ['Raw Chocolate Cake', 'dessert', 'chocolate', [
            'mid' => [7, 'cashews, dates, cocoa powder, coconut, agave syrup', 3, 'No-bake cake of cashews, dates and raw cocoa.'],
        ]],
        ['Chia Pudding', 'dessert', 'dessert', [
            'base' => [5.5, 'chia seeds, almond milk, mango, maple syrup', 2, 'Chia seeds soaked in almond milk with mango.'],
        ]],
        ['Green Juice', 'soft_drink', 'juice', [
            'base' => [5.5, 'apple, cucumber, kale, lemon, ginger', 3, 'Cold-pressed apple, cucumber, kale, lemon and ginger.'],
        ]],
    ],
];
