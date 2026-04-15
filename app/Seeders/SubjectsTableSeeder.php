<?php

namespace App\Seeders;

class SubjectsTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding subjects table...\n";

        $subjects = [
            // Program 1, Semester 1 - CS101
            [
                'semester_id' => 1,
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Programming',
            ],
            // Program 1, Semester 1 - CS102
            [
                'semester_id' => 1,
                'subject_code' => 'CS102',
                'subject_name' => 'Data Structures and Algorithms',
            ],
            // Program 1, Semester 2 - CS201
            [
                'semester_id' => 2,
                'subject_code' => 'CS201',
                'subject_name' => 'Web Development',
            ],
            // Program 1, Semester 2 - CS202
            [
                'semester_id' => 2,
                'subject_code' => 'CS202',
                'subject_name' => 'Database Systems',
            ],
            // Program 2, Semester 1 - EN101
            [
                'semester_id' => 5,
                'subject_code' => 'EN101',
                'subject_name' => 'English Composition',
            ],
            // Program 2, Semester 1 - EN102
            [
                'semester_id' => 5,
                'subject_code' => 'EN102',
                'subject_name' => 'British Literature',
            ],
            // Program 2, Semester 2 - EN201
            [
                'semester_id' => 6,
                'subject_code' => 'EN201',
                'subject_name' => 'American Literature',
            ],
            // Program 3, Semester 1 - BI101
            [
                'semester_id' => 7,
                'subject_code' => 'BI101',
                'subject_name' => 'Cell Biology',
            ],
            // Program 3, Semester 1 - BI102
            [
                'semester_id' => 7,
                'subject_code' => 'BI102',
                'subject_name' => 'Genetics',
            ],
            // Program 4, Semester 1 - MA101
            [
                'semester_id' => 9,
                'subject_code' => 'MA101',
                'subject_name' => 'Calculus I',
            ],
            // Program 4, Semester 1 - MA102
            [
                'semester_id' => 9,
                'subject_code' => 'MA102',
                'subject_name' => 'Linear Algebra',
            ],
        ];

        $this->insertMany('subjects', $subjects);
        echo "Seeded " . count($subjects) . " subjects.\n";
    }
}
