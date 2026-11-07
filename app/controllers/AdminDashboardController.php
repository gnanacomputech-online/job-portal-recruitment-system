<?php

namespace App\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\SkillRequest;
use App\Models\User;
use Core\Auth;
use Core\View;

class AdminDashboardController
{
    public function index(): void
    {
        Auth::requireRole('admin');

        $stats = [
            'candidates' => User::countByRole('candidate'),
            'recruiters_approved' => User::countByRoleStatus('recruiter', 'approved'),
            'recruiters_pending' => User::countByRoleStatus('recruiter', 'pending'),
            'jobs_open' => Job::countOpen(),
            'jobs_total' => Job::countTotal(),
            'applications_total' => Application::countTotal(),
            'skill_requests_pending' => count(SkillRequest::listPending()),
        ];

        View::render('admin/dashboard', ['stats' => $stats]);
    }
}
