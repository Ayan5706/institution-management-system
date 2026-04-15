<?php

namespace App\Seeders;

class ProgramsTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding programs table...\n";

        $programs = [
            [
                'program_name' => 'Bachelor of Science in Computer Science',
                'program_code' => 'BSCS',
                'duration_semesters' => 8,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'program_name' => 'Bachelor of Arts in English Literature',
                'program_code' => 'BAEL',
                'duration_semesters' => 8,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'program_name' => 'Bachelor of Science in Biology',
                'program_code' => 'BSBI',
                'duration_semesters' => 8,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'program_name' => 'Bachelor of Science in Mathematics',
                'program_code' => 'BSMA',
                'duration_semesters' => 8,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'program_name' => 'Associate Degree in Business Administration',
                'program_code' => 'ASBA',
                'duration_semesters' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->insertMany('programs', $programs);
        echo "Seeded " . count($programs) . " programs.\n";
    }
}
