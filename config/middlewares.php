<?php

return [

    'apiSignature' => [
        'secret'           => env('API_SECRET'),
        'algorithm'        => env('API_ALGORITHM', 'sha512'),
        'toleranceSeconds' => env('API_TOLERANCE_SECONDS', 0),
    ]

];