<?php

// Pizzerias: neighbourhood (budget), Neapolitan (traditional) and gourmet (premium).
return [
    'types' => ['Pizzeria'],
    'extra_types' => ['premium' => ['Gourmet Pizza'], 'traditional' => ['Italian']],
    'concepts' => [
        ['Forno Saraceno', 'budget', 'Pizza by the slice and round pizzas from an electric deck oven, with long-fermented dough and generous toppings at neighbourhood prices.'],
        ['Pizzeria Il Vicoletto', 'budget', 'A family pizzeria with twelve tables and a busy delivery counter: thin, crisp Roman-style bases and classic toppings.'],
        ['Da Gennarino', 'traditional', 'Wood-fired Neapolitan pizza with a soft, airy cornicione, made with a 48-hour biga and tomatoes from the Agro Sarnese-Nocerino.'],
        ['Pizzeria Partenope', 'traditional', 'Neapolitan pizzaioli working a dome oven at 450°C: fried starters, classic pizzas and a short list of Campanian wines.'],
        ['Masardona Lab', 'traditional', 'Pizza fritta and wood-fired pies in the street tradition of Naples, with Campanian cheeses and cured meats.'],
        ['Lievito Madre Pizza Bistrot', 'premium', 'Gourmet pizza on a sourdough base with 72-hour fermentation, topped with Slow Food presidia and paired with craft beers and natural wines.', [
            ['Crunch of Mortadella and Pistachio', 'pizza', 'pizza', 16, 'pizza dough, mortadella, bronte pistachio, burrata, lemon', 3, 'A crunchy "padellino" base topped after baking with thin slices of Bologna mortadella, pistachio crumble and burrata with lemon zest.'],
            ['Yellow Tomato and Colatura', 'pizza', 'pizza', 15, 'pizza dough, yellow datterino tomatoes, anchovies, colatura di alici, fried basil', 2, 'Yellow datterini cooked in their juice, Cantabrian anchovies and a few drops of Cetara colatura.'],
        ]],
        ['Crosta Contemporanea', 'premium', 'A contemporary pizzeria where each pie is a composed dish: seasonal toppings, flours from Italian mills and a tasting of four half-pizzas.', [
            ['Pumpkin, Smoked Provola and Amaretto', 'pizza', 'pizza', 15, 'pizza dough, pumpkin, provola, amaretti, sage', 3, 'Roasted pumpkin cream, smoked provola, crumbled amaretto and crisp sage: sweet, smoky and savoury.'],
            ['Pizza Tasting for Two', 'tasting_menu', 'pizza', 46, 'pizza dough, buffalo mozzarella, san marzano tomatoes, nduja, burrata, porcini mushrooms', 2, 'Four half-pizzas chosen by the pizzaiolo with the season, served one after the other.'],
        ]],
    ],
    'recipes' => [
        ['Margherita', 'pizza', 'pizza', [
            'base' => [6, 'pizza dough, tomato sauce, mozzarella fior di latte, basil, extra virgin olive oil', 3, 'Tomato, fior di latte and fresh basil on a thin, crisp base.'],
            'mid' => [8, 'pizza dough, san marzano tomatoes, mozzarella fior di latte, basil, extra virgin olive oil', 3, 'San Marzano tomato, Agerola fior di latte and basil on a soft, leopard-spotted crust.'],
            'high' => [12, 'pizza dough, yellow datterino tomatoes, buffalo mozzarella, fried basil, extra virgin olive oil', 3, 'Yellow datterino sauce, buffalo mozzarella added after baking and crisp fried basil.'],
        ]],
        ['Marinara', 'pizza', 'pizza', [
            'base' => [5, 'pizza dough, tomato sauce, garlic, oregano, extra virgin olive oil', 2, 'Tomato, garlic and oregano: the oldest pizza, naturally vegan.'],
            'mid' => [6.5, 'pizza dough, san marzano tomatoes, garlic, oregano, extra virgin olive oil', 2, 'San Marzano tomato, sliced garlic, wild oregano and a generous pour of olive oil.'],
            'high' => [10, 'pizza dough, cherry tomatoes, black garlic, oregano, capers, extra virgin olive oil', 2, 'Confit cherry tomatoes, black garlic cream, Pantelleria capers and oregano.'],
        ]],
        ['Diavola', 'pizza', 'pizza', [
            'base' => [7, 'pizza dough, tomato sauce, mozzarella fior di latte, spicy salami', 3, 'Tomato, mozzarella and hot salami.'],
            'mid' => [9, 'pizza dough, san marzano tomatoes, mozzarella fior di latte, spicy salami, chilli oil', 3, 'Spicy Calabrian salami with tomato, fior di latte and a drizzle of chilli oil.'],
        ]],
        ['Capricciosa', 'pizza', 'pizza', [
            'base' => [8, 'pizza dough, tomato sauce, mozzarella fior di latte, prosciutto cotto, mushrooms, artichoke, olives', 3, 'Ham, mushrooms, artichokes and olives over tomato and mozzarella.'],
            'mid' => [10, 'pizza dough, san marzano tomatoes, mozzarella fior di latte, prosciutto cotto, mushrooms, artichoke, olives, egg', 3, 'The full Neapolitan capricciosa, with a hard-boiled egg as tradition wants.'],
        ]],
        ['Quattro Formaggi', 'pizza', 'pizza', [
            'base' => [8, 'pizza dough, mozzarella fior di latte, gorgonzola, fontina, parmigiano reggiano', 2, 'White pizza with mozzarella, gorgonzola, fontina and Parmigiano.'],
            'mid' => [10, 'pizza dough, mozzarella fior di latte, gorgonzola, taleggio, pecorino romano, walnuts', 2, 'Four cheeses from four regions finished with toasted walnuts.'],
        ]],
        ['Prosciutto e Funghi', 'pizza', 'pizza', [
            'base' => [7.5, 'pizza dough, tomato sauce, mozzarella fior di latte, prosciutto cotto, mushrooms', 3, 'Cooked ham and sliced mushrooms over tomato and mozzarella.'],
        ]],
        ['Tonno e Cipolla', 'pizza', 'pizza', [
            'base' => [7.5, 'pizza dough, tomato sauce, mozzarella fior di latte, canned tuna, red onion', 3, 'Tuna in olive oil and thinly sliced red onion.'],
            'mid' => [9.5, 'pizza dough, san marzano tomatoes, canned tuna, tropea onion, oregano, extra virgin olive oil', 3, 'No cheese: tuna in olive oil, sweet Tropea onion and oregano on San Marzano tomato.'],
        ]],
        ['Ortolana', 'pizza', 'pizza', [
            'base' => [8, 'pizza dough, tomato sauce, mozzarella fior di latte, zucchini, aubergine, bell pepper', 3, 'Grilled courgettes, aubergines and peppers on tomato and mozzarella.'],
            'mid' => [9.5, 'pizza dough, mozzarella fior di latte, zucchini, aubergine, red pepper, cherry tomatoes, basil', 3, 'White base with roasted summer vegetables, cherry tomatoes and basil.'],
        ], ['season' => [5, 9]]],
        ['Bufalina', 'pizza', 'pizza', [
            'mid' => [10.5, 'pizza dough, san marzano tomatoes, buffalo mozzarella, basil, extra virgin olive oil', 3, 'San Marzano tomato and buffalo mozzarella DOP from Caserta.'],
        ]],
        ['Salsiccia e Friarielli', 'pizza', 'pizza', [
            'mid' => [10, 'pizza dough, provola, italian sausage, broccoli rabe, chilli pepper', 3, 'Smoked provola, crumbled pork sausage and sautéed friarielli with a hint of chilli.'],
            'high' => [14, 'pizza dough, provola, italian sausage, broccoli rabe, garlic, pecorino romano', 3, 'Fennel sausage from a Campanian butcher, friarielli cream and aged pecorino.'],
        ], ['season' => [10, 3]]],
        ['Nduja e Burrata', 'pizza', 'pizza', [
            'mid' => [11, 'pizza dough, san marzano tomatoes, nduja, burrata, basil', 3, 'Spreadable spicy nduja from Spilinga, cooled down by a whole burrata.'],
            'high' => [15, 'pizza dough, yellow datterino tomatoes, nduja, stracciatella, honey', 3, 'Nduja and yellow tomato, stracciatella and a thread of acacia honey.'],
        ]],
        ['Crudo e Rucola', 'pizza', 'pizza', [
            'base' => [8.5, 'pizza dough, tomato sauce, mozzarella fior di latte, prosciutto crudo, rocket', 3, 'Mozzarella pizza topped with Parma ham and rocket after baking.'],
            'mid' => [11, 'pizza dough, mozzarella fior di latte, prosciutto crudo, rocket, parmigiano reggiano', 3, 'Prosciutto crudo aged 24 months, wild rocket and Parmigiano shavings.'],
        ]],
        ['Porcini e Tartufo', 'pizza', 'pizza', [
            'high' => [18, 'pizza dough, mozzarella fior di latte, porcini mushrooms, black truffle, parmigiano reggiano', 3, 'Sautéed porcini, fior di latte and fresh black truffle shaved at the table.'],
        ], ['season' => [9, 12]]],
        ['Calzone', 'pizza', 'pizza', [
            'base' => [8, 'pizza dough, ricotta, mozzarella fior di latte, salame, tomato sauce', 3, 'Folded pizza filled with ricotta, mozzarella and salami.'],
            'mid' => [9.5, 'pizza dough, ricotta, provola, salame, black pepper', 3, 'Baked calzone with ricotta, smoked provola, salami and cracked pepper.'],
        ]],
        ['Pizza Fritta', 'pizza', 'pizza', [
            'mid' => [8.5, 'pizza dough, ricotta, provola, tomato sauce, sunflower oil', 2, 'Deep-fried folded pizza with ricotta, provola and a spoon of tomato, the Naples street classic.'],
        ]],
        ['Gluten-Free Margherita', 'pizza', 'pizza', [
            'mid' => [10, 'rice flour, tomato sauce, mozzarella fior di latte, basil', 3, 'Made with a gluten-free flour blend and baked on a dedicated tray.'],
        ]],
        ['Supplì', 'starter', 'antipasti', [
            'base' => [2.5, 'arborio rice, tomato sauce, mozzarella fior di latte, breadcrumbs, egg', 2, 'Fried Roman rice ball with tomato and a stringy mozzarella heart.'],
        ]],
        ['Frittatina di Pasta', 'starter', 'antipasti', [
            'mid' => [4, 'bucatini, béchamel, peas, prosciutto cotto, breadcrumbs', 2, 'Deep-fried bucatini with béchamel, peas and ham, a Neapolitan pizzeria starter.'],
        ]],
        ['Montanarine', 'starter', 'antipasti', [
            'mid' => [6, 'pizza dough, tomato sauce, parmigiano reggiano, basil', 1, 'Three small fried dough discs topped with tomato, Parmigiano and basil.'],
        ]],
        ['Crocchè', 'starter', 'fries', [
            'mid' => [4.5, 'potato, parmigiano reggiano, parsley, egg, breadcrumbs', 1, 'Potato croquettes with Parmigiano and parsley, two per portion.'],
        ]],
        ['Fried Mixed Platter', 'starter', 'fries', [
            'base' => [7, 'potato, arborio rice, mozzarella fior di latte, zucchini flowers, wheat flour', 2, 'Croquettes, rice balls, mozzarella bites and fried zucchini flowers to share.'],
        ], ['serves' => 2]],
        ['French Fries', 'side', 'fries', [
            'base' => [3.5, 'potato, sunflower oil, sea salt', 1, 'Crisp chips fried in sunflower oil.'],
        ]],
        ['Caprese Salad', 'salad', 'salad', [
            'mid' => [9, 'buffalo mozzarella, tomato, basil, extra virgin olive oil', 2, 'Buffalo mozzarella with ripe tomatoes and basil.'],
        ], ['season' => [5, 9]]],
        ['Green Salad', 'salad', 'salad', [
            'base' => [5, 'mixed leaves, cherry tomatoes, carrot, extra virgin olive oil', 1, 'Mixed leaves, cherry tomatoes and shredded carrot.'],
        ]],
        ['Tiramisù', 'dessert', 'tiramisu', [
            'base' => [4.5, 'mascarpone, savoiardi biscuits, espresso, egg, sugar, cocoa powder', 2, 'Homemade tiramisù with mascarpone and espresso-soaked savoiardi.'],
            'mid' => [6, 'mascarpone, savoiardi biscuits, espresso, egg yolk, sugar, cocoa powder, marsala', 2, 'Classic tiramisù with a splash of Marsala in the coffee.'],
        ]],
        ['Babà al Rum', 'dessert', 'dessert', [
            'mid' => [5.5, 'baba dough, rum syrup, whipped cream', 1, 'Soft Neapolitan babà soaked in rum syrup, with whipped cream.'],
        ]],
        ['Nutella Pizza', 'dessert', 'pizza', [
            'base' => [7, 'pizza dough, hazelnut spread, icing sugar', 2, 'Folded pizza filled with hazelnut spread, to share.'],
        ], ['serves' => 2]],
        ['Sfogliatella', 'dessert', 'pastry', [
            'mid' => [3.5, 'sfogliatella pastry, ricotta, semolina, candied orange peel, cinnamon', 2, 'Crisp shell-shaped pastry filled with ricotta, semolina and candied orange.'],
        ]],
    ],
];
