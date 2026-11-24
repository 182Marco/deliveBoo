<?php

// Inappropriate reviews of the demo dataset, on purpose: they show the moderation of the platform
// admin (/platform > Tables > Reviews > Hide, audit log, new average of the restaurant).
// ReviewsSeeder gives them to about 2% of the commented reviews, at most one per restaurant, with
// the four categories in rotation. They are seeded VISIBLE (hidden_at = null) and without a reply:
// only an admin hides them. English, like every seeded text. Never used for the normal reviews.
return [
    // swearing, insults and aggressive tones against the restaurant or its staff
    'vulgar' => [
        'What a load of crap. The guy at the counter is a complete idiot and so is whoever runs this dump.',
        'Absolute shit. Don\'t bother, these clowns couldn\'t cook to save their lives.',
        'Worst garbage I\'ve ever paid for. The staff are useless morons, end of story.',
        'Damn rip-off. The owner is a crook and the people working there are rude bastards.',
        'Pathetic. Whoever packed this is a brainless idiot. Shut the damn place down.',
        'Screw this place and its lazy, arrogant staff. Total bullshit from start to finish.',
        'The manager is a jerk and everything they make is crap. Go to hell.',
    ],

    // topics that have nothing to do with the restaurant, the food or the service
    'off_topic' => [
        'Does anyone know if the trams to the airport are running this weekend? Asking for my cousin.',
        'Selling my old road bike, barely used, great price. Message me if interested!',
        'Just moved into a new flat and the landlord still hasn\'t fixed the heating. Rant over.',
        'Crypto is going to the moon this year, everyone should buy now before it\'s too late!!!',
        'Can\'t believe the referee gave that penalty last night. Worst match of the season.',
        'My cat learned to open the fridge today. No idea what to do about it.',
        'Looking for a good dentist in the area, any recommendations?',
    ],

    // childish jokes, misplaced irony and deliberately meaningless texts
    'nonsense' => [
        'Banana banana banana. The moon told me to write this.',
        'Why did the chicken cross the road? To avoid reading this review. Ha ha ha.',
        'Blorp. Zibble wobble. That is all I have to say.',
        'If you read this backwards it still makes no sense. tnemmoc siht ekil.',
        'My left sock gives this three stars, my right sock strongly disagrees.',
        'Knock knock. Who\'s there? Not my motivation to write a real review.',
        'I typed this with my elbows while balancing a spoon on my nose. Nailed it.',
    ],

    // customers telling how they flirted with waiters, cashiers or riders, not about the service
    'flirting' => [
        'Honestly I only ordered to see the cute rider again. Gave him my number this time, fingers crossed!',
        'The girl at the counter has the most beautiful smile. I asked her out when I picked up my last order. Still waiting for an answer!',
        'Came for the food, stayed for the handsome guy at the till. I\'ve ordered five times this week just to chat with him.',
        'Shout-out to the delivery guy with the green helmet: you have amazing eyes. Call me!',
        'I left a note in the order asking the chef out on a date. No reply yet, but I\'m not giving up.',
        'The new cashier is gorgeous. I wrote my phone number on the receipt, hope she sees it.',
        'I keep hoping for the same rider because we flirt a little at the door every time. Best part of my week.',
    ],
];
