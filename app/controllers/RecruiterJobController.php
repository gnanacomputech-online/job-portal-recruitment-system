<?php

namespace App\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\JobSkill;
use App\Models\Skill;
use Core\Auth;
use Core\Csrf;
use Core\Router;
use Core\Validator;
use Core\View;

class RecruiterJobController
{
    private function ownedJobOrRedirect(int $id): array
    {
        $job = Job::find($id);
        if (!$job || (int) $job['recruiter_id'] !== Auth::id()) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            exit;
        }
        return $job;
    }

    public function index(): void
    {
        Auth::requireRole('recruiter');
        $jobs = Job::listForRecruiter(Auth::id());
        View::render('recruiter/jobs/index', ['jobs' => $jobs]);
    }

    public function showNew(): void
    {
        Auth::requireRole('recruiter');
        View::render('recruiter/jobs/form', ['job' => null, 'requirements' => [], 'grouped' => Skill::grouped()]);
    }

    private function collectRequirements(): array
    {
        $requirements = [];
        $skillIds = $_POST['skill_id'] ?? [];
        $importances = $_POST['importance'] ?? [];
        $minProfs = $_POST['min_proficiency'] ?? [];
        foreach ($skillIds as $i => $skillId) {
            $skillId = (int) $skillId;
            if ($skillId <= 0) {
                continue;
            }
            $requirements[$skillId] = [
                'skill_id' => $skillId,
                'importance' => in_array($importances[$i] ?? '', ['must_have', 'nice_to_have'], true) ? $importances[$i] : 'must_have',
                'min_proficiency' => in_array($minProfs[$i] ?? '', ['beginner', 'intermediate', 'expert'], true) ? $minProfs[$i] : 'beginner',
            ];
        }
        return array_values($requirements);
    }

    public function create(): void
    {
        Auth::requireRole('recruiter');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/recruiter/jobs/new');
        }

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'job_type' => $_POST['job_type'] ?? 'full_time',
            'min_years_experience' => $_POST['min_years_experience'] ?? '0',
            'min_education_level' => $_POST['min_education_level'] ?? '',
        ];

        $v = new Validator($data);
        $v->required('title')->required('description')->required('location')->numeric('min_years_experience');
        if ($v->fails()) {
            $_SESSION['errors'] = $v->firstErrors();
            $_SESSION['old'] = $data;
            Router::redirect('/recruiter/jobs/new');
        }

        $data['status'] = ($_POST['action'] ?? 'publish') === 'draft' ? 'draft' : 'open';
        $jobId = Job::create(Auth::id(), $data);
        JobSkill::replaceForJob($jobId, $this->collectRequirements());

        View::flash('success', 'Job posting created.');
        Router::redirect('/recruiter/jobs');
    }

    public function showEdit(int $id): void
    {
        Auth::requireRole('recruiter');
        $job = $this->ownedJobOrRedirect($id);
        View::render('recruiter/jobs/form', [
            'job' => $job,
            'requirements' => JobSkill::listForJob($id),
            'grouped' => Skill::grouped(),
        ]);
    }

    public function update(int $id): void
    {
        Auth::requireRole('recruiter');
        $this->ownedJobOrRedirect($id);
        if (!Csrf::verifyRequest()) {
            Router::redirect("/recruiter/jobs/{$id}/edit");
        }

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'job_type' => $_POST['job_type'] ?? 'full_time',
            'min_years_experience' => $_POST['min_years_experience'] ?? '0',
            'min_education_level' => $_POST['min_education_level'] ?? '',
        ];

        $v = new Validator($data);
        $v->required('title')->required('description')->required('location')->numeric('min_years_experience');
        if ($v->fails()) {
            $_SESSION['errors'] = $v->firstErrors();
            Router::redirect("/recruiter/jobs/{$id}/edit");
        }

        Job::update($id, $data);
        JobSkill::replaceForJob($id, $this->collectRequirements());

        View::flash('success', 'Job posting updated.');
        Router::redirect('/recruiter/jobs');
    }

    public function close(int $id): void
    {
        Auth::requireRole('recruiter');
        $this->ownedJobOrRedirect($id);
        if (!Csrf::verifyRequest()) {
            Router::redirect('/recruiter/jobs');
        }
        Job::updateStatus($id, 'closed');
        View::flash('success', 'Job posting closed.');
        Router::redirect('/recruiter/jobs');
    }

    public function reopen(int $id): void
    {
        Auth::requireRole('recruiter');
        $this->ownedJobOrRedirect($id);
        if (!Csrf::verifyRequest()) {
            Router::redirect('/recruiter/jobs');
        }
        Job::updateStatus($id, 'open');
        View::flash('success', 'Job posting reopened.');
        Router::redirect('/recruiter/jobs');
    }

    public function delete(int $id): void
    {
        Auth::requireRole('recruiter');
        $this->ownedJobOrRedirect($id);
        if (!Csrf::verifyRequest()) {
            Router::redirect('/recruiter/jobs');
        }
        // `applications` (and its status history) cascade from `jobs`, so
        // deleting a posting people have already applied to would wipe those
        // candidates' application records — and their snapshotted match
        // scores — out from under them, with no trace on either side. Once a
        // posting has applicants it can only be closed, never deleted.
        $applicantCount = Application::countForJob($id);
        if ($applicantCount > 0) {
            View::flash('error', "This posting has {$applicantCount} application(s) and can no longer be deleted — deleting it would erase those candidates' applications and match scores. Close the posting instead.");
            Router::redirect('/recruiter/jobs');
        }

        Job::delete($id);
        View::flash('success', 'Job posting deleted.');
        Router::redirect('/recruiter/jobs');
    }
}
