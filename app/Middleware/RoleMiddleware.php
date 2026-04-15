<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;

final class RoleMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $requiredRoles = 'admin')
    {
    }

    public function handle(Request $request, array $params = []): bool
    {
        $currentRole = strtoupper((string) ($_SESSION['user_role'] ?? ''));

        if ($currentRole === '') {
            http_response_code(403);
            echo 'Forbidden: No role assigned';
            return false;
        }

        // Parse required roles (can be single role or comma-separated like 'admin,principal,vp')
        $requiredRoles = array_map('trim', array_map('strtoupper', explode(',', $this->requiredRoles)));

        // Check if current role matches any required role
        foreach ($requiredRoles as $role) {
            if ($currentRole === $role) {
                return true;
            }
        }

        http_response_code(403);
        echo 'Forbidden: Insufficient permissions';
        return false;
    }
}

