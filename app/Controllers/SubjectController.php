<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SubjectModel;

class SubjectController extends BaseController
{
    private SubjectModel $subject;

    public function __construct()
    {
        parent::__construct();
        $this->subject = new SubjectModel();
    }

    public function index(): void
    {
        $subjects = $this->subject->all();

        $this->view('subjects.index', [
            'title' => 'Subjects',
            'subjects' => $subjects ?? [],
        ]);
    }

    public function create(): void
    {
        $this->view('subjects.create', [
            'title' => 'Create Subject',
        ]);
    }

    public function show(int $id): void
    {
        $subject = $this->subject->find($id);

        $this->view('subjects.show', [
            'title' => 'Subject Details',
            'subject' => $subject ?? [],
        ]);
    }

    public function edit(int $id): void
    {
        $subject = $this->subject->find($id);

        $this->view('subjects.edit', [
            'title' => 'Edit Subject',
            'subject' => $subject ?? [],
        ]);
    }

    public function store(): void
    {
        $subjectName = (string) $this->input('subject_name', '');
        $code = (string) $this->input('subject_code', '');
        $semesterId = (int) $this->input('semester_id', 0);

        if ($subjectName === '' || $code === '' || $semesterId === 0) {
            $this->json([
                'success' => false,
                'message' => 'Subject name, code, and semester are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Subject created successfully.',
        ], 201);
    }

    public function update(int $id): void
    {
        $subjectName = (string) $this->input('subject_name', '');
        $code = (string) $this->input('subject_code', '');
        $semesterId = (int) $this->input('semester_id', 0);

        if ($subjectName === '' || $code === '' || $semesterId === 0) {
            $this->json([
                'success' => false,
                'message' => 'Subject name, code, and semester are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Subject updated successfully.',
            'data' => ['id' => $id],
        ]);
    }

    public function destroy(int $id): void
    {
        $this->json([
            'success' => true,
            'message' => 'Subject deleted successfully.',
            'data' => ['id' => $id],
        ]);
    }
}
