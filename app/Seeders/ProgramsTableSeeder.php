<?php

namespace App\Seeders;

class ProgramsTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding programs table...\n";

        $programs = [
            [
                'program_name' => 'Bachelor of Computer Applications',
                'program_code' => 'BCA',
                'duration_semesters' => 6,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'program_name' => 'Master of Science in Computer Science',
                'program_code' => 'MSC',
                'duration_semesters' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->insertMany('programs', $programs);
        echo "Seeded " . count($programs) . " programs.\n";
    }
}
