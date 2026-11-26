<?php

/*
|--------------------------------------------------------------------------
| PNP offices
|--------------------------------------------------------------------------
|
| The office an account belongs to (users.region): the 18 Police Regional
| Offices by their official short names, plus National Headquarters. One list
| for registration, profiles and staff accounts. PRO NIR (Negros Island
| Region) was activated on 27 March 2025 and covers Negros Occidental, Negros
| Oriental, Siquijor and Bacolod City.
|
| When a name changes, keep the old one recognised in App\Support\PnpRegions
| and add a migration that maps saved values (see
| 2026_11_26_000002_use_official_pnp_regional_office_names).
|
*/

return [

    'regions' => [
        'NCRPO - National Capital Region Police Office',
        'PRO 1 - Ilocos Region',
        'PRO 2 - Cagayan Valley',
        'PRO 3 - Central Luzon',
        'PRO 4A - CALABARZON',
        'PRO 4B - MIMAROPA',
        'PRO 5 - Bicol Region',
        'PRO 6 - Western Visayas',
        'PRO 7 - Central Visayas',
        'PRO 8 - Eastern Visayas',
        'PRO 9 - Zamboanga Peninsula',
        'PRO 10 - Northern Mindanao',
        'PRO 11 - Davao Region',
        'PRO 12 - SOCCSKSARGEN',
        'PRO 13 - Caraga',
        'PRO BAR - Bangsamoro Autonomous Region in Muslim Mindanao',
        'PRO COR - Cordillera Administrative Region',
        'PRO NIR - Negros Island Region',
        'NHQ Camp Crame - PNP National Headquarters',
    ],

];
