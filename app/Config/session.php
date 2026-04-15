<?php

declare(strict_types=1);

return [
    'name' => getenv('SESSION_NAME') ?: 'IMSSESSID',
    'lifetime' => (int) (getenv('SESSION_LIFETIME') ?: 7200),
    'expire_on_close' => filter_var(getenv('SESSION_EXPIRE_ON_CLOSE') ?: false, FILTER_VALIDATE_BOOLEAN),
    'secure' => filter_var(getenv('SESSION_SECURE') ?: false, FILTER_VALIDATE_BOOLEAN),
    'httponly' => true,
    'samesite' => getenv('SESSION_SAMESITE') ?: 'Lax',
];
