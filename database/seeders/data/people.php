<?php

// Synthetic people for owners and customers: common Italian first and last names, combined
// deterministically. Every email uses the reserved example.com / example.net / example.org domains.
return [
    'first' => [
        'Giulia', 'Marco', 'Francesca', 'Luca', 'Chiara', 'Andrea', 'Sara', 'Matteo', 'Elena', 'Alessandro',
        'Valentina', 'Davide', 'Martina', 'Simone', 'Federica', 'Stefano', 'Silvia', 'Paolo', 'Alessia', 'Giorgio',
        'Laura', 'Riccardo', 'Elisa', 'Lorenzo', 'Anna', 'Fabio', 'Roberta', 'Nicola', 'Ilaria', 'Daniele',
        'Beatrice', 'Emanuele', 'Camilla', 'Tommaso', 'Serena', 'Pietro', 'Marta', 'Gabriele', 'Veronica', 'Filippo',
        'Monica', 'Claudio', 'Irene', 'Massimo', 'Arianna', 'Enrico', 'Debora', 'Vincenzo', 'Caterina', 'Antonio',
        'Rosa', 'Salvatore', 'Noemi', 'Giuseppe', 'Margherita', 'Edoardo', 'Lucia', 'Michele', 'Greta', 'Raffaele',
        'Amina', 'Karim', 'Mei', 'Hiro', 'Sofia', 'Diego', 'Fatou', 'Rahul', 'Yasmin', 'Tomás',
    ],
    'last' => [
        'Rossi', 'Russo', 'Ferrari', 'Esposito', 'Bianchi', 'Romano', 'Colombo', 'Ricci', 'Marino', 'Greco',
        'Bruno', 'Gallo', 'Conti', 'De Luca', 'Mancini', 'Costa', 'Giordano', 'Rizzo', 'Lombardi', 'Moretti',
        'Barbieri', 'Fontana', 'Santoro', 'Mariani', 'Rinaldi', 'Caruso', 'Ferrara', 'Galli', 'Martini', 'Leone',
        'Longo', 'Gentile', 'Martinelli', 'Vitale', 'Lombardo', 'Serra', 'Coppola', 'De Santis', 'D\'Angelo', 'Marchetti',
        'Parisi', 'Villa', 'Conte', 'Ferraro', 'Ferri', 'Fabbri', 'Bianco', 'Marini', 'Grasso', 'Valentini',
        'Messina', 'Sala', 'De Angelis', 'Gatti', 'Pellegrini', 'Palumbo', 'Sanna', 'Farina', 'Rizzi', 'Monti',
        'Cattaneo', 'Morelli', 'Amato', 'Silvestri', 'Mazza', 'Testa', 'Grassi', 'Pellegrino', 'Carbone', 'Giuliani',
        'Benedetti', 'Barone', 'Rossetti', 'Caputo', 'Montanari', 'Guerra', 'Palmieri', 'Bernardi', 'Martino', 'Fiore',
    ],
    'company' => ['{last} Ristorazione S.r.l.', '{last} Food S.r.l.', 'Gruppo {last} S.p.A.', '{last} & Partners S.r.l.', 'Cucine {last} S.r.l.s.', '{last} Hospitality S.r.l.'],
    'domains' => ['example.com', 'example.net', 'example.org'],
];
