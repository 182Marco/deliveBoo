<?php

// Customer reviews (stars 1-5 + optional text), written from "My orders" for a delivered order.
return [
    // days after the delivery during which the customer can write or edit the review
    'window_days' => (int) env('REVIEWS_WINDOW_DAYS', 30),

    // maximum length of the comment and of the restaurateur's reply
    'comment_max' => 1000,

    // Bayesian ranking of "Guest favourites" (restaurants.rating_score, never shown):
    // score = (prior_weight × prior_mean + sum of the stars) / (prior_weight + number of reviews).
    // A place with few reviews stays near prior_mean until it collects more.
    'prior_weight' => 10,
    'prior_mean' => 3.8,
];
