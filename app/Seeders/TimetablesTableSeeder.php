<?php

namespace App\Seeders;

class TimetablesTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding timetables table...\n";

        $timetables = [
            // BCA Semester 1
            // CS101 - Introduction to Programming (teacher_assignment_id: 1)
            ['teacher_assignment_id' => 1, 'day' => 'MON', 'start_time' => '09:00:00', 'end_time' => '10:30:00'],
            ['teacher_assignment_id' => 1, 'day' => 'WED', 'start_time' => '09:00:00', 'end_time' => '10:30:00'],

            // CS102 - Data Structures (teacher_assignment_id: 5)
            ['teacher_assignment_id' => 5, 'day' => 'TUE', 'start_time' => '09:00:00', 'end_time' => '10:30:00'],

            // MATH101 - Calculus I (teacher_assignment_id: 9)
            ['teacher_assignment_id' => 9, 'day' => 'THU', 'start_time' => '10:30:00', 'end_time' => '12:00:00'],

            // BCA Semester 2
            // CS201 - OOP (teacher_assignment_id: 2)
            ['teacher_assignment_id' => 2, 'day' => 'MON', 'start_time' => '12:00:00', 'end_time' => '13:30:00'],
            ['teacher_assignment_id' => 2, 'day' => 'WED', 'start_time' => '12:00:00', 'end_time' => '13:30:00'],

            // CS202 - Web Development (teacher_assignment_id: 6)
            ['teacher_assignment_id' => 6, 'day' => 'TUE', 'start_time' => '12:00:00', 'end_time' => '13:30:00'],

            // MATH102 - Calculus II (teacher_assignment_id: 10)
            ['teacher_assignment_id' => 10, 'day' => 'THU', 'start_time' => '13:30:00', 'end_time' => '15:00:00'],

            // MSC Semester 1
            // MACS101 - Advanced Algorithms (teacher_assignment_id: 3)
            ['teacher_assignment_id' => 3, 'day' => 'FRI', 'start_time' => '09:00:00', 'end_time' => '10:30:00'],

            // MACS102 - Distributed Systems (teacher_assignment_id: 7)
            ['teacher_assignment_id' => 7, 'day' => 'FRI', 'start_time' => '10:30:00', 'end_time' => '12:00:00'],

            // MSC Semester 2
            // MACS201 - Machine Learning Advanced (teacher_assignment_id: 4)
            ['teacher_assignment_id' => 4, 'day' => 'SAT', 'start_time' => '09:00:00', 'end_time' => '10:30:00'],

            // MACS202 - Natural Language Processing (teacher_assignment_id: 8)
            ['teacher_assignment_id' => 8, 'day' => 'SAT', 'start_time' => '10:30:00', 'end_time' => '12:00:00'],
        ];

        $this->insertMany('timetables', $timetables);
        echo "Seeded " . count($timetables) . " timetable entries.\n";
    }
}
