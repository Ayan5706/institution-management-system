<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ProgramModel;

class ProgramController extends BaseController
{
    private ProgramModel $program;

    public function __construct()
    {
        parent::__construct();
        $this->program = new ProgramModel();
    }

    public function index(): void
    {
        $programs = $this->program->all();

        $this->view('programs.index', [
            'title' => 'Programs',
            'programs' => $programs ?? [],
        ]);
    }

    public function create(): void
    {
        $this->view('programs.create', [
            'title' => 'Create Program',
        ]);
    }

    public function show(int $id): void
    {
        $program = $this->program->find($id);

        $this->view('programs.show', [
            'title' => 'Program Details',
            'program' => $program ?? [],
        ]);
    }

    public function edit(int $id): void
    {
        $program = $this->program->find($id);

        $this->view('programs.edit', [
            'title' => 'Edit Program',
            'program' => $program ?? [],
        ]);
    }

    public function store(): void
    {
        $name = (string) $this->input('program_name', '');
        $code = (string) $this->input('program_code', '');

        if ($name === '' || $code === '') {
            $this->json([
                'success' => false,
                'message' => 'Program name and code are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Program created successfully.',
        ], 201);
    }

    public function update(int $id): void
    {
        $name = (string) $this->input('program_name', '');
        $code = (string) $this->input('program_code', '');

        if ($name === '' || $code === '') {
            $this->json([
                'success' => false,
                'message' => 'Program name and code are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Program updated successfully.',
            'data' => ['id' => $id],
        ]);
    }

    public function destroy(int $id): void
    {
        $this->json([
            'success' => true,
            'message' => 'Program deleted successfully.',
            'data' => ['id' => $id],
        ]);
    }
}
