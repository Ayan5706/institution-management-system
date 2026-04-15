<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ProgramModel;
use App\Models\SemesterModel;
use App\Models\SubjectModel;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $userRole = (string) ($_SESSION['user_role'] ?? 'STUDENT');
        $userName = (string) ($_SESSION['user_name'] ?? 'User');

        $userModel = new UserModel();
        $programModel = new ProgramModel();
        $semesterModel = new SemesterModel();
        $subjectModel = new SubjectModel();

        $totalUsers = count($userModel->all());
        $totalPrograms = count($programModel->all());
        $totalSemesters = count($semesterModel->all());
        $totalSubjects = count($subjectModel->all());
        
        // Default dashboard data
        $defaultData = [
            'title' => 'Dashboard',
            'user_name' => $userName,
            'user_role' => $userRole,
            'summary' => [
                'total_users' => $totalUsers,
                'total_programs' => $totalPrograms,
                'total_semesters' => $totalSemesters,
                'total_subjects' => $totalSubjects,
            ],
        ];
        
        // Route to role-specific dashboard
        match (strtoupper($userRole)) {
            'PRINCIPAL' => $this->redirect('/principal/dashboard'),
            'VP' => $this->view('dashboard.vp', $defaultData),
            'MANAGER' => $this->view('dashboard.manager', $defaultData),
            'ACCOUNTANT' => $this->view('dashboard.accountant', $defaultData),
            'STUDENT' => $this->redirect('/student/dashboard'),
            default => $this->view('dashboard.index', $defaultData),
        };
    }
}
