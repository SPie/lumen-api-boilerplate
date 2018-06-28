<?php

return [

    //JWT service settings
    'jwt' => [
        'issuer'      => env('JWT_ISSUER'),
        'secret'      => env('JWT_SECRET'),
        'expiryHours' => env('JWT_EXPIRY_HOURS', 1),
    ]

];