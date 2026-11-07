<?php

namespace App\Controllers;

use App\Models\Job;
use App\Models\JobSkill;
use App\Models\RecruiterProfile;
use App\Models\SavedJob;
use Core\Auth;
use Core\MatchScoreCalculator;
use Core\View;

/** Public/candidate job browsing, job detail + live match breakdown, public company page. */
class JobController
{
    public function browse(): void
    {
        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'location' => trim($_GET['location'] ?? ''),
            'job_type' => trim($_GET['job_type'] ?? ''),
        ];
        $jobs = Job::browse($filters);

        $candidateId = Auth::role() === 'candidate' ? Auth::id() : null;
        $savedIds = $candidateId ? SavedJob::savedJobIds($candidateId) : [];

        foreach ($jobs as &$job) {
            $job['match_score'] = $candidateId ? MatchScoreCalculator::compute($candidateId, (int) $job['id'])['overall'] : null;
            $job['is_saved'] = in_array((int) $job['id'], $savedIds, true);
        }
        unset($job);

        $sortBy = $_GET['sort'] ?? 'recent';
        if ($sortBy === 'match' && $candidateId) {
            usort($jobs, fn($a, $b) => $b['match_score'] <=> $a['match_score']);
        }

        View::render('jobs/browse', ['jobs' => $jobs, 'filters' => $filters, 'sortBy' => $sortBy]);
    }

    public function show(int $id): void
    {
        $job = Job::find($id);
        if (!$job || $job['moderation_status'] !== 'active' || $job['recruiter_status'] !== 'approved') {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        // A draft is an unpublished posting — it never appears in browse, so it
        // must not be readable by URL either. Its own recruiter (and an admin)
        // can still open it to preview what it will look like.
        if ($job['status'] === 'draft') {
            $isOwner = Auth::role() === 'recruiter' && (int) $job['recruiter_id'] === Auth::id();
            if (!$isOwner && Auth::role() !== 'admin') {
                http_response_code(404);
                require __DIR__ . '/../views/errors/404.php';
                return;
            }
        }

        $requirements = JobSkill::listForJob($id);
        $score = null;
        $isSaved = false;
        $alreadyApplied = false;

        if (Auth::role() === 'candidate') {
            $score = MatchScoreCalculator::compute(Auth::id(), $id);
            $isSaved = \App\Models\SavedJob::isSaved(Auth::id(), $id);
            $alreadyApplied = \App\Models\Application::findByJobAndCandidate($id, Auth::id()) !== null;
        }

        View::render('jobs/show', [
            'job' => $job,
            'requirements' => $requirements,
            'score' => $score,
            'isSaved' => $isSaved,
            'alreadyApplied' => $alreadyApplied,
        ]);
    }

    public function company(int $recruiterId): void
    {
        $company = RecruiterProfile::findWithUser($recruiterId);
        if (!$company || $company['account_status'] !== 'approved') {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        $jobs = array_filter(Job::browse(), fn($j) => (int) $j['recruiter_id'] === $recruiterId);

        View::render('jobs/company', ['company' => $company, 'jobs' => $jobs]);
    }
}
