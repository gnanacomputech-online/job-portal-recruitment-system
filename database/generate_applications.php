<?php

/**
 * Standalone seed script for `applications` / `application_status_history`
 * (see the header comment in schema.sql). Deliberately independent of
 * core/bootstrap.php (no session, no router, no composer-autoloaded app
 * classes needed) — just Core\Database + Core\MatchScoreCalculator, so it
 * can run the moment schema.sql has been imported, the same standalone
 * spirit as project 1's install.php.
 *
 * Every match_score/score_breakdown inserted here is genuine output of
 * Core\MatchScoreCalculator::compute() run against the seeded candidate and
 * job data — not hand-typed numbers. Re-runnable: truncates both tables
 * first, so running it twice just regenerates the same demo data.
 *
 * Run from the command line: php database/generate_applications.php
 */

require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/MatchScoreCalculator.php';

use Core\Database;
use Core\MatchScoreCalculator;

$config = require __DIR__ . '/../config/database.php';
Database::init($config);
$pdo = Database::connection();

echo "Clearing existing applications / status history...\n";
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('TRUNCATE TABLE application_status_history');
$pdo->exec('TRUNCATE TABLE applications');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

/**
 * Each entry: [candidate_id, job_id, status_chain, applied_days_ago]
 * status_chain is the ordered sequence of statuses the application walks
 * through, e.g. ['applied', 'shortlisted', 'interview', 'offered'].
 * Candidates/skills below were seeded in schema.sql specifically to produce
 * a range of outcomes: strong matches, partial matches, and two deliberate
 * "missing a must-have skill" 50-point-cap examples (candidate 16 on jobs
 * 1 and 2) — see schema.sql's candidate_skills comments.
 */
$pairs = [
    // Job 1: Backend Engineer (Node.js) — recruiter 2
    [11, 1, ['applied', 'shortlisted', 'interview', 'offered'], 6],
    [16, 1, ['applied', 'rejected'], 5],          // missing must-have MongoDB -> capped at 50
    [15, 1, ['applied'], 3],                       // fresher, low score

    // Job 2: Frontend Developer (React) — recruiter 2
    [12, 2, ['applied', 'shortlisted', 'interview'], 4],
    [16, 2, ['applied'], 3],                       // missing must-have React/HTML5 -> capped
    [20, 2, ['applied', 'rejected'], 3],           // UI/UX designer, weak fit for a dev role

    // Job 3: DevOps Engineer (closed) — recruiter 2
    [13, 3, ['applied', 'shortlisted', 'interview', 'offered'], 18],
    [27, 3, ['applied', 'rejected'], 17],

    // Job 4: Junior Software Engineer Intern — recruiter 2
    [15, 4, ['applied', 'shortlisted'], 1],
    [19, 4, ['applied'], 1],

    // Job 5: Data Analyst — recruiter 3
    [14, 5, ['applied', 'shortlisted', 'interview', 'offered'], 3],
    [22, 5, ['applied', 'shortlisted'], 2],
    [28, 5, ['applied'], 2],

    // Job 6: Full Stack Developer (Python/Django) — recruiter 3
    [16, 6, ['applied', 'shortlisted'], 4],
    [25, 6, ['applied', 'rejected'], 4],

    // Job 8: Product Manager — recruiter 4
    [18, 8, ['applied', 'shortlisted', 'interview', 'offered'], 7],

    // Job 9: UI/UX Designer — recruiter 4
    [20, 9, ['applied', 'shortlisted', 'interview'], 2],

    // Job 11: Mobile App Developer (Flutter) — recruiter 5
    [17, 11, ['applied', 'shortlisted'], 6],

    // Job 12: QA Engineer — recruiter 5
    [19, 12, ['applied', 'shortlisted', 'interview', 'offered'], 3],

    // Job 13: IT Support Executive — recruiter 6
    [24, 13, ['applied', 'shortlisted'], 9],

    // Job 14: Business Analyst (flagged, still open) — recruiter 6
    [22, 14, ['applied'], 3],

    // Withdrawn example
    [23, 15, ['applied', 'withdrawn'], 12],
    [26, 13, ['applied', 'withdrawn'], 5],
];

$jobRecruiterStmt = $pdo->prepare('SELECT recruiter_id FROM jobs WHERE id = ?');
$insertApp = $pdo->prepare(
    'INSERT INTO applications (job_id, candidate_id, status, match_score, score_breakdown, applied_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$insertHistory = $pdo->prepare(
    'INSERT INTO application_status_history (application_id, from_status, to_status, changed_by, note, changed_at)
     VALUES (?, ?, ?, ?, ?, ?)'
);

$notesByStatus = [
    'shortlisted' => 'Good fit on paper — moving to shortlist.',
    'interview' => 'Scheduling a first-round interview outside the system.',
    'offered' => 'Strong interview — extending an offer.',
    'rejected' => 'Not proceeding at this time.',
];

$created = 0;
foreach ($pairs as [$candidateId, $jobId, $chain, $daysAgo]) {
    $jobRecruiterStmt->execute([$jobId]);
    $recruiterId = (int) $jobRecruiterStmt->fetchColumn();

    $score = MatchScoreCalculator::compute($candidateId, $jobId);
    $finalStatus = end($chain);
    $appliedAt = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));

    $insertApp->execute([
        $jobId, $candidateId, $finalStatus, $score['overall'], json_encode($score), $appliedAt, $appliedAt,
    ]);
    $applicationId = (int) $pdo->lastInsertId();

    $from = null;
    $stepTime = strtotime($appliedAt);
    foreach ($chain as $i => $status) {
        $changedBy = in_array($status, ['applied', 'withdrawn'], true) ? $candidateId : $recruiterId;
        $note = $notesByStatus[$status] ?? null;
        $changedAt = date('Y-m-d H:i:s', $stepTime + $i * 86400);
        $insertHistory->execute([$applicationId, $from, $status, $changedBy, $note, $changedAt]);
        $from = $status;
    }

    $created++;
    echo sprintf("  #%d candidate=%d job=%d score=%d status=%s\n", $applicationId, $candidateId, $jobId, $score['overall'], $finalStatus);
}

echo "\nDone — {$created} applications generated with real MatchScoreCalculator output.\n";
