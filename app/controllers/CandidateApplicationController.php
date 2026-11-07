<?php

namespace App\Controllers;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\Job;
use App\Models\SavedJob;
use Core\Auth;
use Core\Csrf;
use Core\MatchScoreCalculator;
use Core\Router;
use Core\View;

class CandidateApplicationController
{
    public function apply(int $jobId): void
    {
        Auth::requireRole('candidate');
        if (!Csrf::verifyRequest()) {
            Router::redirect("/jobs/{$jobId}");
        }

        $job = Job::find($jobId);
        if (!$job || $job['status'] !== 'open' || $job['moderation_status'] !== 'active' || $job['recruiter_status'] !== 'approved') {
            View::flash('error', 'This job is no longer accepting applications.');
            Router::redirect('/jobs');
        }

        if (Application::findByJobAndCandidate($jobId, Auth::id())) {
            View::flash('error', 'You have already applied to this job.');
            Router::redirect("/jobs/{$jobId}");
        }

        $scoreResult = MatchScoreCalculator::compute(Auth::id(), $jobId);
        try {
            Application::create($jobId, Auth::id(), $scoreResult);
        } catch (\PDOException $e) {
            // `applications` has a UNIQUE (job_id, candidate_id) key, so a
            // double-submitted Apply (impatient double-click / refreshed POST)
            // loses the race on the second insert rather than creating two
            // applications. Report it as the same "already applied" outcome the
            // pre-check gives instead of a 500.
            if ($e->getCode() === '23000') {
                View::flash('error', 'You have already applied to this job.');
                Router::redirect('/candidate/applications');
            }
            throw $e;
        }

        View::flash('success', 'Application submitted! You can view your match-score breakdown any time from My Applications.');
        Router::redirect('/candidate/applications');
    }

    public function index(): void
    {
        Auth::requireRole('candidate');
        $applications = Application::listForCandidate(Auth::id());
        View::render('candidate/applications/index', ['applications' => $applications]);
    }

    public function show(int $id): void
    {
        Auth::requireRole('candidate');
        $application = Application::find($id);
        if (!$application || (int) $application['candidate_id'] !== Auth::id()) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            return;
        }
        $history = ApplicationStatusHistory::listForApplication($id);
        View::render('candidate/applications/show', ['application' => $application, 'history' => $history]);
    }

    public function withdraw(int $id): void
    {
        Auth::requireRole('candidate');
        $application = Application::find($id);
        if (!$application || (int) $application['candidate_id'] !== Auth::id()) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            return;
        }
        if (!Csrf::verifyRequest()) {
            Router::redirect('/candidate/applications');
        }

        if (!in_array($application['status'], ['applied', 'shortlisted'], true)) {
            View::flash('error', 'This application can no longer be withdrawn.');
            Router::redirect('/candidate/applications');
        }

        Application::updateStatus($id, 'withdrawn');
        ApplicationStatusHistory::record($id, $application['status'], 'withdrawn', Auth::id(), null);

        View::flash('success', 'Application withdrawn.');
        Router::redirect('/candidate/applications');
    }

    public function save(int $jobId): void
    {
        Auth::requireRole('candidate');
        if (!Csrf::verifyRequest()) {
            Router::redirect($_POST['redirect_to'] ?? '/jobs');
        }
        SavedJob::save(Auth::id(), $jobId);
        View::flash('success', 'Job saved.');
        Router::redirect($_POST['redirect_to'] ?? '/jobs');
    }

    public function unsave(int $jobId): void
    {
        Auth::requireRole('candidate');
        if (!Csrf::verifyRequest()) {
            Router::redirect($_POST['redirect_to'] ?? '/candidate/saved-jobs');
        }
        SavedJob::unsave(Auth::id(), $jobId);
        View::flash('success', 'Job removed from saved list.');
        Router::redirect($_POST['redirect_to'] ?? '/candidate/saved-jobs');
    }

    public function savedIndex(): void
    {
        Auth::requireRole('candidate');
        View::render('candidate/saved_jobs', ['jobs' => SavedJob::listForCandidate(Auth::id())]);
    }
}
