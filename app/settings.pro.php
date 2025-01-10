<?php

return [
    'settings' => [
        'env' => 'pro',
        'installerKey' => '~SECRET~',
        'displayErrorDetails' => true,
        'timezone' => 'America/Argentina/Buenos_Aires',
        'spaUrl' => 'https://creatingnetworks.youthsig.org',
        'adminEmails' => [],
        'swiftmailer' => [
            'transport' => 'sparkpost',
            'transportOptions' => [
                'secret' => '~SECRET~',
            ],
            'mailerOptions' => [
                'from' => [
                    'address' => 'no-reply@email.youthsig.org',
                    'name' => 'Youth SIG',
                ],
            ],
        ],
        'jwt' => [
            'priKey' => 'file://'.__DIR__.'/../var/keys/private.pem',
            'pubKey' => 'file://'.__DIR__.'/../var/keys/pubkey.pem',
            'storedKey' => true,
            'alg' => 'RS256',
            'iss' => 'youthSIG',
            'ttl' => 86400,
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
        'recaptcha' => [
            'fieldname' => 'recaptcha',
            'secret' => '~SECRET~',
            'hostname' => null,
        ],
        'cors' => [
            'origin' => ['https://creatingnetworks.youthsig.virtuagora.org'],
            'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'headers.allow' => ['X-Requested-With', 'Content-Type', 'Accept', 'Origin', 'Authorization'],
            'headers.expose' => [],
            'credentials' => true,
            'cache' => 0,
        ],
    ],
];
