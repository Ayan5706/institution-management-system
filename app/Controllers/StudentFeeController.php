<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\StudentFeeModel;

class StudentFeeController extends BaseController
{
    private StudentFeeModel $fee;

    public function __construct()
    {
        parent::__construct();
        $this->fee = new StudentFeeModel();
    }

    public function index(): void
    {
        $fees = $this->fee->all();

        $this->view('fees.index', [
            'title' => 'Student Fees',
            'fees' => $fees ?? [],
        ]);
    }

    public function create(): void
    {
        $this->view('fees.create', [
            'title' => 'Record Student Fee',
        ]);
    }

    public function show(int $id): void
    {
        $fee = $this->fee->find($id);

        $this->view('fees.show', [
            'title' => 'Fee Details',
            'fee' => $fee ?? [],
        ]);
    }

    public function edit(int $id): void
    {
        $fee = $this->fee->find($id);

        $this->view('fees.edit', [
            'title' => 'Edit Fee Record',
            'fee' => $fee ?? [],
        ]);
    }

    public function store(): void
    {
        $studentId = (int) $this->input('student_id', 0);
        $amount = (float) $this->input('amount', 0);
        $dueDate = (string) $this->input('due_date', '');

        if ($studentId === 0 || $amount <= 0 || $dueDate === '') {
            $this->json([
                'success' => false,
                'message' => 'Student, amount, and due date are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Student fee recorded successfully.',
        ], 201);
    }

    public function update(int $id): void
    {
        $amount = (float) $this->input('amount', 0);
        $dueDate = (string) $this->input('due_date', '');

        if ($amount <= 0 || $dueDate === '') {
            $this->json([
                'success' => false,
                'message' => 'Amount and due date are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Student fee updated successfully.',
            'data' => ['id' => $id],
        ]);
    }

    public function destroy(int $id): void
    {
        $this->json([
            'success' => true,
            'message' => 'Fee record deleted successfully.',
            'data' => ['id' => $id],
        ]);
    }
}
