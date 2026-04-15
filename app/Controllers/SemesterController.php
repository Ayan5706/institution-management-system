<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SemesterModel;

class SemesterController extends BaseController
{
    private SemesterModel $semester;

    public function __construct()
    {
        parent::__construct();
        $this->semester = new SemesterModel();
    }

    public function index(): void
    {
        $semesters = $this->semester->all();

        $this->view('semesters.index', [
            'title' => 'Semesters',
            'semesters' => $semesters ?? [],
        ]);
    }

    public function create(): void
    {
        $this->view('semesters.create', [
            'title' => 'Create Semester',
        ]);
    }

    public function show(int $id): void
    {
        $semester = $this->semester->find($id);

        $this->view('semesters.show', [
            'title' => 'Semester Details',
            'semester' => $semester ?? [],
        ]);
    }

    public function edit(int $id): void
    {
        $semester = $this->semester->find($id);

        $this->view('semesters.edit', [
            'title' => 'Edit Semester',
            'semester' => $semester ?? [],
        ]);
    }

    public function store(): void
    {
        $semesterName = (string) $this->input('semester_name', '');
        $startDate = (string) $this->input('start_date', '');
        $endDate = (string) $this->input('end_date', '');

        if ($semesterName === '' || $startDate === '' || $endDate === '') {
            $this->json([
                'success' => false,
                'message' => 'Semester name, start date, and end date are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Semester created successfully.',
        ], 201);
    }

    public function update(int $id): void
    {
        $semesterName = (string) $this->input('semester_name', '');
        $startDate = (string) $this->input('start_date', '');
        $endDate = (string) $this->input('end_date', '');

        if ($semesterName === '' || $startDate === '' || $endDate === '') {
            $this->json([
                'success' => false,
                'message' => 'Semester name, start date, and end date are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Semester updated successfully.',
            'data' => ['id' => $id],
        ]);
    }

    public function destroy(int $id): void
    {
        $this->json([
            'success' => true,
            'message' => 'Semester deleted successfully.',
            'data' => ['id' => $id],
        ]);
    }
}
