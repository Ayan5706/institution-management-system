<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

return [
    'root' => $root,
    'app' => $root . DIRECTORY_SEPARATOR . 'app',
    'config' => $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config',
    'public' => $root . DIRECTORY_SEPARATOR . 'public',
    'storage' => $root . DIRECTORY_SEPARATOR . 'storage',
    'logs' => $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs',
    'cache' => $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache',
    'sessions' => $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions',
    'views' => $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Views',
    'database' => $root . DIRECTORY_SEPARATOR . 'database',
];
