<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Models\UserModel;

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, array $params = []): bool
    {
        if (isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] > 0) {
            $role = strtoupper((string) ($_SESSION['user_role'] ?? ''));
            if ($role === 'PRINCIPAL' && !$this->isProfileComplete((int) $_SESSION['user_id'])) {
                $path = $request->path();
                $allowed = [
                    '/principal/profile',
                    '/principal/profile/email',
                    '/principal/profile/email/verify-otp',
                    '/change-password',
                    '/logout',
                ];

                if (!in_array($path, $allowed, true)) {
                    Response::redirect('principal/profile');
                    return false;
                }
            }
            return true;
        }

        Response::redirect('login');
        return false;
    }

    private function isProfileComplete(int $userId): bool
    {
        $user = (new UserModel())->find($userId);
        if (!$user) {
            return false;
        }

        $fullName = trim((string) ($user['full_name'] ?? ''));
        if ($fullName === '' || strcasecmp($fullName, 'Account Pending Activation') === 0) {
            return false;
        }

        return true;
    }
}
