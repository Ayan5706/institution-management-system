<?php

namespace App\Seeders;

class StudentProfilesTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding student_profiles table...\n";

        $profiles = [
            // janderson (user_id=6)
            [
                'user_id' => 6,
                'program_id' => 1,
                'enrollment_date' => '2026-01-15',
                'status' => 'active',
                'semester' => 1,
                'gpa' => 3.45,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // sbrown (user_id=7)
            [
                'user_id' => 7,
                'program_id' => 1,
                'enrollment_date' => '2026-01-15',
                'status' => 'active',
                'semester' => 1,
                'gpa' => 3.78,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // mharris (user_id=8)
            [
                'user_id' => 8,
                'program_id' => 2,
                'enrollment_date' => '2026-01-15',
                'status' => 'active',
                'semester' => 1,
                'gpa' => 3.62,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // ltaylor (user_id=9)
            [
                'user_id' => 9,
                'program_id' => 3,
                'enrollment_date' => '2026-01-15',
                'status' => 'active',
                'semester' => 1,
                'gpa' => 3.55,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // dmiller (user_id=10)
            [
                'user_id' => 10,
                'program_id' => 4,
                'enrollment_date' => '2026-01-15',
                'status' => 'active',
                'semester' => 1,
                'gpa' => 3.92,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->insertMany('student_profiles', $profiles);
        echo "Seeded " . count($profiles) . " student profiles.\n";
    }
}
