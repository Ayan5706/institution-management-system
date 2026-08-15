<?php

namespace App\Seeders;

class TeacherAssignmentsTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding teacher_assignments table...\n";

        $assignments = [
            // Dr. Johnson (user_id=3)
            ['teacher_id' => 3, 'subject_id' => 1],  // CS101
            ['teacher_id' => 3, 'subject_id' => 4],  // CS201
            ['teacher_id' => 3, 'subject_id' => 7],  // MACS101
            ['teacher_id' => 3, 'subject_id' => 9],  // MACS201

            // Mr. Smith (user_id=4)
            ['teacher_id' => 4, 'subject_id' => 2],  // CS102
            ['teacher_id' => 4, 'subject_id' => 5],  // CS202
            ['teacher_id' => 4, 'subject_id' => 8],  // MACS102
            ['teacher_id' => 4, 'subject_id' => 10], // MACS202

            // Ms. Davis (user_id=5)
            ['teacher_id' => 5, 'subject_id' => 3],  // MATH101
            ['teacher_id' => 5, 'subject_id' => 6],  // MATH102
        ];

        $this->insertMany('teacher_assignments', $assignments);
        echo "Seeded " . count($assignments) . " teacher assignments.\n";
    }
}
