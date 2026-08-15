<?php

namespace App\Services;

use App\Core\Database;
use App\Models\UserModel;

class CsvService
{
    private Database $db;
    private UserModel $userModel;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->userModel = new UserModel();
    }

    /**
     * Phase 1: Validate CSV file before import
     * Uses fgetcsv() to parse and validate header row, detect duplicates, validate row format
     * 
     * @param string $filePath Absolute path to uploaded CSV file
     * @return array ['success' => bool, 'errors' => array, 'warnings' => array, 'validRows' => int]
     */
    public function validateCsv(string $filePath): array
    {
        $errors = [];
        $warnings = [];
        $validRows = 0;
        $emailsInFile = [];
        $emailsInDb = [];
        $lineNumber = 0;

        // Fetch all existing emails from database to check for duplicates
        try {
            $stmt = $this->db->getConnection()->prepare('SELECT email FROM users WHERE is_active = 1');
            $stmt->execute();
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $emailsInDb[] = $row['email'];
            }
        } catch (\Exception $e) {
            $errors[] = "Database error checking existing emails: " . $e->getMessage();
            return ['success' => false, 'errors' => $errors, 'warnings' => $warnings, 'validRows' => 0];
        }

        // Open and parse CSV using fgetcsv()
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $errors[] = "Unable to open CSV file";
            return ['success' => false, 'errors' => $errors, 'warnings' => $warnings, 'validRows' => 0];
        }

        // Validate header row
        $header = fgetcsv($handle);
        $lineNumber = 1;
        
        if (!$header) {
            $errors[] = "CSV file is empty";
            fclose($handle);
            return ['success' => false, 'errors' => $errors, 'warnings' => $warnings, 'validRows' => 0];
        }

        // Expected headers per spec: email, full_name, phone, role
        $expectedHeaders = ['email', 'full_name', 'phone', 'role'];
        $header = array_map('strtolower', array_map('trim', $header));

        if ($header !== $expectedHeaders) {
            $errors[] = "CSV header must be: " . implode(', ', $expectedHeaders);
            fclose($handle);
            return ['success' => false, 'errors' => $errors, 'warnings' => $warnings, 'validRows' => 0];
        }

        // Parse and validate data rows
        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Map row to fields
            $data = array_combine($expectedHeaders, $row);

            // Trim all values
            $data = array_map('trim', $data);

            // Validate email format
            if (empty($data['email'])) {
                $errors[] = "Line $lineNumber: Email cannot be empty";
                continue;
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Line $lineNumber: Invalid email format: {$data['email']}";
                continue;
            }

            // Check for duplicates within file
            if (in_array($data['email'], $emailsInFile)) {
                $errors[] = "Line $lineNumber: Duplicate email in file: {$data['email']}";
                continue;
            }

            // Check for duplicates in database
            if (in_array($data['email'], $emailsInDb)) {
                $warnings[] = "Line $lineNumber: Email already exists in database: {$data['email']} (will be skipped)";
                continue;
            }

            // Validate full_name
            if (empty($data['full_name'])) {
                $errors[] = "Line $lineNumber: Full name cannot be empty";
                continue;
            }
            if (strlen($data['full_name']) > 255) {
                $errors[] = "Line $lineNumber: Full name too long (max 255 characters)";
                continue;
            }

            // Validate phone format (exactly 10 digits)
            if (!empty($data['phone']) && !preg_match('/^\d{10}$/', $data['phone'])) {
                $errors[] = "Line $lineNumber: Phone number must be exactly 10 digits";
                continue;
            }

            // Validate role - must be TEACHER or STUDENT for CSV import (per spec limited scope)
            $validRoles = ['TEACHER', 'STUDENT'];
            if (empty($data['role'])) {
                $errors[] = "Line $lineNumber: Role cannot be empty";
                continue;
            }
            if (!in_array(strtoupper($data['role']), $validRoles)) {
                $errors[] = "Line $lineNumber: Invalid role '{$data['role']}'. Must be one of: " . implode(', ', $validRoles);
                continue;
            }

            // Row is valid
            $emailsInFile[] = $data['email'];
            $validRows++;
        }

        fclose($handle);

        // If there are critical errors, return failure
        $success = empty($errors);

        return [
            'success' => $success,
            'errors' => $errors,
            'warnings' => $warnings,
            'validRows' => $validRows
        ];
    }

    /**
     * Phase 2: Import validated CSV file
     * For each valid row, calls UserModel::create() with bcrypt password hashing
     * Uses PDO transaction to ensure all-or-nothing atomicity
     * 
     * @param string $filePath Absolute path to CSV file
     * @return array ['success' => bool, 'created' => int, 'skipped' => int, 'errors' => array]
     */
    public function importCsv(string $filePath): array
    {
        $created = 0;
        $skipped = 0;
        $errors = [];
        $connection = $this->db->getConnection();

        // Start transaction
        $connection->beginTransaction();

        try {
            // Open CSV for reading
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new \Exception("Unable to open CSV file");
            }

            // Skip header row
            fgetcsv($handle);
            $lineNumber = 1;

            // Fetch all existing emails to check for duplicates during import
            $stmt = $connection->prepare('SELECT email FROM users WHERE is_active = 1');
            $stmt->execute();
            $emailsInDb = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $emailsInDb[] = $row['email'];
            }

            // Process each row
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Map row to fields
                $expectedHeaders = ['email', 'full_name', 'phone', 'role'];
                $data = array_combine($expectedHeaders, $row);
                $data = array_map('trim', $data);

                // Quick validation checks
                if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Line $lineNumber: Invalid email";
                    $skipped++;
                    continue;
                }

                // Skip if email already exists
                if (in_array($data['email'], $emailsInDb)) {
                    $skipped++;
                    continue;
                }

                // Generate temporary password (10 hex chars)
                $tempPassword = bin2hex(random_bytes(5));

                // Prepare data for insert
                $userData = [
                    'email' => $data['email'],
                    'full_name' => $data['full_name'],
                    'phone' => $data['phone'] ?? '',
                    'password' => password_hash($tempPassword, PASSWORD_BCRYPT, ['cost' => 12]),
                    'role' => strtoupper($data['role']),
                    'must_change_password' => 1,  // Force password change on first login
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // Insert user using prepared statement
                $stmt = $connection->prepare('
                    INSERT INTO users 
                    (email, full_name, phone, password, role, must_change_password, is_active, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ');

                $stmt->execute([
                    $userData['email'],
                    $userData['full_name'],
                    $userData['phone'],
                    $userData['password'],
                    $userData['role'],
                    $userData['must_change_password'],
                    $userData['is_active'],
                    $userData['created_at']
                ]);

                // Track created user
                $created++;
                $emailsInDb[] = $data['email'];

                // If STUDENT role, create student_profile entry
                if ($userData['role'] === 'STUDENT') {
                    $userId = $connection->lastInsertId();
                    $profileStmt = $connection->prepare('
                        INSERT INTO student_profiles (user_id, enrollment_status, is_active, created_at)
                        VALUES (?, ?, ?, ?)
                    ');
                    $profileStmt->execute([
                        $userId,
                        'ACTIVE',
                        1,
                        date('Y-m-d H:i:s')
                    ]);
                }
            }

            fclose($handle);

            // Commit transaction
            $connection->commit();

            return [
                'success' => true,
                'created' => $created,
                'skipped' => $skipped,
                'errors' => $errors
            ];
        } catch (\Exception $e) {
            // Rollback on error
            $connection->rollBack();
            fclose($handle ?? null);

            return [
                'success' => false,
                'created' => 0,
                'skipped' => 0,
                'errors' => [$e->getMessage()]
            ];
        }
    }
}
