<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'firebase' => [
        'config' => [
            'apiKey' => env('FIREBASE_API_KEY'),
            'authDomain' => env('FIREBASE_AUTH_DOMAIN'),
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'storageBucket' => env('FIREBASE_STORAGE_BUCKET'),
            'messagingSenderId' => env('FIREBASE_MESSAGING_SENDER_ID'),
            'appId' => env('FIREBASE_APP_ID'),
            'measurementId' => env('FIREBASE_MEASUREMENT_ID'),
        ],
        'vapid_key' => env('FIREBASE_VAPID_KEY'),
        // FCM legacy server key used to send push notifications
        'server_key' => env('SERVER_API_KEY'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'zoom' => [
        'api_url' => env('ZOOM_API_URL', 'https://api.zoom.us/v2/'),
        'key' => env('ZOOM_API_KEY'),
        'secret' => env('ZOOM_API_SECRET'),
        'jwt' => env('ZOOM_API_JWT'),
        // Base URL used to build meeting links from a meeting id
        'join_url' => env('ZOOM_JOIN_URL', 'https://zoom.us/j/'),
    ],

    'socket' => [
        'url' => env('SOCKET_SERVER_URL', 'http://127.0.0.1:3000'),
    ],

    'document_converter' => [
        'url' => env('DOCUMENT_CONVERTER_URL'),
    ],

];
