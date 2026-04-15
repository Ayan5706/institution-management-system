<?php

namespace App\Seeders;

class TeacherAssignmentsTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding teacher_assignments table...\n";

        $assignments = [
            // Dr. Johnson (user_id=3) assignments
            [
                'user_id' => 3,
                'subject_id' => 1,
                'program_id' => 1,
                'academic_year' => '2026',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 3,
                'subject_id' => 4,
                'program_id' => 4,
                'academic_year' => '2026',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Mr. Smith (user_id=4) assignments
            [
                'user_id' => 4,
                'subject_id' => 2,
                'program_id' => 1,
                'academic_year' => '2026',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 4,
                'subject_id' => 3,
                'program_id' => 1,
                'academic_year' => '2026',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Ms. Davis (user_id=5) assignments
            [
                'user_id' => 5,
                'subject_id' => 5,
                'program_id' => 2,
                'academic_year' => '2026',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 5,
                'subject_id' => 6,
                'program_id' => 2,
                'academic_year' => '2026',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->insertMany('teacher_assignments', $assignments);
        echo "Seeded " . count($assignments) . " teacher assignments.\n";
    }
}
