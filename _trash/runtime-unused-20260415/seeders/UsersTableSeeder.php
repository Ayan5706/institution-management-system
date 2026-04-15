<?php

namespace App\Seeders;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding users table with standardized test credentials...\n";

        // Standardized test accounts - one per role
        $users = [
            // Principal - can access all system functions
            [
                'full_name' => 'Principal Test Account',
                'login_id' => 'principal',
                'email' => 'principal@imsschool.local',
                'phone' => '555-1001',
                'password' => password_hash('principal123', PASSWORD_BCRYPT),
                'role' => 'PRINCIPAL',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Vice Principal - manages operations and reports
            [
                'full_name' => 'Vice Principal Test Account',
                'login_id' => 'vp',
                'email' => 'vp@imsschool.local',
                'phone' => '555-1002',
                'password' => password_hash('vp123', PASSWORD_BCRYPT),
                'role' => 'VP',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Manager - manages programs, schedules, and staff
            [
                'full_name' => 'Manager Test Account',
                'login_id' => 'manager',
                'email' => 'manager@imsschool.local',
                'phone' => '555-1003',
                'password' => password_hash('manager123', PASSWORD_BCRYPT),
                'role' => 'MANAGER',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Accountant - manages fees and finances
            [
                'full_name' => 'Accountant Test Account',
                'login_id' => 'accountant',
                'email' => 'accountant@imsschool.local',
                'phone' => '555-1004',
                'password' => password_hash('accountant123', PASSWORD_BCRYPT),
                'role' => 'ACCOUNTANT',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Teacher - manages attendance and class records
            [
                'full_name' => 'Teacher Test Account',
                'login_id' => 'teacher',
                'email' => 'teacher@imsschool.local',
                'phone' => '555-1005',
                'password' => password_hash('teacher123', PASSWORD_BCRYPT),
                'role' => 'TEACHER',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Student - views own records and fees
            [
                'full_name' => 'Student Test Account',
                'login_id' => 'student',
                'email' => 'student@imsschool.local',
                'phone' => '555-1006',
                'password' => password_hash('student123', PASSWORD_BCRYPT),
                'role' => 'STUDENT',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->insertMany('users', $users);
        echo "✓ Seeded " . count($users) . " test accounts (one per role).\n";
    }
}
