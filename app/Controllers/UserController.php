<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use App\Services\ActivationService;

class UserController extends BaseController
{
    public function index(): void
    {
        $model = new UserModel();
        $rows = $model->all('id', 'DESC');

        $users = array_map(static function (array $user): array {
            $role = strtoupper((string) ($user['role'] ?? ''));
            $isActive = (int) ($user['is_active'] ?? 0) === 1;

            return [
                'id' => (int) ($user['id'] ?? 0),
                'role' => $role,
                'login_id' => (string) ($user['login_id'] ?? ''),
                'full_name' => (string) ($user['full_name'] ?? ''),
                'email' => (string) ($user['email'] ?? ''),
                'status' => $isActive ? 'Active' : 'Inactive',
                'can_resend_activation' => $role === 'PRINCIPAL',
            ];
        }, $rows);

        $this->view('users.index', [
            'title' => 'Users',
            'users' => $users,
        ]);
    }

    public function create(): void
    {
        $this->view('users.create', [
            'title' => 'Create User',
        ]);
    }

    public function show(int $id): void
    {
        $this->view('users.show', [
            'title' => 'User Details',
            'user' => [
                'id' => $id,
                'role' => 'student',
                'login_id' => 'user' . $id,
                'full_name' => 'Sample User ' . $id,
                'email' => 'user' . $id . '@example.test',
                'phone' => '0917000000' . $id,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function edit(int $id): void
    {
        $this->view('users.edit', [
            'title' => 'Edit User',
            'user' => [
                'id' => $id,
                'role' => 'teacher',
                'login_id' => 'teacher' . $id,
                'full_name' => 'Sample User ' . $id,
                'email' => 'user' . $id . '@example.test',
                'phone' => '0917000000' . $id,
                'is_active' => '1',
            ],
        ]);
    }

    public function store(): void
    {
        $name = (string) $this->input('full_name', '');
        $loginId = (string) $this->input('login_id', '');
        $email = (string) $this->input('email', '');

        if ($name === '' || $loginId === '' || $email === '') {
            $this->json([
                'success' => false,
                'message' => 'Full name, login ID, and email are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'User endpoint is ready for model integration.',
        ], 201);
    }

    public function update(int $id): void
    {
        $name = (string) $this->input('full_name', '');
        $loginId = (string) $this->input('login_id', '');
        $email = (string) $this->input('email', '');

        if ($name === '' || $loginId === '' || $email === '') {
            $this->json([
                'success' => false,
                'message' => 'Full name, login ID, and email are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'User update endpoint is ready for model integration.',
            'data' => ['id' => $id],
        ]);
    }

    public function resendActivation(int $id): void
    {
        $service = new ActivationService();
        $result = $service->sendPrincipalActivation($id, true, (int) ($_SESSION['user_id'] ?? 0));

        $this->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['status']);
    }
}
