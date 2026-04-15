<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;

interface MiddlewareInterface
{
    /**
     * Return true to continue request pipeline, false to stop.
     * Middleware may set headers/response before returning false.
     *
     * @param array<string, string> $params
     */
    public function handle(Request $request, array $params = []): bool;
}
