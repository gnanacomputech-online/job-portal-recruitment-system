<?php

namespace App\Controllers;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use Core\Auth;
use Core\View;

class AdminApplicationController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        $status = $_GET['status'] ?? null;
        $applications = Application::listAllForAdmin($status ?: null);
        View::render('admin/applications/index', ['applications' => $applications, 'status' => $status]);
    }

    public function show(int $id): void
    {
        Auth::requireRole('admin');
        $application = Application::find($id);
        if (!$application) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }
        $history = ApplicationStatusHistory::listForApplication($id);
        View::render('admin/applications/show', ['application' => $application, 'history' => $history]);
    }
}
