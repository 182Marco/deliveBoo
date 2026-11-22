<?php

// Indian restaurants: North Indian tandoor, South Indian and takeaway.
return [
    'types' => ['Indian'],
    'extra_types' => ['ethnic' => ['Ethnic']],
    'concepts' => [
        ['Taj Tandoor', 'ethnic', 'North Indian restaurant with a clay tandoor: kebabs, naan and slow curries from Punjab.'],
        ['Masala Express', 'budget', 'Curry house takeaway with generous portions and a spice level for everyone.'],
        ['Chennai Spice', 'ethnic', 'South Indian vegetarian specialities: dosa, idli, sambar and coconut chutneys.'],
        ['Jaipur Royal', 'premium', 'Refined Indian cuisine with royal Rajasthani recipes, a thali tasting and an aromatic wine list.', [
            ['Royal Thali', 'tasting_menu', 'indian', 38, 'basmati rice, lamb, paneer, lentils, chickpeas, naan bread, raita', 4, 'Eight small dishes, rice, bread and dessert on a silver thali.'],
        ]],
    ],
    'recipes' => [
        ['Chicken Tikka Masala', 'main', 'curry', [
            'base' => [11, 'chicken breast, tikka masala sauce, tomato, cream, garam masala', 2, 'Tandoori chicken pieces in a creamy, mildly spiced tomato sauce.'],
            'mid' => [15, 'chicken thighs, tikka masala sauce, tomato, cream, fenugreek, ginger, garlic', 2, 'Charred tandoor chicken in a buttery tomato and fenugreek gravy.'],
        ]],
        ['Butter Chicken', 'main', 'curry', [
            'mid' => [15, 'chicken thighs, butter, tomato, cream, cashews, garam masala', 2, 'Murgh makhani: chicken in a velvety butter, tomato and cashew sauce.'],
        ]],
        ['Lamb Rogan Josh', 'main', 'curry', [
            'mid' => [17, 'lamb, yogurt, tomato, onion, cardamom, paprika, ginger', 2, 'Kashmiri lamb curry with aromatic spices and a deep red colour.'],
        ]],
        ['Chicken Biryani', 'main', 'indian', [
            'base' => [12, 'basmati rice, chicken, yogurt, onion, saffron, cardamom', 2, 'Layered rice and chicken cooked dum style with saffron.'],
            'mid' => [16, 'basmati rice, lamb, yogurt, onion, saffron, mint, cardamom', 2, 'Hyderabadi lamb biryani sealed under pastry and baked.'],
        ]],
        ['Palak Paneer', 'main', 'indian', [
            'mid' => [13, 'paneer, spinach, onion, garlic, cream, cumin', 2, 'Cubes of paneer in a smooth spiced spinach sauce.'],
        ]],
        ['Chana Masala', 'main', 'curry', [
            'base' => [9, 'chickpeas, tomato, onion, ginger, garam masala, coriander', 1, 'Chickpea curry with tomato and warm spices, vegan.'],
        ]],
        ['Dal Makhani', 'main', 'curry', [
            'mid' => [12, 'lentils, kidney beans, butter, cream, tomato, garlic', 2, 'Black lentils simmered overnight with butter and cream.'],
        ]],
        ['Tarka Dal', 'main', 'curry', [
            'base' => [8.5, 'red lentils, turmeric, cumin, garlic, chilli pepper, sunflower oil', 1, 'Yellow lentils tempered with cumin and garlic, vegan.'],
        ]],
        ['Vegetable Korma', 'main', 'curry', [
            'mid' => [12, 'cauliflower, peas, carrot, cashews, cream, cardamom', 2, 'Mild vegetable curry with cashews and cream.'],
        ]],
        ['Aloo Gobi', 'main', 'indian', [
            'base' => [9, 'potato, cauliflower, turmeric, cumin, onion, coriander', 2, 'Dry-spiced potato and cauliflower, vegan.'],
        ]],
        ['Tandoori Chicken', 'main', 'chicken', [
            'mid' => [15, 'chicken, yogurt, paprika, garam masala, lemon, garlic', 1, 'Half chicken marinated in yogurt and spices, roasted in the tandoor.'],
        ]],
        ['Lamb Seekh Kebab', 'starter', 'skewers', [
            'mid' => [11, 'lamb, onion, coriander, garam masala, chilli pepper', 1, 'Minced lamb skewers cooked in the tandoor, with mint chutney.'],
        ]],
        ['Vegetable Samosa · 2 pieces', 'starter', 'indian', [
            'base' => [4.5, 'wheat flour, potato, peas, cumin, coriander, tamarind', 2, 'Crisp pastries filled with spiced potato and peas.'],
        ]],
        ['Onion Bhaji', 'starter', 'indian', [
            'base' => [5, 'onion, chickpea flour, cumin, chilli pepper, coriander', 2, 'Onion fritters in chickpea batter.'],
        ]],
        ['Masala Dosa', 'main', 'indian', [
            'mid' => [11, 'rice flour, lentils, potato, mustard seeds, curry leaves, coconut', 3, 'Thin fermented rice crêpe with spiced potato, sambar and coconut chutney.'],
        ]],
        ['Idli Sambar', 'main', 'indian', [
            'mid' => [9, 'rice flour, lentils, tamarind, carrot, curry leaves', 2, 'Steamed rice cakes with lentil and vegetable sambar.'],
        ]],
        ['Garlic Naan', 'side', 'bread', [
            'base' => [3, 'naan bread, garlic, butter, coriander', 1, 'Tandoor flatbread brushed with garlic butter.'],
        ]],
        ['Cheese Naan', 'side', 'bread', [
            'mid' => [4, 'naan bread, cheddar, coriander', 1, 'Naan stuffed with melted cheese.'],
        ]],
        ['Basmati Rice', 'side', 'asian_bowl', [
            'base' => [3, 'basmati rice, cumin', 1, 'Steamed basmati with cumin seeds.'],
        ]],
        ['Cucumber Raita', 'side', 'indian', [
            'base' => [3, 'raita, cucumber, mint', 1, 'Cooling yogurt with cucumber and mint.'],
        ]],
        ['Gulab Jamun', 'dessert', 'dessert', [
            'mid' => [5, 'milk, wheat flour, sugar, cardamom, ghee', 2, 'Milk dumplings soaked in cardamom syrup.'],
        ]],
        ['Mango Kulfi', 'dessert', 'gelato', [
            'mid' => [5, 'milk, mango, pistachios, cardamom', 2, 'Dense Indian ice cream with mango and pistachio.'],
        ]],
    ],
];
