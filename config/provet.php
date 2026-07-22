<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Provet Connection
    |--------------------------------------------------------------------------
    |
    | The connection used when none is specified explicitly, e.g. via
    | Provet::get(...) rather than Provet::connection('5200')->get(...).
    |
    */

    'default' => env('PROVET_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Provet Connections
    |--------------------------------------------------------------------------
    |
    | Each connection is one Provet Cloud organization (tenant). The array
    | key doubles as its `provet_id` (the organization id in the API URL)
    | unless a `provet_id` key overrides it, so a connection named "5200"
    | talks to https://provetcloud.com/5200/api/0.1/ by default.
    |
    | Add one entry per tenant to support multiple organizations at once,
    | e.g.:
    |
    |   'connections' => [
    |       '5200' => [
    |           'client_id' => env('PROVET_API_5200_ID'),
    |           'client_secret' => env('PROVET_API_5200_SECRET'),
    |       ],
    |       '5444' => [
    |           'client_id' => env('PROVET_API_5444_ID'),
    |           'client_secret' => env('PROVET_API_5444_SECRET'),
    |       ],
    |   ],
    |
    | Unknown keys are ignored, so app-specific per-tenant settings can be
    | stored alongside these without conflicting with the package.
    |
    */

    'connections' => [
        'default' => [
            'provet_id' => env('PROVET_ID'),
            'client_id' => env('PROVET_CLIENT_ID'),
            'client_secret' => env('PROVET_CLIENT_SECRET'),
            'domain' => env('PROVET_DOMAIN', 'provetcloud.com'),

            // Cache successful GET responses (does not apply to writes).
            'use_cache' => env('PROVET_USE_CACHE', false),
            'cache_ttl' => env('PROVET_CACHE_TTL', 3600),

            // OAuth2 access tokens are always cached, for this long.
            'token_ttl' => env('PROVET_TOKEN_TTL', 3600),

            // HTTP timeout (seconds) and retry behaviour for failed requests.
            'timeout' => env('PROVET_TIMEOUT', 60),
            'retries' => env('PROVET_RETRIES', 5),
            'retry_delay_min' => env('PROVET_RETRY_DELAY_MIN', 1),
            'retry_delay_max' => env('PROVET_RETRY_DELAY_MAX', 5),
        ],
    ],

];
