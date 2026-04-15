<?php

namespace App\Seeders;

class SemestersTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding semesters table...\n";

        $semesters = [
            // Year 1
            [
                'program_id' => 1,
                'year' => 1,
                'semester' => 1,
                'start_date' => '2026-01-15',
                'end_date' => '2026-05-15',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'program_id' => 1,
                'year' => 1,
                'semester' => 2,
                'start_date' => '2026-06-01',
                'end_date' => '2026-10-01',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Year 2
            [
                'program_id' => 1,
                'year' => 2,
                'semester' => 1,
                'start_date' => '2027-01-15',
                'end_date' => '2027-05-15',
                'is_active' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'program_id' => 1,
                'year' => 2,
                'semester' => 2,
                'start_date' => '2027-06-01',
                'end_date' => '2027-10-01',
                'is_active' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Program 2 semesters
            [
                'program_id' => 2,
                'year' => 1,
                'semester' => 1,
                'start_date' => '2026-01-15',
                'end_date' => '2026-05-15',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'program_id' => 2,
                'year' => 1,
                'semester' => 2,
                'start_date' => '2026-06-01',
                'end_date' => '2026-10-01',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Program 3 semesters
            [
                'program_id' => 3,
                'year' => 1,
                'semester' => 1,
                'start_date' => '2026-01-15',
                'end_date' => '2026-05-15',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'program_id' => 3,
                'year' => 1,
                'semester' => 2,
                'start_date' => '2026-06-01',
                'end_date' => '2026-10-01',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->insertMany('semesters', $semesters);
        echo "Seeded " . count($semesters) . " semesters.\n";
    }
}
