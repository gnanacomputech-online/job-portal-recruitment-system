<?php

namespace App\Controllers;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\Job;
use App\Models\User;
use Core\Auth;
use Core\Csrf;
use Core\Mailer;
use Core\Router;
use Core\View;

class RecruiterApplicantController
{
    private const ALLOWED_TRANSITIONS = [
        'applied' => ['shortlisted', 'rejected'],
        'shortlisted' => ['interview', 'rejected'],
        'interview' => ['offered', 'rejected'],
        'offered' => ['rejected'],
    ];

    public function index(int $jobId): void
    {
        Auth::requireRole('recruiter');
        $job = Job::find($jobId);
        if (!$job || (int) $job['recruiter_id'] !== Auth::id()) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            return;
        }

        $applicants = Application::listForJob($jobId);
        View::render('recruiter/applicants/index', ['job' => $job, 'applicants' => $applicants]);
    }

    public function show(int $id): void
    {
        Auth::requireRole('recruiter');
        $application = Application::find($id);
        if (!$application || (int) $application['recruiter_id'] !== Auth::id()) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            return;
        }
        $history = ApplicationStatusHistory::listForApplication($id);
        $pipelineOpen = $application['job_moderation_status'] === 'active'
            && $application['candidate_status'] === 'approved';
        $transitions = $pipelineOpen ? (self::ALLOWED_TRANSITIONS[$application['status']] ?? []) : [];
        View::render('recruiter/applicants/show', [
            'application' => $application,
            'history' => $history,
            'transitions' => $transitions,
        ]);
    }

    public function updateStatus(int $id): void
    {
        Auth::requireRole('recruiter');
        $application = Application::find($id);
        if (!$application || (int) $application['recruiter_id'] !== Auth::id()) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            return;
        }
        if (!Csrf::verifyRequest()) {
            Router::redirect("/recruiter/applications/{$id}");
        }
        if ($application['job_moderation_status'] !== 'active') {
            View::flash('error', 'This job posting has been flagged/removed by an admin — applicant status can no longer be changed.');
            Router::redirect("/recruiter/applications/{$id}");
        }
        // Same reasoning the moderation check above encodes, from the other
        // side: an admin suspending the candidate mid-pipeline must stop the
        // pipeline, not let them keep being shortlisted/interviewed/offered
        // (and emailed about it) on a deactivated account.
        if ($application['candidate_status'] !== 'approved') {
            View::flash('error', 'This candidate\'s account is no longer active — their application can no longer be advanced.');
            Router::redirect("/recruiter/applications/{$id}");
        }

        $newStatus = $_POST['status'] ?? '';
        $note = trim($_POST['note'] ?? '');
        $allowed = self::ALLOWED_TRANSITIONS[$application['status']] ?? [];

        if (!in_array($newStatus, $allowed, true)) {
            View::flash('error', 'That status change is not allowed from the current stage.');
            Router::redirect("/recruiter/applications/{$id}");
        }

        Application::updateStatus($id, $newStatus);
        ApplicationStatusHistory::record($id, $application['status'], $newStatus, Auth::id(), $note ?: null);

        $candidate = User::findById((int) $application['candidate_id']);
        if ($candidate) {
            Mailer::send($candidate['email'], $candidate['name'], 'TalentFit — Application status update',
                '<p>Hi ' . e($candidate['name']) . ',</p><p>Your application for <strong>' . e($application['job_title']) . '</strong> at ' . e($application['company_name']) . ' is now: <strong>' . e($newStatus) . '</strong>.</p>' .
                ($note ? '<p>Note from the recruiter: ' . e($note) . '</p>' : ''));
        }

        View::flash('success', 'Applicant status updated.');
        Router::redirect("/recruiter/applications/{$id}");
    }
}
