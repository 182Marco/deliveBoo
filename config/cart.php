<?php

// Persistent cart (dev/PIANO_CARRELLO.md).
return [
    // true (default): the cart lives in the database. false = rollback switch: the /api/cart routes answer 404
    // and the site keeps the cart in localStorage, as before (dev/PIANO_CARRELLO.md)
    'server' => (bool) env('CART_SERVER', true),

    // cookie holding the anonymous id of a guest (random token; the database keeps only its SHA-256)
    'cookie' => 'foodsy_cart',
    // renewed at every cart request; 400 days is the longest lifetime browsers accept
    'cookie_minutes' => 400 * 24 * 60,

    // portions of one plate, and of the whole cart (same limit as POST /api/orders)
    'max_quantity' => 20,
    'max_portions' => 100,
];
