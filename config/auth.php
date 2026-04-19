<?php

return [
    'defaults' => [
        'guard'     => 'web',
        'passwords' => 'bibliotecarios',
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'bibliotecarios',
        ],
    ],

    'providers' => [
        'bibliotecarios' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Bibliotecario::class,
        ],
    ],

    'passwords' => [
        'bibliotecarios' => [
            'provider' => 'bibliotecarios',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
