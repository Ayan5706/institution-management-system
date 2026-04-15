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
                    'description' => 'Comprehensive student profiles, enrollment tracking, and academic records management.',
                    'icon' => '👥',
                ],
                [
                    'title' => 'Attendance Tracking',
                    'description' => 'Real-time attendance marking, reports, and monitoring for all classes.',
                    'icon' => '📋',
                ],
                [
                    'title' => 'Fee Management',
                    'description' => 'Streamlined fee collection, payment processing, and financial reporting.',
                    'icon' => '💳',
                ],
                [
                    'title' => 'Class Scheduling',
                    'description' => 'Intelligent timetable management with conflict detection and optimization.',
                    'icon' => '📅',
                ],
                [
                    'title' => 'Teacher Management',
                    'description' => 'Manage teacher assignments, qualifications, and workload distribution.',
                    'icon' => '🎓',
                ],
                [
                    'title' => 'Reporting & Analytics',
                    'description' => 'Generate insightful reports on attendance, academics, and finances.',
                    'icon' => '📊',
                ],
            ],
        ]);
    }
}
