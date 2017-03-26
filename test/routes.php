<?php

use Resilient\DummyController;

return [
    'GET' => [
        '/{dev}' => function () {
            return 'routed';
        },
        '/' => 'Home/class',
        '/{dev}/{id:\d+}' => 'Dev/func'
    ],
    'POST' => [
        '/api' => function () {
            return $_POST;
        },
        '/{name}[/{point}]' => DummyController::class
    ]
];
