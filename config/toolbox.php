<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application environment mode that use for start
    |--------------------------------------------------------------------------

    | Supported: "dev", "prod"
    |
    */

    'mode' => env('TOOLBOX_STARTER_MODE', 'dev'),
    
    /*
    |--------------------------------------------------------------------------
    | Octane watch
    |--------------------------------------------------------------------------

    | Supported: true, false
    |
    */
    'octan_watch' => env('TOOLBOX_STARTER_OCTANE_WATCH', false),
];
