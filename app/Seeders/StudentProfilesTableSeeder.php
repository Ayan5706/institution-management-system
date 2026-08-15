<?php

namespace App\Seeders;

class StudentProfilesTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding student_profiles table...\n";

        $profiles = [
            // BCA Program (program_id = 1)
            // janderson (user_id=6)
            [
                'user_id' => 6,
                'registration_number' => 'BCA2025001',
                'date_of_birth' => '2004-05-15',
                'program_id' => 1,
            ],
            // sbrown (user_id=7)
            [
                'user_id' => 7,
                'registration_number' => 'BCA2025002',
                'date_of_birth' => '2004-08-22',
                'program_id' => 1,
            ],
            // mharris (user_id=8)
            [
                'user_id' => 8,
                'registration_number' => 'BCA2025003',
                'date_of_birth' => '2004-03-10',
                'program_id' => 1,
            ],
            // ltaylor (user_id=9)
            [
                'user_id' => 9,
                'registration_number' => 'BCA2025004',
                'date_of_birth' => '2004-11-30',
                'program_id' => 1,
            ],

            // MSC Program (program_id = 2)
            // dmiller (user_id=10)
            [
                'user_id' => 10,
                'registration_number' => 'MSC2025001',
                'date_of_birth' => '2004-07-14',
                'program_id' => 2,
            ],
            // asingh (user_id=11)
            [
                'user_id' => 11,
                'registration_number' => 'MSC2025002',
                'date_of_birth' => '2003-02-20',
                'program_id' => 2,
            ],
            // rdesai (user_id=12)
            [
                'user_id' => 12,
                'registration_number' => 'MSC2025003',
                'date_of_birth' => '2003-06-18',
                'program_id' => 2,
            ],
            // nkumar (user_id=13)
            [
                'user_id' => 13,
                'registration_number' => 'MSC2025004',
                'date_of_birth' => '2003-09-12',
                'program_id' => 2,
            ],
        ];

        $this->insertMany('student_profiles', $profiles);
        echo "Seeded " . count($profiles) . " student profiles.\n";
    }
}
