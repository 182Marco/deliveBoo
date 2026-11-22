<?php

// Mexican restaurants: taquerías and cantinas.
return [
    'types' => ['Mexican'],
    'concepts' => [
        ['Taquería La Güera', 'budget', 'Taquería with corn tortillas pressed to order, al pastor from the trompo and five house salsas.'],
        ['Cantina Oaxaca', 'ethnic', 'Mexican cantina with moles from Oaxaca, slow-cooked barbacoa and a mezcal list.'],
        ['El Burrito Loco', 'fast_food', 'Burritos, bowls and nachos built your way, fast.'],
    ],
    'recipes' => [
        ['Tacos al Pastor · 3 pieces', 'main', 'tacos', [
            'base' => [9, 'corn tortilla, pork shoulder, pineapple, onion, coriander, salsa roja', 2, 'Marinated pork from the spit with pineapple, onion and coriander.'],
            'mid' => [11, 'corn tortilla, pork shoulder, chipotle, pineapple, onion, coriander, lime', 2, 'Achiote and chipotle pork shaved from the trompo, charred pineapple.'],
        ]],
        ['Tacos de Carnitas · 3 pieces', 'main', 'tacos', [
            'mid' => [11, 'corn tortilla, pork shoulder, onion, coriander, lime, salsa roja', 2, 'Pork confit in its own fat, crisped on the plancha.'],
        ]],
        ['Fish Tacos Baja Style · 3 pieces', 'main', 'tacos', [
            'mid' => [12, 'corn tortilla, cod, wheat flour, cabbage, sour cream, lime', 2, 'Beer-battered fish with shredded cabbage and lime crema.'],
        ]],
        ['Mushroom and Black Bean Tacos · 3 pieces', 'main', 'tacos', [
            'base' => [8.5, 'corn tortilla, mushrooms, black beans, guacamole, pico de gallo', 3, 'Vegan tacos with roasted mushrooms, beans and guacamole.'],
        ]],
        ['Beef Burrito', 'main', 'mexican', [
            'base' => [[10, 'flour tortilla, ground beef, black beans, basmati rice, cheddar, sour cream, pico de gallo', 3, 'Big flour tortilla with spiced beef, rice, beans and cheese.'],
                       [9.5, 'flour tortilla, chicken, pinto beans, basmati rice, guacamole, salsa roja', 3, 'Chipotle chicken burrito with guacamole.']],
        ]],
        ['Veggie Burrito Bowl', 'main', 'vegan_bowl', [
            'base' => [9.5, 'basmati rice, black beans, sweetcorn, guacamole, pico de gallo, lettuce', 3, 'Rice, beans, corn, guacamole and salsa, no tortilla, vegan.'],
        ]],
        ['Quesadilla', 'main', 'mexican', [
            'base' => [8, 'flour tortilla, monterey jack, chicken, jalapeño', 2, 'Grilled tortilla with melted cheese, chicken and jalapeño.'],
        ]],
        ['Enchiladas Verdes', 'main', 'mexican', [
            'mid' => [13, 'corn tortilla, chicken, tomato salsa, queso fresco, sour cream', 2, 'Rolled tortillas with chicken in tomatillo salsa, baked with queso fresco.'],
        ]],
        ['Mole Poblano', 'main', 'mexican', [
            'mid' => [17, 'turkey, mole sauce, sesame seeds, basmati rice, corn tortilla', 2, 'Turkey in dark mole of chillies, chocolate and spices.'],
        ]],
        ['Barbacoa de Res', 'main', 'meat', [
            'mid' => [16, 'beef short ribs, chipotle, cumin, corn tortilla, onion, coriander', 1, 'Beef braised for eight hours with chillies, served with tortillas.'],
        ]],
        ['Pozole Rojo', 'soup', 'soup', [
            'mid' => [12, 'pork shoulder, sweetcorn, chilli pepper, radish, cabbage, oregano', 2, 'Hominy and pork stew in red chilli broth with crunchy garnishes.'],
        ], ['season' => [10, 3]]],
        ['Guacamole and Chips', 'starter', 'mexican', [
            'base' => [6.5, 'guacamole, tortilla chips, lime', 1, 'Avocado smashed to order with lime, onion and coriander.'],
        ]],
        ['Loaded Nachos', 'starter', 'mexican', [
            'base' => [9, 'tortilla chips, cheese sauce, black beans, jalapeño, sour cream, pico de gallo', 2, 'Tortilla chips with cheese sauce, beans and jalapeños, to share.'],
        ], ['serves' => 2]],
        ['Elote', 'side', 'side', [
            'mid' => [5.5, 'sweetcorn, mayonnaise, queso fresco, chilli flakes, lime', 1, 'Grilled corn with mayo, cheese and chilli.'],
        ], ['season' => [6, 9]]],
        ['Mexican Rice and Beans', 'side', 'mexican', [
            'base' => [4, 'basmati rice, tomato, pinto beans, cumin', 2, 'Tomato rice and stewed beans.'],
        ]],
        ['Churros with Chocolate', 'dessert', 'dessert', [
            'base' => [6, 'churros dough, sugar, cinnamon, dark chocolate', 1, 'Fried churros with cinnamon sugar and a chocolate dip.'],
        ]],
        ['Tres Leches Cake', 'dessert', 'cake', [
            'mid' => [6.5, 'sponge cake, milk, cream, cinnamon', 1, 'Sponge cake soaked in three milks.'],
        ]],
    ],
];
