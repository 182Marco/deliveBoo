<?php

// Korean restaurants: barbecue, home cooking and fried chicken.
return [
    'types' => ['Korean'],
    'concepts' => [
        ['Seoul Kitchen', 'ethnic', 'Korean home cooking: bibimbap in hot stone bowls, kimchi stew and banchan made in house.'],
        ['Chimaek Fried Chicken', 'budget', 'Korean fried chicken twice-fried and glazed, with beer: the chimaek tradition.'],
        ['Hanok BBQ House', 'ethnic', 'Korean barbecue sets to finish at home, marinated meats and a dozen side dishes.'],
    ],
    'recipes' => [
        ['Bibimbap', 'main', 'korean', [
            'mid' => [[14, 'jasmine rice, beef, egg, spinach, carrot, bean sprouts, gochujang', 3, 'Rice topped with marinated beef, vegetables and a fried egg, mixed with gochujang.'],
                      [15, 'jasmine rice, tofu, shiitake mushrooms, spinach, carrot, zucchini, gochujang', 3, 'Vegetarian bibimbap with tofu and shiitake, served in a sizzling stone bowl.']],
        ]],
        ['Bulgogi', 'main', 'meat', [
            'mid' => [17, 'beef, soy sauce, pear, garlic, sesame oil, onion', 2, 'Thin beef marinated in soy and pear, seared with onions.'],
        ]],
        ['Samgyeopsal BBQ Set · for two', 'main', 'korean', [
            'mid' => [34, 'pork belly, lettuce, garlic, kimchi, gochujang, jasmine rice', 2, 'Thick pork belly to grill, with lettuce wraps, ssamjang and kimchi, for two.'],
        ], ['serves' => 2]],
        ['Kimchi Jjigae', 'soup', 'soup', [
            'mid' => [13, 'kimchi, pork belly, tofu, gochugaru, spring onion', 2, 'Bubbling kimchi stew with pork and tofu.'],
        ]],
        ['Sundubu Jjigae', 'soup', 'soup', [
            'mid' => [13, 'silken tofu, clams, egg, gochugaru, spring onion', 2, 'Soft tofu stew with clams and an egg cracked in at the table.'],
        ]],
        ['Japchae', 'main', 'noodles', [
            'mid' => [12, 'glass noodles, beef, spinach, carrot, shiitake mushrooms, sesame oil', 2, 'Sweet potato glass noodles stir-fried with vegetables and beef.'],
        ]],
        ['Tteokbokki', 'starter', 'korean', [
            'base' => [8, 'rice flour, gochujang, fish sauce, spring onion, egg', 2, 'Chewy rice cakes in sweet-spicy gochujang sauce.'],
        ]],
        ['Kimchi Pancake', 'starter', 'korean', [
            'mid' => [9, 'kimchi, wheat flour, spring onion, egg', 2, 'Crisp pancake with fermented kimchi.'],
        ]],
        ['Yangnyeom Fried Chicken', 'main', 'chicken', [
            'base' => [[13, 'chicken wings, rice flour, gochujang, honey, garlic, sesame seeds', 2, 'Double-fried wings glazed in sweet and spicy sauce.'],
                       [14, 'chicken thighs, rice flour, soy sauce, garlic, honey', 2, 'Boneless soy-garlic fried chicken.']],
        ]],
        ['Half-and-Half Chicken Box', 'main', 'chicken', [
            'base' => [24, 'chicken wings, chicken thighs, rice flour, gochujang, soy sauce, garlic', 3, 'Half spicy, half soy-garlic: the sharing box for two.'],
        ], ['serves' => 2]],
        ['Kimchi', 'side', 'korean', [
            'base' => [3.5, 'kimchi', 1, 'House-fermented napa cabbage kimchi.'],
        ]],
        ['Banchan Selection', 'side', 'korean', [
            'mid' => [8, 'vegan kimchi, spinach, bean sprouts, radish, sesame seeds', 3, 'Five vegetable side dishes, changing daily.'],
        ]],
        ['Bingsu', 'dessert', 'gelato', [
            'mid' => [8, 'milk, red bean paste, mochi dough, strawberries', 2, 'Shaved milk ice with red bean, mochi and fruit.'],
        ], ['season' => [5, 9]]],
        ['Hotteok', 'dessert', 'dessert', [
            'mid' => [5, 'wheat flour, brown sugar, cinnamon, peanuts', 2, 'Pan-fried pancake filled with brown sugar, cinnamon and nuts.'],
        ]],
    ],
];
