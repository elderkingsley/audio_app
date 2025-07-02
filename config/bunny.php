<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bunny.net Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Bunny.net storage zone and CDN settings.
    |
    */

    'storage' => [
        'zone_name' => env('BUNNY_STORAGE_ZONE_NAME'),
        'password' => env('BUNNY_STORAGE_PASSWORD'),
        'hostname' => env('BUNNY_STORAGE_HOSTNAME', 'storage.bunnycdn.com'),
        'region' => env('BUNNY_STORAGE_REGION', 'de'), // Default to Germany region
    ],

    'cdn' => [
        'hostname' => env('BUNNY_CDN_HOSTNAME'),
        'use_ssl' => env('BUNNY_CDN_USE_SSL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    |
    | Settings for API communication with Bunny.net
    |
    */

    'api' => [
        'timeout' => env('BUNNY_API_TIMEOUT', 30),
        'retry_attempts' => env('BUNNY_API_RETRY_ATTEMPTS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Music File Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for music file handling
    |
    */

    'music' => [
        'allowed_extensions' => ['mp3', 'wav', 'flac', 'aac', 'm4a', 'ogg'],
        'max_file_size' => env('BUNNY_MAX_FILE_SIZE', 50 * 1024 * 1024), // 50MB default
        'music_directory' => env('BUNNY_MUSIC_DIRECTORY', ''),
    ],
];
