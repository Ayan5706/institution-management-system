<?php

declare(strict_types=1);

$helpersDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Helpers';
$files = glob($helpersDir . DIRECTORY_SEPARATOR . '*.php') ?: [];

sort($files);

foreach ($files as $file) {
    if (is_file($file)) {
        require_once $file;
    }
}
