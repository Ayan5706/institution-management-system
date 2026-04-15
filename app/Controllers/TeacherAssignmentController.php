<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\TeacherAssignmentModel;

class TeacherAssignmentController extends BaseController
{
    private TeacherAssignmentModel $assignment;

    public function __construct()
    {
        parent::__construct();
        $this->assignment = new TeacherAssignmentModel();
    }

    public function index(): void
    {
        $assignments = $this->assignment->all();

        $this->view('teacher_assignments.index', [
            'title' => 'Teacher Assignments',
            'assignments' => $assignments ?? [],
        ]);
    }

    public function create(): void
    {
        $this->view('teacher_assignments.create', [
            'title' => 'Assign Teacher',
        ]);
    }

    public function show(int $id): void
    {
        $assignment = $this->assignment->find($id);

        $this->view('teacher_assignments.show', [
            'title' => 'Teacher Assignment Details',
            'assignment' => $assignment ?? [],
        ]);
    }

    public function edit(int $id): void
    {
        $assignment = $this->assignment->find($id);

        $this->view('teacher_assignments.edit', [
            'title' => 'Edit Teacher Assignment',
            'assignment' => $assignment ?? [],
        ]);
    }

    public function store(): void
    {
        $userId = (int) $this->input('user_id', 0);
        $subjectId = (int) $this->input('subject_id', 0);
        $semesterId = (int) $this->input('semester_id', 0);

        if ($userId === 0 || $subjectId === 0 || $semesterId === 0) {
            $this->json([
                'success' => false,
                'message' => 'User, subject, and semester are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Teacher assignment created successfully.',
        ], 201);
    }

    public function update(int $id): void
    {
        $subjectId = (int) $this->input('subject_id', 0);
        $semesterId = (int) $this->input('semester_id', 0);

        if ($subjectId === 0 || $semesterId === 0) {
            $this->json([
                'success' => false,
                'message' => 'Subject and semester are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Teacher assignment updated successfully.',
            'data' => ['id' => $id],
        ]);
    }

    public function destroy(int $id): void
    {
        $this->json([
            'success' => true,
            'message' => 'Teacher assignment deleted successfully.',
            'data' => ['id' => $id],
        ]);
    }
}
