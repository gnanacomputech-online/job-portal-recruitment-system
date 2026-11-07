<?php

namespace App\Controllers;

use App\Models\Job;
use App\Models\RecruiterProfile;
use Core\Auth;
use Core\Database;
use Core\View;

class RecruiterDashboardController
{
    public function index(): void
    {
        Auth::requireRole('recruiter');
        $recruiterId = Auth::id();
        $user = Auth::user();
        $jobs = Job::listForRecruiter($recruiterId);

        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.recruiter_id = ?'
        );
        $stmt->execute([$recruiterId]);
        $totalApplicants = (int) $stmt->fetchColumn();

        View::render('recruiter/dashboard', [
            'user' => $user,
            'profile' => RecruiterProfile::find($recruiterId),
            'jobs' => $jobs,
            'openJobsCount' => count(array_filter($jobs, fn($j) => $j['status'] === 'open')),
            'totalApplicants' => $totalApplicants,
        ]);
    }
}
