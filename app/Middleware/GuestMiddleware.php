<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

final class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, array $params = []): bool
    {
        $path = $request->path();
        if (str_starts_with($path, '/activate/')) {
            return true;
        }

        if (!isset($_SESSION['user_id']) || (int) $_SESSION['user_id'] <= 0) {
            return true;
        }

        Response::redirect('dashboard');
        return false;
    }
}
