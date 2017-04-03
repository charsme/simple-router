<?php

include __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function ($class) {
    if (strpos($class, 'Resilient\Factory\\') === 0) {
        $dir = 'test/Factory/' ;
        $name = substr($class, strlen('Resilient\\Factory\\'));
        require __DIR__ . '/../' . $dir . strtr($name, '\\', DIRECTORY_SEPARATOR) . '.php';
    }
});
