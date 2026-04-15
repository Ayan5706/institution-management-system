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
                'teacher_id' => 3,
                'subject_id' => 1,
            ],
            [
                'teacher_id' => 3,
                'subject_id' => 4,
            ],
            // Mr. Smith (user_id=4) assignments
            [
                'teacher_id' => 4,
                'subject_id' => 2,
            ],
            [
                'teacher_id' => 4,
                'subject_id' => 3,
            ],
            // Ms. Davis (user_id=5) assignments
            [
                'teacher_id' => 5,
                'subject_id' => 5,
            ],
            [
                'teacher_id' => 5,
                'subject_id' => 6,
            ],
        ];

        $this->insertMany('teacher_assignments', $assignments);
        echo "Seeded " . count($assignments) . " teacher assignments.\n";
    }
}
