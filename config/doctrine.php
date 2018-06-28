<?php

return [
    'managers'   => [
        'default' => [
            'dev'        => env('APP_DEBUG'),
            'meta'       => env('DOCTRINE_METADATA', 'annotations'),
            'connection' => env('DB_CONNECTION', 'mysql'),
            'namespaces' => [
                'App',
            ],
            'paths'         => [
                base_path('app/Models')
            ],
            'repository' => Doctrine\ORM\EntityRepository::class,
            'proxies'    => [
                'namespace'     => false,
                'path'          => storage_path('proxies'),
                'auto_generate' => env('DOCTRINE_PROXY_AUTOGENERATE', false),
            ],
        ],
    ],
    'extensions' => [
        LaravelDoctrine\Extensions\Timestamps\TimestampableExtension::class,
        LaravelDoctrine\Extensions\SoftDeletes\SoftDeleteableExtension::class,
    ],
    'gedmo'      => [
        'all_mappings' => false
    ],
];