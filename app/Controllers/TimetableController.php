<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\TimetableModel;

class TimetableController extends BaseController
{
    private TimetableModel $timetable;

    public function __construct()
    {
        parent::__construct();
        $this->timetable = new TimetableModel();
    }

    public function index(): void
    {
        $timetables = $this->timetable->all();

        $this->view('timetables.index', [
            'title' => 'Timetables',
            'timetables' => $timetables ?? [],
        ]);
    }

    public function create(): void
    {
        $this->view('timetables.create', [
            'title' => 'Create Timetable',
        ]);
    }

    public function show(int $id): void
    {
        $timetable = $this->timetable->find($id);

        $this->view('timetables.show', [
            'title' => 'Timetable Details',
            'timetable' => $timetable ?? [],
        ]);
    }

    public function edit(int $id): void
    {
        $timetable = $this->timetable->find($id);

        $this->view('timetables.edit', [
            'title' => 'Edit Timetable',
            'timetable' => $timetable ?? [],
        ]);
    }

    public function store(): void
    {
        $semesterId = (int) $this->input('semester_id', 0);
        $dayOfWeek = (string) $this->input('day_of_week', '');
        $startTime = (string) $this->input('start_time', '');
        $endTime = (string) $this->input('end_time', '');

        if ($semesterId === 0 || $dayOfWeek === '' || $startTime === '' || $endTime === '') {
            $this->json([
                'success' => false,
                'message' => 'All timetable fields are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Timetable created successfully.',
        ], 201);
    }

    public function update(int $id): void
    {
        $dayOfWeek = (string) $this->input('day_of_week', '');
        $startTime = (string) $this->input('start_time', '');
        $endTime = (string) $this->input('end_time', '');

        if ($dayOfWeek === '' || $startTime === '' || $endTime === '') {
            $this->json([
                'success' => false,
                'message' => 'Day of week, start time, and end time are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Timetable updated successfully.',
            'data' => ['id' => $id],
        ]);
    }

    public function destroy(int $id): void
    {
        $this->json([
            'success' => true,
            'message' => 'Timetable deleted successfully.',
            'data' => ['id' => $id],
        ]);
    }
}
