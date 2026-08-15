<?php

namespace App\Seeders;

class SemestersTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding semesters table...\n";

        $semesters = [
            // Program 1 (BCA)
            ['program_id' => 1, 'semester_number' => 1, 'academic_year' => '2025-2026', 'is_current' => 1],
            ['program_id' => 1, 'semester_number' => 2, 'academic_year' => '2025-2026', 'is_current' => 1],

            // Program 2 (MSC)
            ['program_id' => 2, 'semester_number' => 1, 'academic_year' => '2025-2026', 'is_current' => 1],
            ['program_id' => 2, 'semester_number' => 2, 'academic_year' => '2025-2026', 'is_current' => 1],
        ];

        $this->insertMany('semesters', $semesters);
        echo "Seeded " . count($semesters) . " semesters.\n";
    }
}
