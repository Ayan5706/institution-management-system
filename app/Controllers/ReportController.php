<?php

declare(strict_types=1);

namespace App\Controllers;

class ReportController extends BaseController
{
    public function index(): void
    {
        $this->view('reports.index', [
            'title' => 'Reports',
        ]);
    }

    public function academic(): void
    {
        $this->view('reports.academic', [
            'title' => 'Academic Summary',
        ]);
    }
}
