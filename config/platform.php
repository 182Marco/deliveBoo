<?php

// Platform backoffice (/platform) settings. See dev/PIANO_AUTH_CLIENTI.md, decision D5.
return [
    // SIMULATED commission of the platform on the value of every completed order. Orders store no
    // commission, so the platform revenue is an estimate computed in the queries (never saved) and
    // always labelled as such. 25% is mid-range for a delivery marketplace that also delivers
    // (roughly 15-30% of the food subtotal). Changing it recomputes the whole history.
    'commission_rate' => (float) env('PLATFORM_COMMISSION_RATE', 0.25),

    // seconds the aggregates of a filter combination stay cached (file cache)
    'cache_seconds' => (int) env('PLATFORM_CACHE_SECONDS', 600),
];
