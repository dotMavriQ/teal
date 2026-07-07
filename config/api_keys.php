<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Instance key policy
    |--------------------------------------------------------------------------
    |
    | Controls how per-user keys interact with the instance-level keys in
    | config/services.php (.env). The admin sets this via API_KEYS_POLICY:
    |
    |   both   - user key overrides, .env is the fallback (default)
    |   shared - .env only; user keys are ignored and the settings UI is hidden
    |   byo    - "bring your own"; only user keys are used, no .env fallback
    |
    */

    'policy' => env('API_KEYS_POLICY', 'both'),

    /*
    |--------------------------------------------------------------------------
    | Supported credentials
    |--------------------------------------------------------------------------
    |
    | Each field maps a credential slug (stored in user_api_keys.service) to the
    | config/services.php path it overrides. Fields are grouped by service purely
    | for display. OpenLibrary and Jikan need no keys, so they are absent.
    |
    */

    'services' => [
        'tmdb' => [
            'label' => 'TMDB',
            'description' => 'Movies & TV metadata',
            'fields' => [
                'tmdb_access_token' => [
                    'label' => 'API Read Access Token (v4)',
                    'config' => 'services.tmdb.access_token',
                ],
                'tmdb_api_key' => [
                    'label' => 'API Key (v3, optional fallback)',
                    'config' => 'services.tmdb.api_key',
                ],
            ],
        ],
        'comic_vine' => [
            'label' => 'ComicVine',
            'description' => 'Comics metadata',
            'fields' => [
                'comic_vine_api_key' => [
                    'label' => 'API Key',
                    'config' => 'services.comic_vine.api_key',
                ],
            ],
        ],
        'igdb' => [
            'label' => 'IGDB',
            'description' => 'Games metadata (Twitch OAuth)',
            'fields' => [
                'igdb_client_id' => [
                    'label' => 'Client ID',
                    'config' => 'services.igdb.client_id',
                ],
                'igdb_client_secret' => [
                    'label' => 'Client Secret',
                    'config' => 'services.igdb.client_secret',
                ],
            ],
        ],
        'bgg' => [
            'label' => 'BoardGameGeek',
            'description' => 'Board games metadata',
            'fields' => [
                'bgg_api_token' => [
                    'label' => 'API Token',
                    'config' => 'services.bgg.api_token',
                ],
            ],
        ],
        'discogs' => [
            'label' => 'Discogs',
            'description' => 'Albums metadata',
            'fields' => [
                'discogs_token' => [
                    'label' => 'Token',
                    'config' => 'services.discogs.token',
                ],
            ],
        ],
        'setlistfm' => [
            'label' => 'Setlist.fm',
            'description' => 'Concerts & setlists',
            'fields' => [
                'setlistfm_api_key' => [
                    'label' => 'API Key',
                    'config' => 'services.setlistfm.api_key',
                ],
            ],
        ],
        'trakt' => [
            'label' => 'Trakt',
            'description' => 'Movie/TV fallback metadata',
            'fields' => [
                'trakt_client_id' => [
                    'label' => 'Client ID',
                    'config' => 'services.trakt.client_id',
                ],
            ],
        ],
    ],

];
