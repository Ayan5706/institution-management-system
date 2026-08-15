<?php

namespace App\Seeders;

class SubjectsTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding subjects table...\n";

        $subjects = [
            // BCA Semester 1 (semester_id: 1)
            ['semester_id' => 1, 'subject_code' => 'CS101', 'subject_name' => 'Introduction to Programming'],
            ['semester_id' => 1, 'subject_code' => 'CS102', 'subject_name' => 'Data Structures'],
            ['semester_id' => 1, 'subject_code' => 'MATH101', 'subject_name' => 'Calculus I'],

            // BCA Semester 2 (semester_id: 2)
            ['semester_id' => 2, 'subject_code' => 'CS201', 'subject_name' => 'Object-Oriented Programming'],
            ['semester_id' => 2, 'subject_code' => 'CS202', 'subject_name' => 'Web Development Basics'],
            ['semester_id' => 2, 'subject_code' => 'MATH102', 'subject_name' => 'Calculus II'],

            // MSC Semester 1 (semester_id: 3)
            ['semester_id' => 3, 'subject_code' => 'MACS101', 'subject_name' => 'Advanced Algorithms'],
            ['semester_id' => 3, 'subject_code' => 'MACS102', 'subject_name' => 'Distributed Systems'],

            // MSC Semester 2 (semester_id: 4)
            ['semester_id' => 4, 'subject_code' => 'MACS201', 'subject_name' => 'Machine Learning Advanced'],
            ['semester_id' => 4, 'subject_code' => 'MACS202', 'subject_name' => 'Natural Language Processing'],
        ];

        $this->insertMany('subjects', $subjects);
        echo "Seeded " . count($subjects) . " subjects.\n";
    }
}
