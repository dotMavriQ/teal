<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Available Themes
    |--------------------------------------------------------------------------
    |
    | Define all available themes here. Each theme needs:
    | - name: Display name for the UI
    | - value: CSS data-theme attribute value
    |
    | To add a new theme:
    | 1. Add the theme definition here
    | 2. Add the CSS variables in resources/css/app.css
    |
    */

    'available' => [
        [
            'name' => 'Normie',
            'value' => 'normie',
            'description' => 'Clean light theme',
        ],
        [
            'name' => 'Gruvbox Dark',
            'value' => 'gruvbox-dark',
            'description' => 'Retro groove dark theme',
        ],
    ],

    'default' => 'normie',
];
