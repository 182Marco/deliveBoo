<?php

// Ramen shops and izakaya.
return [
    'types' => ['Japanese', 'Ramen'],
    'concepts' => [
        ['Ramen Kaze', 'ethnic', 'Ramen shop with broths simmered eighteen hours and noodles made in house every morning.'],
        ['Mendokoro Tanuki', 'budget', 'Quick-service ramen counter: four broths, gyoza and karaage, ready in ten minutes.'],
        ['Izakaya Yoru', 'ethnic', 'Japanese pub food: yakitori from the binchotan grill, small plates, ramen to close and a sake list.'],
    ],
    'recipes' => [
        ['Tonkotsu Ramen', 'ramen', 'ramen', [
            'base' => [12, 'ramen noodles, tonkotsu broth, pork belly, soft-boiled egg, spring onion, nori', 3, 'Rich pork bone broth with chashu belly, ajitama egg and nori.'],
            'mid' => [15, 'ramen noodles, tonkotsu broth, pork belly, soft-boiled egg, black garlic, bamboo shoots, nori', 3, 'Eighteen-hour pork broth with black garlic oil, chashu and menma.'],
        ]],
        ['Shoyu Ramen', 'ramen', 'ramen', [
            'base' => [11, 'ramen noodles, shoyu broth, chicken, spring onion, bamboo shoots', 3, 'Clear chicken and soy broth with sliced chicken.'],
            'mid' => [14, 'ramen noodles, shoyu broth, duck breast, soft-boiled egg, leek', 3, 'Clear duck and soy broth with seared duck breast and leek.'],
        ]],
        ['Miso Ramen', 'ramen', 'ramen', [
            'mid' => [14, 'ramen noodles, miso broth, ground beef, sweetcorn, butter, bean sprouts', 3, 'Sapporo-style miso broth with minced pork, corn and a knob of butter.'],
        ]],
        ['Spicy Tantanmen', 'ramen', 'ramen', [
            'mid' => [15, 'ramen noodles, chicken stock, ground beef, sesame seeds, chilli oil, bok choy', 3, 'Sesame and chilli broth with spicy minced meat and bok choy.'],
        ]],
        ['Vegan Shiitake Ramen', 'ramen', 'ramen', [
            'base' => [12, 'ramen noodles, vegan ramen broth, tofu, shiitake mushrooms, spring onion', 3, 'Kombu and shiitake broth with grilled tofu.'],
            'mid' => [14, 'ramen noodles, vegan ramen broth, smoked tofu, shiitake mushrooms, bok choy, sesame oil', 3, 'Umami-rich vegetable broth, smoked tofu and toasted sesame oil.'],
        ]],
        ['Tsukemen', 'ramen', 'ramen', [
            'mid' => [16, 'ramen noodles, tonkotsu broth, bonito flakes, pork belly, soft-boiled egg', 3, 'Thick cold noodles to dip in a concentrated pork and fish broth.'],
        ]],
        ['Gyoza · 6 pieces', 'starter', 'dumplings', [
            'base' => [6, 'dumpling wrappers, pork, cabbage, garlic, soy sauce', 2, 'Crispy-bottomed pork gyoza.'],
        ]],
        ['Chicken Karaage', 'starter', 'chicken', [
            'base' => [7, 'chicken thighs, soy sauce, ginger, rice flour, kewpie mayonnaise', 2, 'Japanese fried chicken with ginger and Kewpie mayo.'],
        ]],
        ['Takoyaki · 6 pieces', 'starter', 'streetfood', [
            'mid' => [7.5, 'octopus, wheat flour, egg, tonkatsu sauce, kewpie mayonnaise, bonito flakes', 3, 'Octopus balls with okonomi sauce, mayo and dancing bonito.'],
        ]],
        ['Pork Bao · 2 pieces', 'starter', 'sandwich', [
            'mid' => [8, 'bao bun, pork belly, hoisin sauce, cucumber, peanuts', 2, 'Steamed buns with braised pork belly and crushed peanuts.'],
        ]],
        ['Yakitori Selection · 5 skewers', 'main', 'skewers', [
            'mid' => [14, 'chicken thighs, chicken liver, spring onion, yakitori sauce, sea salt', 2, 'Thigh, liver, skin and negima skewers from the charcoal grill.'],
        ]],
        ['Agedashi Tofu', 'starter', 'asian_bowl', [
            'mid' => [7, 'silken tofu, rice flour, dashi, daikon, spring onion', 1, 'Fried silken tofu in warm dashi.'],
        ]],
        ['Chashu Don', 'main', 'asian_bowl', [
            'base' => [10, 'jasmine rice, pork belly, soft-boiled egg, spring onion, teriyaki sauce', 2, 'Rice bowl with torched chashu pork.'],
        ]],
        ['Katsu Curry', 'main', 'curry', [
            'mid' => [14, 'pork, panko, egg, jasmine rice, curry powder, carrot, potato', 2, 'Breaded pork cutlet with Japanese curry sauce and rice.'],
        ]],
        ['Matcha Ice Cream', 'dessert', 'gelato', [
            'base' => [4.5, 'ice cream base, matcha', 2, 'Green tea ice cream.'],
        ]],
        ['Black Sesame Panna Cotta', 'dessert', 'dessert', [
            'mid' => [6, 'cream, black sesame seeds, sugar, gelatin', 2, 'Silky panna cotta flavoured with roasted black sesame.'],
        ]],
    ],
];
