<?php

/**
 * User Management Script
 *
 * Create, update, and manage users from the command line.
 *
 * Usage:
 *   php scripts/user-manage.php create
 *   php scripts/user-manage.php list
 *   php scripts/user-manage.php reset-password <user_id>
 *   php scripts/user-manage.php deactivate <user_id>
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Autoloader.php';
\App\Core\Autoloader::register(BASE_PATH);

require_once BASE_PATH . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'config.php';

use App\Core\Database;

class UserManager
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function create(): void
    {
        echo "\nCreate New User\n";
        echo str_repeat("─", 40) . "\n";

        echo "Full Name: ";
        $fullName = trim(fgets(STDIN));

        echo "Login ID: ";
        $loginId = trim(fgets(STDIN));

        echo "Email: ";
        $email = trim(fgets(STDIN));

        echo "Phone: ";
        $phone = trim(fgets(STDIN));

        echo "Role (admin|teacher|student): ";
        $role = trim(fgets(STDIN));

        echo "Password: ";
        system('stty -echo');
        $password = trim(fgets(STDIN));
        system('stty echo');
        echo "\n";

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO users (login_id, full_name, email, phone, role, password, is_active, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, 1, NOW())'
            );

            $stmt->execute([$loginId, $fullName, $email, $phone, $role, $hashedPassword]);

            $userId = $this->pdo->lastInsertId();

            echo "✓ User created successfully! (ID: {$userId})\n\n";
        } catch (\PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }

    public function listUsers(): void
    {
        echo "\nUsers List\n";
        echo str_repeat("─", 80) . "\n";

        try {
            $stmt = $this->pdo->query('SELECT id, login_id, full_name, email, role, is_active, created_at FROM users');
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($users)) {
                echo "No users found.\n";
                return;
            }

            printf("%-5s %-15s %-20s %-25s %-12s %-10s\n", 'ID', 'Login ID', 'Full Name', 'Email', 'Role', 'Active');
            echo str_repeat("─", 80) . "\n";

            foreach ($users as $user) {
                $active = $user['is_active'] ? 'Yes' : 'No';
                printf(
                    "%-5d %-15s %-20s %-25s %-12s %-10s\n",
                    $user['id'],
                    $user['login_id'],
                    substr($user['full_name'], 0, 19),
                    substr($user['email'], 0, 24),
                    $user['role'],
                    $active
                );
            }

            echo "\n";
        } catch (\PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }

    public function resetPassword(int $userId): void
    {
        try {
            $stmt = $this->pdo->prepare('SELECT full_name FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                echo "✗ User not found.\n";
                return;
            }

            echo "\nReset Password for: {$user['full_name']}\n";
            echo str_repeat("─", 40) . "\n";

            echo "New Password: ";
            system('stty -echo');
            $password = trim(fgets(STDIN));
            system('stty echo');
            echo "\n";

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $this->pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$hashedPassword, $userId]);

            echo "✓ Password reset successfully!\n\n";
        } catch (\PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }

    public function deactivate(int $userId): void
    {
        try {
            $stmt = $this->pdo->prepare('SELECT full_name, is_active FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                echo "✗ User not found.\n";
                return;
            }

            $status = $user['is_active'] ? 'deactivate' : 'activate';
            $action = ucfirst($status);

            echo "\n{$action} User: {$user['full_name']}\n";
            echo str_repeat("─", 40) . "\n";
            echo "Are you sure? (yes/no): ";

            $input = trim(fgets(STDIN));
            if (strtolower($input) !== 'yes' && $input !== 'y') {
                echo "Cancelled.\n";
                return;
            }

            $newStatus = $user['is_active'] ? 0 : 1;
            $stmt = $this->pdo->prepare('UPDATE users SET is_active = ? WHERE id = ?');
            $stmt->execute([$newStatus, $userId]);

            echo "✓ User status updated!\n\n";
        } catch (\PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }

    public function showHelp(): void
    {
        echo "\nUser Management\n";
        echo str_repeat("─", 40) . "\n";
        echo "Usage: php scripts/user-manage.php <command> [args]\n\n";
        echo "Commands:\n";
        echo "  create                  Create a new user\n";
        echo "  list                    List all users\n";
        echo "  reset-password <id>     Reset user password\n";
        echo "  deactivate <id>         Deactivate/activate user\n";
        echo "  help                    Show this message\n\n";
    }
}

$manager = new UserManager();

$command = $argv[1] ?? 'help';

match ($command) {
    'create' => $manager->create(),
    'list' => $manager->listUsers(),
    'reset-password' => $manager->resetPassword((int) ($argv[2] ?? 0)),
    'deactivate' => $manager->deactivate((int) ($argv[2] ?? 0)),
    'help', '--help' => $manager->showHelp(),
    default => $manager->showHelp(),
};
