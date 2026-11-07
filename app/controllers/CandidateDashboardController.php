<?php

namespace App\Controllers;

use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\Job;
use Core\Auth;
use Core\MatchScoreCalculator;
use Core\View;

class CandidateDashboardController
{
    public function index(): void
    {
        Auth::requireRole('candidate');
        $candidateId = Auth::id();

        $jobs = Job::browse();
        foreach ($jobs as &$job) {
            $job['match_score'] = MatchScoreCalculator::compute($candidateId, (int) $job['id'])['overall'];
        }
        unset($job);
        usort($jobs, fn($a, $b) => $b['match_score'] <=> $a['match_score']);
        $recommended = array_slice($jobs, 0, 5);

        $applications = Application::listForCandidate($candidateId);
        $statusCounts = [];
        foreach ($applications as $app) {
            $statusCounts[$app['status']] = ($statusCounts[$app['status']] ?? 0) + 1;
        }

        View::render('candidate/dashboard', [
            'completeness' => CandidateProfile::completeness($candidateId),
            'recommended' => $recommended,
            'applications' => array_slice($applications, 0, 5),
            'statusCounts' => $statusCounts,
            'totalApplications' => count($applications),
        ]);
    }
}
