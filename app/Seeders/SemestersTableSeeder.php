<?php

namespace App\Seeders;

class SemestersTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding semesters table...\n";

        $semesters = [
            // Program 1 (BSCS) - 4 years = 8 semesters
            [
                'program_id' => 1,
                'semester_number' => 1,
                'academic_year' => '2025-2026',
                'is_current' => 1,
            ],
            [
                'program_id' => 1,
                'semester_number' => 2,
                'academic_year' => '2025-2026',
                'is_current' => 1,
            ],
            [
                'program_id' => 1,
                'semester_number' => 3,
                'academic_year' => '2026-2027',
                'is_current' => 0,
            ],
            [
                'program_id' => 1,
                'semester_number' => 4,
                'academic_year' => '2026-2027',
                'is_current' => 0,
            ],
            // Program 2 (BAEL)
            [
                'program_id' => 2,
                'semester_number' => 1,
                'academic_year' => '2025-2026',
                'is_current' => 1,
            ],
            [
                'program_id' => 2,
                'semester_number' => 2,
                'academic_year' => '2025-2026',
                'is_current' => 1,
            ],
            // Program 3 (BSBI)
            [
                'program_id' => 3,
                'semester_number' => 1,
                'academic_year' => '2025-2026',
                'is_current' => 1,
            ],
            [
                'program_id' => 3,
                'semester_number' => 2,
                'academic_year' => '2025-2026',
                'is_current' => 1,
            ],
            // Program 4 (BSMA)
            [
                'program_id' => 4,
                'semester_number' => 1,
                'academic_year' => '2025-2026',
                'is_current' => 1,
            ],
            [
                'program_id' => 4,
                'semester_number' => 2,
                'academic_year' => '2025-2026',
                'is_current' => 1,
            ],
            // Program 5 (ASBA) - 2 years = 4 semesters
            [
                'program_id' => 5,
                'semester_number' => 1,
                'academic_year' => '2025-2026',
                'is_current' => 1,
            ],
            [
                'program_id' => 5,
                'semester_number' => 2,
                'academic_year' => '2025-2026',
                'is_current' => 1,
            ],
        ];

        $this->insertMany('semesters', $semesters);
        echo "Seeded " . count($semesters) . " semesters.\n";
    }
}
