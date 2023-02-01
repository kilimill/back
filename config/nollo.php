<?php

return [
    'per_page' => 9,
    'hotel_new_in_days' => env('NOLLO_HOTEL_NEW_IN_DAYS'),
    'chunk' => 500,
    'default_search_location' => env('NOLLO_DEFAULT_SEARCH_LOCATION', ''),
    'seed' => [
        'minimal' => env('NOLLO_SEED_MINIMAL', true),
        'cities' => env('NOLLO_SEED_MINIMAL_CITIES', ''),
    ],
    'smsc' => [
        'login' =>  env('SMSC_LOGIN'),
        'password' =>  env('SMSC_PASSWORD'),
        // TODO remove
        'debug' =>  env('SMSC_DEBUG', 0),
        'sender' =>  env('SMSC_SENDER'),
        'confirmation_type' =>  env('SMSC_CONFIRMATION_TYPE', 'test'),
    ],
];
