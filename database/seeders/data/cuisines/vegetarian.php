<?php

// Vegetarian restaurants (dairy and eggs allowed).
return [
    'types' => ['Vegetarian'],
    'extra_types' => ['premium' => ['Fine Dining']],
    'concepts' => [
        ['Orto Felice', 'trattoria', 'Vegetarian trattoria working with farms within fifty kilometres: seasonal vegetables, fresh pasta and local cheeses.'],
        ['Radici Vegetarian Bistrot', 'premium', 'Fine vegetarian cooking where vegetables are the main course: fermentations, ashes and a cellar of natural wines.', [
            ['Celeriac Cooked Like a Roast', 'main', 'finedining', 26, 'celeriac, butter, thyme, hazelnuts, black truffle', 2, 'A whole celeriac basted in butter like a roast, with hazelnuts and truffle.'],
        ]],
    ],
    'recipes' => [
        ['Seasonal Soup', 'soup', 'soup', [
            'mid' => [[9, 'pumpkin, carrot, onion, ginger, vegetable stock, pumpkin seeds', 2, 'Pumpkin and ginger velouté with toasted seeds.'],
                      [9, 'peas, mint, spring onion, vegetable stock, crème fraîche', 2, 'Spring pea and mint soup with crème fraîche.']],
        ]],
        ['Eggplant Parmigiana', 'main', 'lasagna', [
            'mid' => [13, 'aubergine, tomato sauce, mozzarella fior di latte, parmigiano reggiano, basil', 3, 'Baked aubergine parmigiana.'],
        ]],
        ['Spinach and Ricotta Ravioli', 'pasta', 'pasta', [
            'mid' => [13, 'ravioli dough, spinach, ricotta, butter, sage, parmigiano reggiano', 3, 'Hand-made ravioli with butter and sage.'],
            'high' => [22, 'ravioli dough, ricotta, swiss chard, butter, hazelnuts', 3, 'Sheep ricotta and wild greens ravioli with brown butter and hazelnut.'],
        ]],
        ['Pumpkin Risotto with Amaretti', 'risotto', 'risotto', [
            'mid' => [14, 'carnaroli rice, pumpkin, amaretti, parmigiano reggiano, butter, vegetable stock', 2, 'Mantua-style pumpkin risotto with crumbled amaretti.'],
        ], ['season' => [9, 12]]],
        ['Asparagus, Poached Egg and Parmigiano', 'starter', 'brunch', [
            'mid' => [12, 'asparagus, egg, parmigiano reggiano, butter', 2, 'Green asparagus, a poached egg and Parmigiano shavings.'],
            'high' => [20, 'asparagus, egg yolk, hollandaise, black truffle', 2, 'White and green asparagus with confit yolk and hollandaise.'],
        ], ['season' => [4, 6]]],
        ['Halloumi and Quinoa Salad', 'salad', 'salad', [
            'mid' => [13, 'halloumi, quinoa, cherry tomatoes, cucumber, mint, pomegranate', 2, 'Grilled halloumi on herby quinoa with pomegranate.'],
        ]],
        ['Vegetable Lasagne', 'pasta', 'lasagna', [
            'mid' => [13, 'lasagne sheets, zucchini, mushrooms, béchamel, parmigiano reggiano', 3, 'Lasagne with courgettes, mushrooms and béchamel.'],
        ]],
        ['Mushroom Wellington', 'main', 'vegetables', [
            'high' => [26, 'mushrooms, puff pastry, chestnuts, spinach, egg', 3, 'Mushroom and chestnut wellington with a red wine jus.'],
        ], ['season' => [10, 3]]],
        ['Beetroot Tartare, Goat Cheese', 'starter', 'salad', [
            'high' => [16, 'beetroot, goat cheese, capers, shallot, dijon mustard', 2, 'Diced roast beetroot dressed like a tartare, with goat cheese.'],
        ]],
        ['Cheese and Honey Board', 'starter', 'antipasti', [
            'mid' => [14, 'pecorino romano, gorgonzola, goat cheese, honey, walnuts', 3, 'Three local cheeses with honey and walnuts.'],
        ]],
        ['Carrot Cake', 'dessert', 'cake', [
            'mid' => [6, 'carrot, wheat flour, egg, walnuts, cream cheese, cinnamon', 2, 'Spiced carrot cake with cream cheese frosting.'],
        ]],
        ['Chocolate Fondant', 'dessert', 'dessert', [
            'high' => [12, 'dark chocolate, butter, egg, sugar, vanilla gelato', 2, 'Molten chocolate fondant with vanilla gelato.'],
        ]],
    ],
];
