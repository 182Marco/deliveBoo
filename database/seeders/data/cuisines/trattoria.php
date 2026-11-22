<?php

// Italian trattorias and traditional restaurants, from different regions.
return [
    'types' => ['Italian', 'Italian Regional'],
    'concepts' => [
        ['Trattoria da Nives', 'trattoria', 'Roman trattoria with checked tablecloths: carbonara, amatriciana and the Thursday gnocchi, made by the same family since 1971.'],
        ['Osteria del Borgo Antico', 'trattoria', 'Tuscan osteria with hand-rolled pici, Chianina beef and a cellar of small Chianti producers.'],
        ['Sfoglia & Mattarello', 'trattoria', 'Bolognese pasta workshop where the sfogline roll tagliatelle and tortellini by hand in the window every morning.'],
        ['Locanda delle Langhe', 'traditional', 'Piedmontese cooking from the Langhe hills: vitello tonnato, tajarin with forty egg yolks and braised beef in Barolo.'],
        ['Masseria Ulivo Grosso', 'traditional', 'Apulian recipes with olive oil from the family grove: orecchiette, burrata, fava bean purée and grilled lamb.'],
        ['Trattoria Mare e Monti', 'trattoria', 'A Ligurian kitchen between the sea and the hills: trofie with pesto, stuffed anchovies and focaccia baked twice a day.'],
        ['Antica Osteria Milanese', 'traditional', 'Milanese classics served on bentwood chairs: saffron risotto, ossobuco, mondeghili and the Sunday cotoletta.'],
        ['Taverna dei Quattro Mori', 'traditional', 'Sardinian tavern with culurgiones, malloreddus and slow-roasted suckling pig from the inland villages.'],
        ['Cucina di Nonna Pina', 'trattoria', 'Sicilian home cooking: pasta alla Norma, arancine as big as an orange and cannoli filled to order.'],
        ['Osteria Ai Do Ponti', 'traditional', 'Venetian bacaro and osteria: cicchetti at the counter, sarde in saor and bigoli in salsa at the table.'],
        ['Il Desco Rustico', 'traditional', 'A large country restaurant for families and celebrations, with a long menu of Italian classics and a wood-burning grill.'],
    ],
    'recipes' => [
        // antipasti
        ['Tagliere Misto', 'starter', 'antipasti', [
            'mid' => [[14, 'prosciutto crudo, salame, mortadella, pecorino romano, country bread', 3, 'Board of cured meats and pecorino with country bread, for two.'],
                      [16, 'culatello, coppa, parmigiano reggiano, giardiniera, focaccia', 3, 'Emilian board: culatello, coppa, 30-month Parmigiano and pickled vegetables.']],
        ], ['serves' => 2]],
        ['Bruschette al Pomodoro', 'starter', 'bruschetta', [
            'mid' => [6, 'country bread, tomato, garlic, basil, extra virgin olive oil', 2, 'Grilled bread rubbed with garlic, topped with chopped tomato and basil.'],
        ], ['season' => [5, 9]]],
        ['Vitello Tonnato', 'starter', 'meat', [
            'mid' => [12, 'veal, canned tuna, mayonnaise, capers, anchovies', 2, 'Thin slices of pink veal under a creamy tuna and caper sauce.'],
        ]],
        ['Burrata con Pomodorini', 'starter', 'burrata', [
            'mid' => [11, 'burrata, cherry tomatoes, basil, extra virgin olive oil', 1, 'Apulian burrata with marinated cherry tomatoes and basil oil.'],
        ]],
        ['Parmigiana di Melanzane', 'starter', 'lasagna', [
            'mid' => [11, 'aubergine, tomato sauce, mozzarella fior di latte, parmigiano reggiano, basil', 3, 'Fried aubergine layered with tomato, fior di latte and Parmigiano, baked until bubbling.'],
        ]],
        ['Fiori di Zucca Fritti', 'starter', 'antipasti', [
            'mid' => [9, 'zucchini flowers, mozzarella fior di latte, anchovies, wheat flour', 2, 'Courgette flowers stuffed with mozzarella and anchovy, fried in a light batter.'],
        ], ['season' => [5, 8]]],
        ['Sarde in Saor', 'starter', 'fish', [
            'mid' => [10, 'sardines, onion, raisins, pine nuts, red wine vinegar', 2, 'Fried sardines marinated with sweet-and-sour onions, raisins and pine nuts, Venetian style.'],
        ]],
        ['Arancine Siciliane', 'starter', 'antipasti', [
            'mid' => [5, 'arborio rice, ragù bolognese, peas, caciocavallo, saffron, breadcrumbs', 3, 'Saffron rice cone filled with ragù, peas and caciocavallo.'],
        ]],
        ['Mondeghili', 'starter', 'meat', [
            'mid' => [8, 'beef, mortadella, parmigiano reggiano, breadcrumbs, egg, butter', 2, 'Milanese meatballs of leftover boiled beef and mortadella, fried in butter.'],
        ]],
        ['Insalata di Puntarelle', 'salad', 'salad', [
            'mid' => [8, 'puntarelle, anchovies, garlic, red wine vinegar, extra virgin olive oil', 1, 'Crisp chicory shoots dressed with anchovy, garlic and vinegar.'],
        ], ['season' => [11, 3]]],
        ['Fave e Cicoria', 'starter', 'soup', [
            'mid' => [10, 'fava beans, turnip greens, extra virgin olive oil, country bread', 2, 'Silky dried fava bean purée with sautéed wild chicory, Apulian peasant style.'],
        ]],

        // primi
        ['Spaghetti alla Carbonara', 'pasta', 'pasta', [
            'mid' => [[12, 'spaghetti, guanciale, egg yolk, pecorino romano, black pepper', 3, 'Guanciale, egg yolk, Pecorino Romano and black pepper: no cream, ever.'],
                      [13, 'rigatoni, guanciale, egg, pecorino romano, black pepper', 3, 'Rigatoni version with crisp guanciale and a creamy egg and pecorino sauce.']],
        ]],
        ['Bucatini all\'Amatriciana', 'pasta', 'pasta', [
            'mid' => [12, 'bucatini, guanciale, san marzano tomatoes, pecorino romano, chilli pepper', 3, 'Guanciale and tomato with a touch of chilli and plenty of Pecorino.'],
        ]],
        ['Tonnarelli Cacio e Pepe', 'pasta', 'pasta', [
            'mid' => [11, 'tonnarelli, pecorino romano, black pepper', 2, 'Square egg spaghetti tossed with a creamy sauce of Pecorino and toasted pepper.'],
        ]],
        ['Spaghetti alla Gricia', 'pasta', 'pasta', [
            'mid' => [11.5, 'spaghetti, guanciale, pecorino romano, black pepper', 2, 'The ancestor of carbonara and amatriciana: just guanciale, pecorino and pepper.'],
        ]],
        ['Tagliatelle al Ragù', 'pasta', 'pasta', [
            'mid' => [[13, 'fresh tagliatelle, ragù bolognese, parmigiano reggiano', 2, 'Hand-cut egg tagliatelle with slow-cooked Bolognese ragù.'],
                      [14, 'fresh tagliatelle, ragù bolognese, butter, parmigiano reggiano', 2, 'Tagliatelle rolled this morning, ragù simmered for six hours and a knob of butter.']],
        ]],
        ['Lasagne alla Bolognese', 'pasta', 'lasagna', [
            'mid' => [13, 'lasagne sheets, ragù bolognese, béchamel, parmigiano reggiano', 2, 'Green spinach lasagne layered with ragù and béchamel, baked to a crisp edge.'],
        ]],
        ['Tortellini in Brodo', 'soup', 'soup', [
            'mid' => [14, 'ravioli dough, pork, prosciutto crudo, mortadella, parmigiano reggiano, beef broth', 3, 'Tiny hand-folded tortellini in a clear capon and beef broth.'],
        ], ['season' => [10, 3]]],
        ['Pici all\'Aglione', 'pasta', 'pasta', [
            'mid' => [12, 'pici, tomato, garlic, extra virgin olive oil, chilli pepper', 2, 'Thick hand-rolled Tuscan spaghetti with a sweet sauce of giant Val di Chiana garlic.'],
        ]],
        ['Pappardelle al Cinghiale', 'pasta', 'pasta', [
            'mid' => [15, 'pappardelle, wild boar ragù, rosemary, juniper berries', 2, 'Wide egg ribbons with wild boar braised in red wine and juniper.'],
        ], ['season' => [10, 3]]],
        ['Ribollita', 'soup', 'soup', [
            'mid' => [10, 'cannellini beans, cavolo nero, country bread, carrot, onion, celery, extra virgin olive oil', 3, 'Twice-cooked Tuscan bread and bean soup with black kale.'],
        ], ['season' => [11, 3]]],
        ['Orecchiette con Cime di Rapa', 'pasta', 'pasta', [
            'mid' => [12, 'orecchiette, broccoli rabe, anchovies, garlic, chilli pepper, breadcrumbs', 2, 'Ear-shaped pasta with turnip tops, anchovies and toasted breadcrumbs.'],
        ], ['season' => [11, 4]]],
        ['Orecchiette al Sugo e Cacioricotta', 'pasta', 'pasta', [
            'mid' => [11, 'orecchiette, tomato sauce, basil, sheep ricotta', 2, 'Orecchiette with fresh tomato sauce and grated salted ricotta.'],
        ]],
        ['Trofie al Pesto', 'pasta', 'pasta', [
            'mid' => [12, 'trofie, pesto genovese, potato, green beans', 2, 'Ligurian twists with basil pesto, potato and green beans, as in Recco.'],
        ]],
        ['Pasta alla Norma', 'pasta', 'pasta', [
            'mid' => [12, 'rigatoni, aubergine, tomato sauce, sheep ricotta, basil', 2, 'Fried aubergine, tomato and salted ricotta, Catania style.'],
        ]],
        ['Bigoli in Salsa', 'pasta', 'pasta', [
            'mid' => [13, 'pici, onion, anchovies, extra virgin olive oil', 2, 'Thick Venetian whole wheat spaghetti with slow-cooked onion and anchovy sauce.'],
        ]],
        ['Tajarin al Burro e Salvia', 'pasta', 'pasta', [
            'mid' => [13, 'egg pasta, butter, sage, parmigiano reggiano', 2, 'Thin Piedmontese egg noodles with brown butter and sage.'],
        ]],
        ['Culurgiones', 'pasta', 'pasta', [
            'mid' => [14, 'ravioli dough, potato, pecorino sardo, mint, tomato sauce', 3, 'Wheat-ear sealed Sardinian ravioli filled with potato, pecorino and mint.'],
        ]],
        ['Malloreddus alla Campidanese', 'pasta', 'pasta', [
            'mid' => [13, 'semolina, italian sausage, tomato sauce, saffron, pecorino sardo', 3, 'Small ridged gnocchetti with sausage and tomato ragù, scented with saffron.'],
        ]],
        ['Risotto alla Milanese', 'risotto', 'risotto', [
            'mid' => [14, 'carnaroli rice, saffron, butter, parmigiano reggiano, beef broth, onion', 2, 'Saffron risotto finished with butter and Parmigiano.'],
        ]],
        ['Risotto ai Funghi Porcini', 'risotto', 'risotto', [
            'mid' => [16, 'carnaroli rice, porcini mushrooms, vegetable stock, butter, parmigiano reggiano, parsley', 2, 'Carnaroli rice with fresh porcini and a long, patient stir.'],
        ], ['season' => [9, 11]]],
        ['Gnocchi al Pomodoro', 'pasta', 'pasta', [
            'mid' => [11, 'potato gnocchi, tomato sauce, basil, parmigiano reggiano', 2, 'Soft potato gnocchi with fresh tomato and basil.'],
        ]],
        ['Gnocchi alla Sorrentina', 'pasta', 'pasta', [
            'mid' => [12, 'potato gnocchi, tomato sauce, mozzarella fior di latte, basil', 2, 'Gnocchi baked in a terracotta dish with tomato and melting fior di latte.'],
        ]],
        ['Gluten-Free Spaghetti al Pomodoro', 'pasta', 'pasta', [
            'mid' => [11, 'gluten-free pasta, tomato sauce, basil, extra virgin olive oil', 1, 'Corn and rice spaghetti cooked in a separate pot, with fresh tomato sauce.'],
        ]],

        // secondi
        ['Saltimbocca alla Romana', 'main', 'meat', [
            'mid' => [16, 'veal, prosciutto crudo, sage, butter, white wine', 2, 'Veal escalopes with prosciutto and sage, glazed with white wine.'],
        ]],
        ['Ossobuco con Gremolada', 'main', 'meat', [
            'mid' => [24, 'veal shank, white wine, onion, carrot, lemon, parsley, garlic', 1, 'Braised veal shank with lemon and parsley gremolada, served with saffron risotto on request.'],
        ], ['season' => [10, 4]]],
        ['Cotoletta alla Milanese', 'main', 'meat', [
            'mid' => [24, 'veal, egg, breadcrumbs, clarified butter, lemon', 1, 'Bone-in veal chop breaded and fried in clarified butter, "elephant ear" thin.'],
        ]],
        ['Bistecca alla Fiorentina', 'main', 'steak', [
            'mid' => [55, 'beef, sea salt, black pepper, rosemary, extra virgin olive oil', 1, 'T-bone of at least 1.2 kg grilled over embers, served rare: price for two.'],
        ], ['serves' => 2]],
        ['Tagliata di Manzo', 'main', 'steak', [
            'mid' => [22, 'beef sirloin, rocket, parmigiano reggiano, aged balsamic vinegar', 1, 'Sliced grilled sirloin on rocket with Parmigiano and aged balsamic.'],
        ]],
        ['Brasato al Barolo', 'main', 'meat', [
            'mid' => [22, 'beef, red wine, onion, carrot, celery, polenta', 2, 'Beef braised for hours in Barolo, served with soft polenta.'],
        ], ['season' => [10, 3]]],
        ['Coda alla Vaccinara', 'main', 'stew', [
            'mid' => [18, 'oxtail, tomato sauce, celery, cocoa powder, pine nuts, raisins', 1, 'Oxtail stewed with tomato and lots of celery, finished with cocoa, raisins and pine nuts.'],
        ]],
        ['Porchetta di Ariccia', 'main', 'roast', [
            'mid' => [16, 'pork belly, rosemary, garlic, black pepper, wild fennel', 1, 'Slow-roasted rolled pork with crackling skin, scented with wild fennel.'],
        ]],
        ['Porceddu', 'main', 'roast', [
            'mid' => [24, 'suckling pig, myrtle, sea salt', 1, 'Sardinian suckling pig roasted on myrtle branches until the skin crackles.'],
        ]],
        ['Agnello alla Brace', 'main', 'meat', [
            'mid' => [20, 'lamb chops, rosemary, garlic, extra virgin olive oil', 1, 'Lamb chops grilled over olive wood with rosemary and garlic.'],
        ]],
        ['Pollo alla Cacciatora', 'main', 'chicken', [
            'mid' => [15, 'chicken thighs, tomato, olives, rosemary, white wine, garlic', 1, 'Hunter-style chicken braised with tomato, olives and rosemary.'],
        ]],
        ['Polpette al Sugo', 'main', 'meat', [
            'mid' => [13, 'ground beef, pork, country bread, parmigiano reggiano, egg, tomato sauce', 2, 'Grandma\'s meatballs simmered in tomato sauce.'],
        ]],
        ['Baccalà alla Vicentina', 'main', 'fish', [
            'mid' => [19, 'salt cod, milk, onion, anchovies, parmigiano reggiano, polenta', 1, 'Stockfish slowly cooked in milk and oil with onions, served on grilled polenta.'],
        ]],
        ['Frittura di Paranza', 'main', 'seafood', [
            'mid' => [18, 'anchovies, squid, prawns, wheat flour, lemon', 3, 'Small fish, squid rings and prawns from the morning catch, fried crisp.'],
        ]],

        // contorni
        ['Patate al Forno', 'side', 'side', [
            'mid' => [5, 'potato, rosemary, garlic, extra virgin olive oil', 1, 'Roast potatoes with rosemary and garlic.'],
        ]],
        ['Cicoria Ripassata', 'side', 'vegetables', [
            'mid' => [5, 'turnip greens, garlic, chilli pepper, extra virgin olive oil', 1, 'Wild chicory boiled, then tossed in the pan with garlic and chilli.'],
        ]],
        ['Carciofi alla Romana', 'side', 'vegetables', [
            'mid' => [7, 'artichoke, mint, garlic, parsley, extra virgin olive oil', 1, 'Whole artichokes braised with mint and garlic.'],
        ], ['season' => [1, 4]]],
        ['Fagioli all\'Uccelletto', 'side', 'stew', [
            'mid' => [6, 'cannellini beans, tomato, sage, garlic, extra virgin olive oil', 1, 'Cannellini beans stewed with tomato and sage.'],
        ]],
        ['Insalata Mista', 'side', 'salad', [
            'mid' => [5, 'mixed leaves, tomato, carrot, radicchio', 1, 'Seasonal mixed salad.'],
        ]],

        // dolci
        ['Tiramisù della Casa', 'dessert', 'tiramisu', [
            'mid' => [6, 'mascarpone, savoiardi biscuits, espresso, egg, sugar, cocoa powder', 2, 'Our tiramisù, assembled every morning.'],
        ]],
        ['Panna Cotta ai Frutti di Bosco', 'dessert', 'dessert', [
            'mid' => [5.5, 'cream, sugar, vanilla, gelatin, mixed berries', 1, 'Vanilla panna cotta with a warm berry compote.'],
        ]],
        ['Cantucci e Vin Santo', 'dessert', 'pastry', [
            'mid' => [7, 'wheat flour, almonds, egg, sugar, sweet wine', 2, 'Almond biscuits to dip in a glass of Vin Santo.'],
        ]],
        ['Cannolo Siciliano', 'dessert', 'pastry', [
            'mid' => [5, 'cannoli shells, sheep ricotta, sugar, candied orange peel, pistachios', 2, 'Crisp shell filled to order with sweetened sheep ricotta, candied orange and pistachio.'],
        ]],
        ['Seadas', 'dessert', 'dessert', [
            'mid' => [6.5, 'semolina, pecorino sardo, lemon, honey', 2, 'Fried pastry filled with fresh pecorino and lemon zest, drizzled with honey.'],
        ]],
        ['Zuppa Inglese', 'dessert', 'dessert', [
            'mid' => [6, 'sponge cake, custard, sweet liqueur, cocoa powder', 2, 'Emilian trifle with custard, chocolate cream and alchermes-soaked sponge.'],
        ]],
        ['Bonet', 'dessert', 'dessert', [
            'mid' => [6, 'egg, milk, sugar, cocoa powder, amaretti, rum', 2, 'Piedmontese chocolate and amaretti pudding with caramel.'],
        ]],
        ['Sgroppino', 'dessert', 'gelato', [
            'mid' => [7, 'lemon sorbet, vodka, sparkling wine', 1, 'Lemon sorbet whipped with vodka and Prosecco, Venetian style.'],
        ]],
    ],
];
