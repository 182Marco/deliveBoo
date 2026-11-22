<?php

// Italian cities of the synthetic catalog: real city names with province, postal code, phone
// prefix, approximate centre and neighbourhoods. Street names are generic and numbers invented.
// [province, postal code, phone prefix, latitude, longitude, [neighbourhoods], weight]
return [
    'cities' => [
        'Milano' => ['MI', '20121', '02', 45.4642, 9.1900, ['Navigli', 'Brera', 'Isola', 'Porta Romana', 'Città Studi', 'NoLo', 'Sempione', 'Porta Venezia'], 10],
        'Roma' => ['RM', '00184', '06', 41.9028, 12.4964, ['Trastevere', 'Monti', 'Prati', 'Testaccio', 'Pigneto', 'San Lorenzo', 'Parioli', 'Ostiense'], 10],
        'Torino' => ['TO', '10121', '011', 45.0703, 7.6869, ['San Salvario', 'Vanchiglia', 'Crocetta', 'Centro', 'Lingotto'], 5],
        'Bologna' => ['BO', '40121', '051', 44.4949, 11.3426, ['Centro Storico', 'Bolognina', 'Santo Stefano', 'Saragozza'], 5],
        'Firenze' => ['FI', '50122', '055', 43.7696, 11.2558, ['Santo Spirito', 'Sant\'Ambrogio', 'Campo di Marte', 'Novoli'], 5],
        'Napoli' => ['NA', '80134', '081', 40.8518, 14.2681, ['Chiaia', 'Vomero', 'Quartieri Spagnoli', 'Posillipo', 'Centro Storico'], 6],
        'Venezia' => ['VE', '30121', '041', 45.4408, 12.3155, ['Cannaregio', 'Dorsoduro', 'Castello', 'Mestre'], 3],
        'Verona' => ['VR', '37121', '045', 45.4384, 10.9916, ['Centro', 'Veronetta', 'Borgo Trento'], 3],
        'Genova' => ['GE', '16121', '010', 44.4056, 8.9463, ['Centro Storico', 'Albaro', 'Boccadasse', 'Sampierdarena'], 3],
        'Bergamo' => ['BG', '24121', '035', 45.6983, 9.6773, ['Città Alta', 'Borgo Palazzo', 'Centro'], 2],
        'Brescia' => ['BS', '25121', '030', 45.5416, 10.2118, ['Centro', 'Carmine'], 2],
        'Padova' => ['PD', '35121', '049', 45.4064, 11.8768, ['Centro', 'Portello', 'Arcella'], 2],
        'Parma' => ['PR', '43121', '0521', 44.8015, 10.3279, ['Oltretorrente', 'Centro'], 2],
        'Modena' => ['MO', '41121', '059', 44.6471, 10.9252, ['Centro', 'Sacca'], 2],
        'Rimini' => ['RN', '47921', '0541', 44.0678, 12.5695, ['Marina Centro', 'Borgo San Giuliano'], 2],
        'Pisa' => ['PI', '56125', '050', 43.7228, 10.4017, ['Centro', 'San Martino'], 1],
        'Perugia' => ['PG', '06121', '075', 43.1107, 12.3908, ['Centro Storico', 'Fontivegge'], 1],
        'Bari' => ['BA', '70122', '080', 41.1171, 16.8719, ['Bari Vecchia', 'Murat', 'Poggiofranco'], 3],
        'Lecce' => ['LE', '73100', '0832', 40.3515, 18.1750, ['Centro Storico'], 1],
        'Palermo' => ['PA', '90133', '091', 38.1157, 13.3615, ['Kalsa', 'Politeama', 'Mondello', 'Ballarò'], 3],
        'Catania' => ['CT', '95131', '095', 37.5079, 15.0830, ['Centro', 'Borgo'], 2],
        'Cagliari' => ['CA', '09124', '070', 39.2238, 9.1217, ['Marina', 'Castello', 'Poetto'], 2],
        'Trento' => ['TN', '38122', '0461', 46.0748, 11.1217, ['Centro'], 1],
        'Como' => ['CO', '22100', '031', 45.8081, 9.0852, ['Centro', 'Borgo Vico'], 1],
        'Piacenza' => ['PC', '29121', '0523', 45.0526, 9.6930, ['Centro'], 1],
    ],
    'streets' => [
        'Via Garibaldi', 'Via Mazzini', 'Via Cavour', 'Corso Italia', 'Via Roma', 'Via Dante', 'Via Verdi', 'Via Manzoni',
        'Via XX Settembre', 'Corso Vittorio Emanuele', 'Via Matteotti', 'Via Marconi', 'Piazza della Repubblica', 'Via San Francesco',
        'Via dei Mille', 'Via Carducci', 'Via Leopardi', 'Via Gramsci', 'Via Oberdan', 'Via Solferino', 'Via Indipendenza',
        'Via del Corso', 'Via Nazionale', 'Via Montebello', 'Via Torino', 'Via Milano', 'Via Firenze', 'Via Pascoli', 'Via Galilei',
        'Via Volta', 'Via Petrarca', 'Via Foscolo', 'Via Tasso', 'Via Ariosto', 'Viale Europa', 'Via della Libertà',
    ],
];
