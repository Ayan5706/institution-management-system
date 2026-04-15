<?php

namespace App\Seeders;

class ProgramsTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding programs table...\n";

        $programs = [
            [
                'name' => 'Bachelor of Science in Computer Science',
                'code' => 'BSCS',
                'description' => 'A comprehensive program in computer science covering theory and practice',
                'duration_years' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Bachelor of Arts in English Literature',
                'code' => 'BAEL',
                'description' => 'Study of literature, writing, and language studies',
                'duration_years' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Bachelor of Science in Biology',
                'code' => 'BSBI',
                'description' => 'Foundation in biological sciences and research methodology',
                'duration_years' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Bachelor of Science in Mathematics',
                'code' => 'BSMA',
                'description' => 'Advanced mathematical theory and applications',
                'duration_years' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Associate Degree in Business Administration',
                'code' => 'ASBA',
                'description' => 'Two-year program in business fundamentals and management',
                'duration_years' => 2,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->insertMany('programs', $programs);
        echo "Seeded " . count($programs) . " programs.\n";
    }
}
