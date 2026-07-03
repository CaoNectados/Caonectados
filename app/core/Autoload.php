<?php

spl_autoload_register(function ($class) {

    $prefix = 'app\\';
    $baseDir = __DIR__ . '/../';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $path = substr($class, strlen($prefix));
    $classFile = $baseDir . str_replace('\\', '/', $path) . '.php';

    if (file_exists($classFile)) {
        require_once $classFile;
    }
});