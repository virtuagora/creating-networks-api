<?php

return [
    'settings' => [
        'env' => 'dev',
        'determineRouteBeforeAppMiddleware' => true,
        'displayErrorDetails' => true,
        'timezone' => 'America/Argentina/Buenos_Aires',
        'swiftmailer' => [
            'transport' => 'smtp',
            'transportOptions' => [
                'host' => '',
                'port' => 2525,
                'encryption' => 'tls',
                'username' => '',
                'password' => '',
            ],
            'mailerOptions' => [
                'from' => [
                    'address' => 'no-reply@youthsig.org',
                    'name' => 'Youth SIG',
                ],
            ],
        ],
        'jwt' => [
            'secret' => '12345678',
            'storedKey' => false,
            'alg' => 'HS256',
            'iss' => 'YouthSIG',
            'ttl' => 8640000,
        ],
        'monolog' => [
            'name' => 'monolog',
            'path' => __DIR__.'/../var/logs/app.log',
        ],
        'eloquent' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'creating_networks',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
        ],
    ],
];
