<?php

declare(strict_types=1);

namespace App\Controllers;

class HomeController extends BaseController
{
    /**
     * Display the landing/home page
     */
    public function landing(): void
    {
        $_SESSION['visited_home'] = true;
        $this->view('home.landing', [
            'title' => 'Welcome to IMS - Institution Management System',
            'features' => [
                [
                    'title' => 'Student Management',
                    'description' => 'Manage student profiles, enrollment details, and academic records efficiently.',
                    'icon' => 'user-graduate.png',
                ],
                [
                    'title' => 'Attendance Tracking',
                    'description' => 'Track and manage student attendance with real-time updates and reports.',
                    'icon' => 'attendance.png',
                ],
                [
                    'title' => 'Fee Management',
                    'description' => 'Handle semester fees, payments, and track pending balances seamlessly.',
                    'icon' => 'fee.png',
                ],
                [
                    'title' => 'Class Scheduling',
                    'description' => 'Create and manage class timetables with structured scheduling.',
                    'icon' => 'timetable.png',
                ],
                [
                    'title' => 'Teacher Management',
                    'description' => 'Manage teacher profiles, subject assignments, and workload distribution.',
                    'icon' => 'teacher.png',
                ],
                [
                    'title' => 'Reporting & Analytics',
                    'description' => 'Generate reports on student performance, attendance, and financial data.',
                    'icon' => 'bar-chart.png',
                ],
            ],
        ]);
    }
}
