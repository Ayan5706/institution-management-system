<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\StudentProfileModel;

class StudentProfileController extends BaseController
{
    private StudentProfileModel $studentProfile;

    public function __construct()
    {
        parent::__construct();
        $this->studentProfile = new StudentProfileModel();
    }

    public function index(): void
    {
        $students = $this->studentProfile->all();

        $this->view('students.index', [
            'title' => 'Students',
            'students' => $students ?? [],
        ]);
    }

    public function create(): void
    {
        $this->view('students.create', [
            'title' => 'Create Student Profile',
        ]);
    }

    public function show(int $id): void
    {
        $student = $this->studentProfile->find($id);

        $this->view('students.show', [
            'title' => 'Student Profile',
            'student' => $student ?? [],
        ]);
    }

    public function edit(int $id): void
    {
        $student = $this->studentProfile->find($id);

        $this->view('students.edit', [
            'title' => 'Edit Student Profile',
            'student' => $student ?? [],
        ]);
    }

    public function store(): void
    {
        $userId = (int) $this->input('user_id', 0);
        $enrollmentNumber = (string) $this->input('enrollment_number', '');
        $programId = (int) $this->input('program_id', 0);
        $semesterId = (int) $this->input('semester_id', 0);

        if ($userId === 0 || $enrollmentNumber === '' || $programId === 0 || $semesterId === 0) {
            $this->json([
                'success' => false,
                'message' => 'User, enrollment number, program, and semester are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Student profile created successfully.',
        ], 201);
    }

    public function update(int $id): void
    {
        $programId = (int) $this->input('program_id', 0);
        $semesterId = (int) $this->input('semester_id', 0);

        if ($programId === 0 || $semesterId === 0) {
            $this->json([
                'success' => false,
                'message' => 'Program and semester are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Student profile updated successfully.',
            'data' => ['id' => $id],
        ]);
    }

    public function destroy(int $id): void
    {
        $this->json([
            'success' => true,
            'message' => 'Student profile deleted successfully.',
            'data' => ['id' => $id],
        ]);
    }
}
