<?php

use App\Services\JWT\Response\HeaderTokenProvider;

return [

    //JWT service settings
    'jwt' => [
        'issuer'      => env('JWT_ISSUER'),
        'secret'      => env('JWT_SECRET'),
        'expiryHours' => env('JWT_EXPIRY_HOURS', 1),
        'responseProvider' => [
            'class'  => env('JWT_RESPONSE_PROVIDER_CLASS', HeaderTokenProvider::class),
            'config' => [
                'bearer' => env('JWT_RESPONSE_BEARER', 'Authorization'),
            ],
        ]
    ]

];