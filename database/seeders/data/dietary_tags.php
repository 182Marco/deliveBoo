<?php

// Dietary characteristics of a plate. code => [name, kind, description].
// None is a certification: a "yes" is a declaration of the restaurant or, for the tags
// marked as derivable, a result computed from VERIFIED reference data of the ingredients.
return [
    'vegan' => ['Vegan', 'diet', 'No ingredients of animal origin (meat, fish, dairy, eggs, honey). Declared by the restaurant or derived from verified ingredients.'],
    'vegetarian' => ['Vegetarian', 'diet', 'No meat, fish or seafood; dairy and eggs allowed. Declared by the restaurant or derived from verified ingredients.'],
    'pescatarian' => ['Pescatarian', 'diet', 'No meat; fish and seafood allowed. Declared by the restaurant or derived from verified ingredients.'],
    'fruitarian' => ['Fruitarian', 'diet', 'Only fruit, nuts and seeds. Declared by the restaurant or derived from verified ingredients.'],
    'gluten_free' => ['Gluten free', 'free_from', 'No gluten in the recipe and no gluten cross-contact declared by the restaurant. Not a coeliac certification.'],
    'lactose_free' => ['Lactose free', 'free_from', 'No lactose in the ingredients and no milk cross-contact declared. An intolerance, not the milk allergy: the plate may still contain milk protein.'],
    'low_nickel' => ['Low nickel', 'restriction', 'Indicative dataset information from the ingredient registry: never a medical guarantee or certification of compatibility.'],
    'halal' => ['Halal', 'diet', 'Declared by the restaurant. Never derived from the ingredients.'],
    'spicy' => ['Spicy', 'restriction', 'Declared by the restaurant: noticeably hot.'],
];
