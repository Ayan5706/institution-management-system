<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AttendanceModel;

class AttendanceController extends BaseController
{
    private AttendanceModel $attendance;

    public function __construct()
    {
        $this->attendance = new AttendanceModel();
    }

    public function index(): void
    {
        $attendances = $this->attendance->all();

        $this->view('attendance.index', [
            'title' => 'Attendance Records',
            'attendances' => $attendances ?? [],
        ]);
    }

    public function create(): void
    {
        $this->view('attendance.create', [
            'title' => 'Record Attendance',
        ]);
    }

    public function show(int $id): void
    {
        $attendance = $this->attendance->find($id);

        $this->view('attendance.show', [
            'title' => 'Attendance Details',
            'attendance' => $attendance ?? [],
        ]);
    }

    public function edit(int $id): void
    {
        $attendance = $this->attendance->find($id);

        $this->view('attendance.edit', [
            'title' => 'Edit Attendance',
            'attendance' => $attendance ?? [],
        ]);
    }

    public function store(): void
    {
        $studentId = (int) $this->input('student_id', 0);
        $classDate = (string) $this->input('class_date', '');
        $status = (string) $this->input('status', '');

        if ($studentId === 0 || $classDate === '' || $status === '') {
            $this->json([
                'success' => false,
                'message' => 'Student, date, and status are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Attendance recorded successfully.',
        ], 201);
    }

    public function update(int $id): void
    {
        $status = (string) $this->input('status', '');

        if ($status === '') {
            $this->json([
                'success' => false,
                'message' => 'Attendance status is required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Attendance updated successfully.',
            'data' => ['id' => $id],
        ]);
    }

    public function destroy(int $id): void
    {
        $this->json([
            'success' => true,
            'message' => 'Attendance record deleted successfully.',
            'data' => ['id' => $id],
        ]);
    }
}
