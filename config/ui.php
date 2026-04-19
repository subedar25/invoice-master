<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default UI theme (public folder under public/)
    |--------------------------------------------------------------------------
    |
    | Used for new organization forms / defaults. Runtime CSS always falls back to
    | dark_theam when no org is selected in the session or theme is empty/invalid
    | (see App\Support\UiTheme::DEFAULT_FOLDER).
    |
    */
    'default_theme' => env('UI_DEFAULT_THEME', 'dark_theam'),

    /*
    |--------------------------------------------------------------------------
    | Allowed theme folder names (must match directories in public/)
    |--------------------------------------------------------------------------
    */
    'themes' => [
        'dark_theam' => 'Dark',
        'blue_theam' => 'Blue',
    ],

];
