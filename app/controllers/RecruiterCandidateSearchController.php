<?php

namespace App\Controllers;

use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\Job;
use App\Models\Skill;
use App\Models\User;
use Core\Auth;
use Core\MatchScoreCalculator;
use Core\View;

/** Recruiter proactively searching the open-to-work candidate pool (FEATURES.md). */
class RecruiterCandidateSearchController
{
    public function index(): void
    {
        Auth::requireRole('recruiter');
        $skillId = !empty($_GET['skill_id']) ? (int) $_GET['skill_id'] : null;
        $candidates = CandidateProfile::searchOpenToWork($skillId);
        $jobs = Job::listForRecruiter(Auth::id());

        View::render('recruiter/candidates/index', [
            'candidates' => $candidates,
            'skills' => Skill::all(),
            'selectedSkillId' => $skillId,
            'jobs' => $jobs,
        ]);
    }

    /** Preview a candidate's match score against one of this recruiter's own postings. */
    public function preview(int $candidateId): void
    {
        Auth::requireRole('recruiter');
        $candidate = User::findById($candidateId);
        if (!$candidate || $candidate['role'] !== 'candidate') {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        // `open_to_work` is the gate on the searchable talent pool, so it has to
        // gate this screen too — otherwise a recruiter could read any
        // candidate's full profile (resume, timeline, contact details) by
        // walking ids, even for candidates who explicitly opted out of recruiter
        // search. The one legitimate exception is a candidate who applied to
        // this recruiter's own posting: they chose to share their profile with
        // this company.
        $profile = CandidateProfile::find($candidateId);
        $inPool = $candidate['status'] === 'approved' && !empty($profile['open_to_work']);
        $isOwnApplicant = Application::existsForRecruiterAndCandidate(Auth::id(), $candidateId);
        if (!$inPool && !$isOwnApplicant) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        $jobId = !empty($_GET['job_id']) ? (int) $_GET['job_id'] : null;
        $job = null;
        $score = null;

        if ($jobId) {
            $job = Job::find($jobId);
            if (!$job || (int) $job['recruiter_id'] !== Auth::id()) {
                $job = null;
            } else {
                $score = MatchScoreCalculator::compute($candidateId, $jobId);
            }
        }

        View::render('recruiter/candidates/preview', [
            'candidate' => $candidate,
            'profile' => $profile,
            'candidateSkills' => \App\Models\CandidateSkill::listForCandidate($candidateId),
            'experience' => \App\Models\CandidateExperience::listForCandidate($candidateId),
            'education' => \App\Models\CandidateEducation::listForCandidate($candidateId),
            'jobs' => Job::listForRecruiter(Auth::id()),
            'selectedJob' => $job,
            'score' => $score,
        ]);
    }
}
