<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, array $params = []): bool
    {
        $method = strtoupper($request->method());

        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return true;
        }

        $sessionToken = (string) ($_SESSION['_token'] ?? '');
        $requestToken = (string) $request->input('_token', '');

        if ($sessionToken !== '' && hash_equals($sessionToken, $requestToken)) {
            return true;
        }

        http_response_code(419);
        echo 'CSRF token mismatch';
        return false;
    }
}
