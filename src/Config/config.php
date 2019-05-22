<?php

return [

    // enable or disable plogs.
    'enabled' => env('ENABLE_PLOGS', true),

    // route where visitlog will be available in your app.
    'route' => 'plogs',

    // set what levels of logs should be captured. It can be one or more of:
    // "debug", "info", "notice", "warning", "error", "critical",
    // "alert", "emergency", "processed". To capture all events just use "all"
    'levels' => [
        'all',
    ],

    // if "true", the PLogs page can be viewed by any user who provides
    // correct login information (eg all app users).
    'http_authentication' => false,

    // records beyond this number of days will be deleted when viewed.
    'delete_old_days' => 30
];
