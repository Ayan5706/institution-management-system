<?php

namespace App\Seeders;

class StudentProfilesTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding student_profiles table...\n";

        $profiles = [
            // janderson (user_id=6)
            [
                'user_id' => 6,
                'registration_number' => 'CS2025001',
                'date_of_birth' => '2004-05-15',
                'program_id' => 1,
            ],
            // sbrown (user_id=7)
            [
                'user_id' => 7,
                'registration_number' => 'CS2025002',
                'date_of_birth' => '2004-08-22',
                'program_id' => 1,
            ],
            // mharris (user_id=8)
            [
                'user_id' => 8,
                'registration_number' => 'EN2025001',
                'date_of_birth' => '2004-03-10',
                'program_id' => 2,
            ],
            // ltaylor (user_id=9)
            [
                'user_id' => 9,
                'registration_number' => 'BI2025001',
                'date_of_birth' => '2004-11-30',
                'program_id' => 3,
            ],
            // dmiller (user_id=10)
            [
                'user_id' => 10,
                'registration_number' => 'MA2025001',
                'date_of_birth' => '2004-07-14',
                'program_id' => 4,
            ],
        ];

        $this->insertMany('student_profiles', $profiles);
        echo "Seeded " . count($profiles) . " student profiles.\n";
    }
}
