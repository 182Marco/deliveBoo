<?php

// Places of the synthetic catalog: the canton of Zurich and its immediate surroundings (Foodsy is
// pretended to serve that area only). Real place names with canton, postal code(s), phone prefix,
// approximate centre and neighbourhoods. Street names are generic and numbers invented.
// [canton, postal code or list of codes, phone prefix, latitude, longitude, [neighbourhoods], weight]
return [
    'cities' => [
        'Zürich' => ['ZH', ['8001', '8002', '8003', '8004', '8005', '8006', '8008', '8032', '8037', '8044', '8045', '8046', '8048', '8050', '8051', '8052'], '044', 47.3769, 8.5417,
            ['Altstadt', 'Langstrasse', 'Oerlikon', 'Seefeld', 'Wipkingen', 'Höngg', 'Enge', 'Wiedikon', 'Hottingen', 'Albisrieden', 'Schwamendingen', 'Zürich West'], 40],
        'Winterthur' => ['ZH', '8400', '052', 47.4988, 8.7237, ['Altstadt', 'Töss', 'Veltheim', 'Seen', 'Oberwinterthur'], 8],
        'Uster' => ['ZH', '8610', '044', 47.3471, 8.7208, ['Zentrum', 'Nänikon'], 3],
        'Dübendorf' => ['ZH', '8600', '044', 47.3970, 8.6186, ['Zentrum', 'Gfenn'], 3],
        'Dietikon' => ['ZH', '8953', '044', 47.4044, 8.4003, ['Zentrum', 'Limmatfeld'], 3],
        'Schlieren' => ['ZH', '8952', '044', 47.3967, 8.4476, ['Zentrum', 'Geissweid'], 2],
        'Wetzikon' => ['ZH', '8620', '044', 47.3267, 8.7978, ['Zentrum'], 2],
        'Horgen' => ['ZH', '8810', '044', 47.2595, 8.5977, ['Zentrum', 'Oberdorf'], 2],
        'Küsnacht' => ['ZH', '8700', '044', 47.3188, 8.5833, ['Zentrum', 'Goldbach'], 2],
        'Kloten' => ['ZH', '8302', '044', 47.4515, 8.5846, ['Zentrum'], 2],
        'Opfikon' => ['ZH', '8152', '044', 47.4328, 8.5755, ['Glattbrugg', 'Glattpark'], 2],
        'Bülach' => ['ZH', '8180', '044', 47.5207, 8.5410, ['Zentrum'], 2],
        'Adliswil' => ['ZH', '8134', '044', 47.3108, 8.5242, ['Zentrum'], 1],
        'Thalwil' => ['ZH', '8800', '044', 47.2927, 8.5658, ['Zentrum'], 1],
        'Wädenswil' => ['ZH', '8820', '044', 47.2287, 8.6692, ['Zentrum'], 1],
        'Meilen' => ['ZH', '8706', '044', 47.2699, 8.6446, ['Zentrum'], 1],
        'Regensdorf' => ['ZH', '8105', '044', 47.4340, 8.4679, ['Zentrum', 'Watt'], 1],
        'Volketswil' => ['ZH', '8604', '044', 47.3894, 8.6925, ['Zentrum'], 1],
        'Baden' => ['AG', '5400', '056', 47.4733, 8.3064, ['Altstadt', 'Kappelerhof'], 2],
        'Zug' => ['ZG', '6300', '041', 47.1724, 8.5176, ['Altstadt', 'Guthirt'], 2],
    ],
    'streets' => [
        'Bahnhofstrasse', 'Langstrasse', 'Hardstrasse', 'Seestrasse', 'Birmensdorferstrasse', 'Badenerstrasse', 'Rämistrasse',
        'Dufourstrasse', 'Hauptstrasse', 'Dorfstrasse', 'Schulstrasse', 'Zürichstrasse', 'Bergstrasse', 'Industriestrasse',
        'Gartenstrasse', 'Kirchgasse', 'Marktgasse', 'Poststrasse', 'Stationsstrasse', 'Thurgauerstrasse', 'Schaffhauserstrasse',
        'Winterthurerstrasse', 'Albisstrasse', 'Limmatstrasse', 'Hohlstrasse', 'Josefstrasse', 'Militärstrasse', 'Bellerivestrasse',
        'Förrlibuckstrasse', 'Kanzleistrasse', 'Rosengartenstrasse', 'Tièchestrasse', 'Zollikerstrasse', 'Obere Zäune', 'Löwenstrasse', 'Weinbergstrasse',
    ],
];
