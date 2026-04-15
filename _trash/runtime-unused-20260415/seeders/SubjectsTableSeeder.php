<?php

namespace App\Seeders;

class SubjectsTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding subjects table...\n";

        $subjects = [
            // Computer Science subjects
            [
                'code' => 'CS101',
                'name' => 'Introduction to Programming',
                'description' => 'Fundamentals of programming using Python',
                'credit_hours' => 3,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'CS102',
                'name' => 'Data Structures and Algorithms',
                'description' => 'Core concepts of data structures and algorithm design',
                'credit_hours' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'CS201',
                'name' => 'Web Development',
                'description' => 'Front-end and back-end web technologies',
                'credit_hours' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'CS202',
                'name' => 'Database Systems',
                'description' => 'Database design and SQL fundamentals',
                'credit_hours' => 3,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // English Literature subjects
            [
                'code' => 'EN101',
                'name' => 'English Composition',
                'description' => 'Writing skills and essay composition',
                'credit_hours' => 3,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'EN102',
                'name' => 'British Literature',
                'description' => 'Survey of British literature from medieval to modern period',
                'credit_hours' => 3,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'EN201',
                'name' => 'American Literature',
                'description' => 'Exploration of American literary tradition',
                'credit_hours' => 3,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Biology subjects
            [
                'code' => 'BI101',
                'name' => 'Cell Biology',
                'description' => 'Structure and function of cells',
                'credit_hours' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'BI102',
                'name' => 'Genetics',
                'description' => 'Principles of heredity and genetic variation',
                'credit_hours' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Mathematics subjects
            [
                'code' => 'MA101',
                'name' => 'Calculus I',
                'description' => 'Differential calculus and limits',
                'credit_hours' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'MA102',
                'name' => 'Linear Algebra',
                'description' => 'Matrices, vectors, and linear systems',
                'credit_hours' => 3,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->insertMany('subjects', $subjects);
        echo "Seeded " . count($subjects) . " subjects.\n";
    }
}
