<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Themes Path
    |--------------------------------------------------------------------------
    |
    | The path where your themes are located. You can provide multiple paths
    | for theme discovery.
    |
    */
    'themes_path' => base_path('themes'),

    /*
    |--------------------------------------------------------------------------
    | Active Theme
    |--------------------------------------------------------------------------
    |
    | The default active theme name.
    |
    */
    'active' => (string) env('THEME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Asset Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how assets and Vite integration should behave.
    |
    */
    'assets' => [
        'path' => 'themes', // Public path suffix (public/themes)
        'publish_on_activate' => true,
        'symlink' => (bool) env('THEMER_SYMLINK', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Discovery
    |--------------------------------------------------------------------------
    |
    | Configuration for theme discovery logic.
    |
    */
    'discovery' => [
        'filename' => 'theme.json',
        'scan_modules' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Auto-Namespaces
    |--------------------------------------------------------------------------
    |
    | Automatically register view and Livewire namespaces for common theme
    | directories. These are applied to each theme during activation and
    | work alongside Livewire's native component_namespaces configuration.
    |
    */
    'auto_namespaces' => [
        'layouts' => 'resources/views/layouts',
        'pages' => 'resources/views/livewire/pages',
    ],
];
