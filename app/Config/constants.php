<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__, 2));
}

if (!defined('APP_PATH')) {
    define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'app');
}

if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'Config');
}

if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'public');
}

if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'storage');
}

if (!defined('DATABASE_PATH')) {
    define('DATABASE_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'database');
}
