<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Minimal landing controller proving the MVC foundation is wired correctly.
 */
final class HomeController extends BaseController
{
    /**
     * Displays the backend foundation status page.
     */
    public function index(Request $request): Response
    {
        return $this->renderView('home', [
            'title' => 'Backend Foundation Ready',
            'modules' => [
                'Authentication and RBAC',
                'Academic Management',
                'Students, Teachers, Accountants',
                'Attendance, Results, CBT',
                'Finance, Timetable, Reports, Audit Logs',
            ],
        ]);
    }

    /**
     * Demonstrates JSON responses for future API-style endpoints.
     */
    public function health(Request $request): Response
    {
        return $this->json([
            'status' => 'ok',
            'app' => app_config('app.name'),
            'time' => date('c'),
        ]);
    }
}
