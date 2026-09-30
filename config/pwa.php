<?php

return [

    'short_name' => env('PWA_SHORT_NAME', 'Magang PERADI'),

    'theme_color' => '#0d2a5c',

    'background_color' => '#eef0f4',

    'display' => 'standalone',

    'lang' => 'id',

    'icons' => [
        [
            'path' => 'pwa/icon-192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'path' => 'pwa/icon-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'path' => 'pwa/icon-maskable-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'maskable',
        ],
    ],

];
