<?php

// Cafés and brunch places.
return [
    'types' => ['Café & Brunch'],
    'concepts' => [
        ['Caffè Aurora Brunch', 'budget', 'All-day brunch café with specialty coffee, eggs your way and pancakes.'],
        ['Sunday Toast Club', 'budget', 'Avocado toast, granola bowls and cold brew, every day from 8 to 4.'],
    ],
    'recipes' => [
        ['Avocado Toast', 'sandwich', 'brunch', [
            'base' => [[9, 'sourdough bread, avocado, lemon, chilli flakes, radish', 2, 'Smashed avocado on sourdough with chilli and radish, vegan.'],
                       [11, 'sourdough bread, avocado, soft-boiled egg, feta, chilli flakes', 2, 'Avocado toast with a jammy egg and feta.']],
        ]],
        ['Eggs Benedict', 'main', 'brunch', [
            'base' => [12, 'brioche bun, egg, prosciutto cotto, hollandaise', 3, 'Poached eggs and ham on a toasted muffin with hollandaise.'],
        ]],
        ['Eggs Royale', 'main', 'brunch', [
            'base' => [14, 'brioche bun, egg, smoked salmon, hollandaise, chives', 3, 'Poached eggs, smoked salmon and hollandaise.'],
        ]],
        ['Full Breakfast', 'main', 'brunch', [
            'base' => [14, 'egg, bacon, italian sausage, mushrooms, tomato, sourdough bread', 3, 'Eggs, bacon, sausage, mushrooms, grilled tomato and toast.'],
        ]],
        ['Buttermilk Pancakes', 'dessert', 'brunch', [
            'base' => [[9, 'wheat flour, buttermilk, egg, butter, maple syrup, blueberries', 2, 'Fluffy pancakes with blueberries and maple syrup.'],
                       [10, 'wheat flour, buttermilk, egg, bacon, maple syrup', 2, 'Pancakes with crispy bacon and maple syrup.']],
        ]],
        ['French Toast', 'dessert', 'brunch', [
            'base' => [9, 'brioche dough, egg, milk, cinnamon, mixed berries, maple syrup', 2, 'Brioche French toast with berries.'],
        ]],
        ['Granola Bowl', 'dessert', 'brunch', [
            'base' => [7, 'greek yogurt, granola, banana, blueberries, honey', 2, 'Greek yogurt with house granola and fruit.'],
        ]],
        ['Club Sandwich', 'sandwich', 'sandwich', [
            'base' => [11, 'country bread, chicken breast, bacon, egg, lettuce, tomato, mayonnaise', 3, 'Triple-decker with chicken, bacon and egg, with fries.'],
        ]],
        ['Shakshuka', 'main', 'brunch', [
            'base' => [11, 'egg, tomato, red pepper, onion, cumin, feta, country bread', 2, 'Eggs baked in spiced tomato and pepper sauce.'],
        ]],
        ['Banana Bread', 'dessert', 'pastry', [
            'base' => [4, 'banana, wheat flour, egg, butter, walnuts', 2, 'Toasted banana bread with butter.'],
        ]],
        ['Cold Brew', 'hot_drink', 'coffee', [
            'base' => [4, 'coffee, ice', 1, 'Steeped eighteen hours, served on ice.'],
        ]],
        ['Matcha Latte', 'hot_drink', 'tea', [
            'base' => [4.5, 'matcha, oat milk', 1, 'Ceremonial matcha whisked with oat milk.'],
        ]],
    ],
];
