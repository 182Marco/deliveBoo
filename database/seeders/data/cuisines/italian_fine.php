<?php

// Italian fine dining, starred restaurants and premium modern Italian kitchens.
return [
    'types' => ['Italian', 'Fine Dining'],
    'extra_types' => ['starred' => ['Starred Restaurant'], 'premium' => ['Mediterranean']],
    'concepts' => [
        ['Aurora Cucina d\'Autore', 'starred', 'Chef-owned restaurant with two tasting menus built on Lombard lake fish, mountain herbs and an 800-label cellar. Delivery of the full menu in insulated boxes, plated at home.', [
            ['Lake Perch, Brown Butter and Sage Ice', 'main', 'finedining', 42, 'trout, butter, sage, lemon, edible flowers', 2, 'Crisp-skinned lake perch fillets, hazelnut butter and a frozen sage granita.'],
            ['Risotto with Saffron Stamens and Bone Marrow', 'risotto', 'risotto', 38, 'carnaroli rice, saffron, bone marrow, beef broth, parmigiano reggiano', 3, 'A modern Milanese: whole saffron stamens, roasted marrow and a reduced beef jus.'],
        ]],
        ['Ristorante Lanterna Blu', 'starred', 'Seafront fine dining where the menu follows the day\'s catch: crudi, red prawns from Mazara and a sea urchin tasting in season.', [
            ['Sea Urchin, Burrata and Bergamot', 'starter', 'seafood', 36, 'sea urchin, burrata, bergamot, maldon salt', 2, 'Raw Sicilian sea urchin on cold burrata with bergamot zest.'],
            ['Spaghettoni with Clams and Lemon Leaves', 'pasta', 'pasta', 34, 'spaghettoni, clams, garlic, parsley, lemon, extra virgin olive oil', 2, 'Clams from Goro, lemon-leaf infused oil and spaghettoni finished in the clam juice.'],
        ]],
        ['Villa Gelsomino', 'fine_dining', 'A restored 18th-century villa with a kitchen garden: Piedmontese white truffle in autumn, game and a cheese trolley of forty references.', [
            ['Tajarin with White Truffle', 'pasta', 'pasta', 75, 'egg pasta, butter, parmigiano reggiano, white truffle', 3, 'Forty-yolk tajarin with butter and Parmigiano, under ten grams of Alba white truffle.'],
        ]],
        ['Atelier Sale e Brace', 'fine_dining', 'Open-fire cooking at its most precise: dry-aged meats, embers-roasted vegetables and wood-smoked desserts.', [
            ['Ember-Roasted Celeriac, Hazelnut and Black Garlic', 'main', 'finedining', 28, 'celeriac, hazelnuts, black garlic, butter, thyme', 2, 'Whole celeriac cooked in the embers for five hours, sliced at the pass with black garlic and hazelnuts.'],
        ]],
        ['Osteria Contemporanea Ginepro', 'premium', 'Modern Italian bistro with a short seasonal menu, house-made pasta and a natural wine list.'],
        ['Terrazza Corallo', 'premium', 'Rooftop restaurant with Mediterranean crudi, handmade pasta and a long list of white wines and champagne.'],
        ['Ristorante Il Melograno', 'premium', 'Elegant dining room in a historic palazzo: refined regional dishes, attentive service and a sommelier-led cellar.'],
    ],
    'recipes' => [
        // tasting menus (per person)
        ['Tasting Menu "Territory" · 7 courses', 'tasting_menu', 'finedining', [
            'high' => [140, 'trout, carnaroli rice, saffron, veal, porcini mushrooms, parmigiano reggiano, mixed berries', 4, 'Seven courses telling our region: lake fish, saffron risotto, veal and a forest-fruit finale. Price per person.'],
        ]],
        ['Tasting Menu "Sea" · 9 courses', 'tasting_menu', 'finedining', [
            'high' => [180, 'red prawns, oysters, sea bass, scallops, squid ink, spaghettoni, lemon', 4, 'Nine courses from the Mediterranean, from raw to roasted. Price per person.'],
        ]],
        ['Vegetarian Tasting Menu · 6 courses', 'tasting_menu', 'finedining', [
            'high' => [110, 'beetroot, celeriac, pumpkin, egg yolk, potato gnocchi, parmigiano reggiano, dark chocolate', 4, 'Six vegetable-led courses from our garden. Price per person.'],
        ]],
        ['Wine Pairing · 7 glasses', 'wine_red', 'wine_cellar', [
            'high' => [95, 'red wine, white wine, sparkling wine, sweet wine', 2, 'Seven glasses chosen by the sommelier to follow the tasting menu.'],
        ]],
        ['Prestige Wine Pairing · 7 glasses', 'wine_red', 'wine_cellar', [
            'high' => [190, 'champagne, red wine, white wine, sweet wine', 2, 'Grand cru and old-vintage glasses, opening with champagne.'],
        ]],

        // starters and crudi
        ['Red Prawn Carpaccio, Citrus and Fennel', 'starter', 'seafood', [
            'high' => [32, 'red prawns, orange, fennel, extra virgin olive oil, maldon salt', 1, 'Mazara red prawns pounded thin with blood orange, shaved fennel and olive oil.'],
        ]],
        ['Crudo of the Day', 'starter', 'sashimi', [
            'high' => [[38, 'tuna, sea bass, red prawns, scallops, lemon, extra virgin olive oil', 4, 'A selection of raw fish and shellfish dressed only with oil, lemon and salt.'],
                       [44, 'langoustines, oysters, red prawns, sea bream, lime', 4, 'Langoustine, oyster, red prawn and sea bream, chosen at dawn at the market.']],
        ]],
        ['Oysters, Shallot Vinegar and Lemon · 6 pieces', 'starter', 'seafood', [
            'high' => [30, 'oysters, shallot, red wine vinegar, lemon', 1, 'Six French oysters on ice with shallot vinegar.'],
        ]],
        ['Caviar, Potato Blini and Crème Fraîche · 30g', 'starter', 'finedining', [
            'high' => [120, 'caviar, potato, crème fraîche, chives, egg', 1, 'Thirty grams of Italian-farmed caviar with warm potato blini and crème fraîche.'],
        ]],
        ['Beef Tartare, Smoked Egg Yolk and Capers', 'starter', 'meat', [
            'high' => [26, 'beef fillet, egg yolk, capers, dijon mustard, shallot', 2, 'Hand-cut Piedmontese fassona with a cold-smoked egg yolk.'],
        ]],
        ['Vitello Tonnato, Modern Style', 'starter', 'meat', [
            'high' => [24, 'veal, tuna, capers, egg yolk, lemon', 2, 'Pink veal loin, raw tuna mayonnaise and fried caper flowers.'],
        ]],
        ['Foie Gras Terrine, Fig and Brioche', 'starter', 'finedining', [
            'high' => [34, 'foie gras, figs, brioche dough, sweet wine', 1, 'Duck foie gras terrine marinated in Passito, with fig chutney and toasted brioche.'],
        ]],
        ['Scallops, Cauliflower and Hazelnut', 'starter', 'seafood', [
            'high' => [30, 'scallops, cauliflower, hazelnuts, butter', 1, 'Seared Breton scallops on a smooth cauliflower purée with brown hazelnut butter.'],
        ], ['season' => [10, 4]]],
        ['Garden Salad, Herbs and Flowers', 'salad', 'salad', [
            'high' => [22, 'mixed leaves, edible flowers, radish, mixed herbs, extra virgin olive oil', 1, 'Thirty leaves, herbs and flowers picked from the villa garden this morning.'],
        ], ['season' => [4, 10]]],
        ['Burrata, Tomato Water and Basil', 'starter', 'burrata', [
            'high' => [22, 'burrata, tomato, basil, extra virgin olive oil', 1, 'Burrata in a clear chilled tomato consommé with basil oil.'],
        ], ['season' => [6, 9]]],
        ['Beetroot Cooked in Salt, Goat Cheese and Walnuts', 'starter', 'salad', [
            'high' => [20, 'beetroot, goat cheese, walnuts, apple cider vinegar', 2, 'Beetroot baked in a salt crust, whipped goat cheese and pickled walnuts.'],
        ]],
        ['Vegetable Consommé, Herb Dumplings', 'soup', 'soup', [
            'high' => [20, 'vegetable stock, ravioli dough, ricotta, mixed herbs', 2, 'A crystal-clear vegetable broth with tiny ricotta and herb dumplings.'],
        ]],

        // pasta and risotto
        ['Risotto with Gorgonzola and Pear', 'risotto', 'risotto', [
            'high' => [30, 'carnaroli rice, gorgonzola, pear, walnuts, vegetable stock', 2, 'Aged Carnaroli with mountain gorgonzola, poached pear and walnuts.'],
        ]],
        ['Risotto, Red Prawns and Lime', 'risotto', 'risotto', [
            'high' => [36, 'carnaroli rice, red prawns, lime, fish stock, butter', 2, 'Risotto made with prawn-head stock, raw red prawns on top and lime zest.'],
        ]],
        ['Black Truffle Risotto', 'risotto', 'risotto', [
            'high' => [42, 'carnaroli rice, black truffle, parmigiano reggiano, butter, vegetable stock', 2, 'Creamed with 36-month Parmigiano and finished with black Norcia truffle.'],
        ], ['season' => [11, 3]]],
        ['Plin with Roast Meat Jus', 'pasta', 'pasta', [
            'high' => [30, 'ravioli dough, veal, pork, rabbit, beef jus', 2, 'Hand-pinched Langhe ravioli filled with three roasts, served in their jus.'],
        ]],
        ['Cappelletti in Capon Broth', 'pasta', 'pasta', [
            'high' => [28, 'ravioli dough, parmigiano reggiano, beef broth, nutmeg', 2, 'Parmigiano-filled cappelletti in a broth simmered for twelve hours.'],
        ], ['season' => [11, 2]]],
        ['Linguine with Blue Lobster', 'pasta', 'pasta', [
            'high' => [58, 'linguine, blue lobster, cherry tomatoes, garlic, parsley', 2, 'Half a Breton blue lobster with linguine in its coral-rich sauce.'],
        ]],
        ['Spaghetti, Sea Urchin and Bread Crumbs', 'pasta', 'pasta', [
            'high' => [40, 'spaghetti, sea urchin, garlic, breadcrumbs, extra virgin olive oil', 2, 'Spaghetti tossed with raw sea urchin at the very end, crisp anchovy bread crumbs.'],
        ], ['season' => [11, 4]]],
        ['Raviolo of Egg Yolk, Ricotta and Butter', 'pasta', 'pasta', [
            'high' => [26, 'ravioli dough, egg yolk, ricotta, butter, parmigiano reggiano', 2, 'A single large raviolo holding a runny yolk, with butter and Parmigiano.'],
        ]],
        ['Paccheri with Scorpionfish Ragù', 'pasta', 'pasta', [
            'high' => [32, 'paccheri, red mullet, cherry tomatoes, garlic, parsley', 2, 'Large tubes with a white ragù of rockfish and cherry tomatoes.'],
        ]],
        ['Gnocchi, Potato Skins and Pecorino', 'pasta', 'pasta', [
            'high' => [26, 'potato gnocchi, pecorino romano, black pepper, butter', 2, 'Gnocchi with a potato-skin broth reduction and aged pecorino.'],
        ]],

        // mains
        ['Wild Turbot, Beurre Blanc and Caviar', 'main', 'fish', [
            'high' => [58, 'turbot, beurre blanc, caviar, leek', 2, 'Roasted turbot on the bone, beurre blanc with caviar and charred leek.'],
        ]],
        ['Sea Bass in Salt Crust · for two', 'main', 'fish', [
            'high' => [78, 'sea bass, sea salt, lemon, rosemary', 1, 'A whole wild sea bass baked in salt and filleted at the table.'],
        ], ['serves' => 2]],
        ['Lobster Catalan Style', 'main', 'seafood', [
            'high' => [64, 'lobster, tomato, red onion, celery, extra virgin olive oil', 1, 'Whole Mediterranean lobster with raw tomato, red onion and celery.'],
        ], ['season' => [5, 9]]],
        ['Veal Fillet, Morels and Marsala', 'main', 'meat', [
            'high' => [46, 'veal, mushrooms, marsala, butter', 1, 'Rosé veal fillet with morels in a Marsala reduction.'],
        ]],
        ['Pigeon, Beetroot and Cocoa', 'main', 'finedining', [
            'high' => [48, 'pigeon, beetroot, cocoa powder, red wine reduction', 1, 'Pigeon breast roasted pink, its leg confit, beetroot and a bitter cocoa jus.'],
        ]],
        ['Lamb, Aubergine and Mint', 'main', 'meat', [
            'high' => [44, 'lamb, aubergine, mint, yogurt', 1, 'Rack of milk-fed lamb, smoked aubergine and mint yogurt.'],
        ]],
        ['Dry-Aged Rib-Eye 60 days · for two', 'main', 'steak', [
            'high' => [120, 'rib-eye steak, sea salt, black pepper, butter', 1, 'One kilogram rib-eye dry-aged sixty days, cooked over embers.'],
        ], ['serves' => 2]],
        ['Venison, Juniper and Red Cabbage', 'main', 'meat', [
            'high' => [48, 'venison, juniper berries, red cabbage, red wine reduction', 1, 'Saddle of venison with juniper, braised red cabbage and wine sauce.'],
        ], ['season' => [10, 2]]],
        ['Guinea Fowl, Chestnuts and Savoy Cabbage', 'main', 'meat', [
            'high' => [40, 'guinea fowl, chestnuts, cabbage, butter', 1, 'Guinea fowl breast, chestnut purée and buttered cabbage.'],
        ], ['season' => [10, 2]]],
        ['Wagyu Sirloin, Smoked Potato', 'main', 'steak', [
            'high' => [95, 'wagyu beef, potato, butter, maldon salt', 1, 'A5 wagyu sirloin seared in seconds, with a smoked potato purée.'],
        ]],
        ['Cheese Trolley · selection of five', 'side', 'antipasti', [
            'high' => [24, 'gorgonzola, taleggio, parmigiano reggiano, goat cheese, honey', 3, 'Five cheeses chosen from the trolley with honey and mostarda.'],
        ]],

        // pastry
        ['Tiramisù, Our Way', 'dessert', 'tiramisu', [
            'high' => [18, 'mascarpone, espresso, savoiardi biscuits, cocoa powder, egg', 2, 'Mascarpone mousse, espresso granita and a cocoa tuile: a deconstructed tiramisù.'],
        ]],
        ['Gianduja, Hazelnut and Salt', 'dessert', 'chocolate', [
            'high' => [18, 'milk chocolate, hazelnuts, maldon salt, cream', 2, 'Gianduja cremeux with caramelised Piedmont hazelnuts and flaked salt.'],
        ]],
        ['Lemon from the Amalfi Coast', 'dessert', 'dessert', [
            'high' => [17, 'lemon, lemon curd, meringue, basil', 2, 'Lemon in five textures with basil and a soft meringue.'],
        ]],
        ['Dark Chocolate Soufflé', 'dessert', 'dessert', [
            'high' => [18, 'dark chocolate, egg, sugar, butter, vanilla gelato', 2, 'Baked to order (fifteen minutes) with vanilla gelato.'],
        ]],
        ['Mille-feuille with Chantilly', 'dessert', 'pastry', [
            'high' => [16, 'puff pastry, chantilly cream, vanilla, icing sugar', 2, 'Caramelised puff pastry layered with vanilla Chantilly.'],
        ]],
        ['Petits Fours', 'dessert', 'pastry', [
            'high' => [12, 'dark chocolate, almonds, pistachios, sugar, butter', 3, 'Small pastries and pralines to end the meal.'],
        ]],
        ['Seasonal Fruit and Sorbet', 'dessert', 'gelato', [
            'high' => [14, 'mixed berries, strawberry sorbet, mint', 1, 'Fruit at its best with a sorbet made the same day.'],
        ], ['season' => [5, 9]]],
    ],
];
