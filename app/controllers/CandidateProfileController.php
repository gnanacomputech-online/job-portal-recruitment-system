<?php

namespace App\Controllers;

use App\Models\CandidateEducation;
use App\Models\CandidateExperience;
use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Skill;
use Core\Auth;
use Core\Csrf;
use Core\Router;
use Core\Upload;
use Core\View;

class CandidateProfileController
{
    public function show(): void
    {
        Auth::requireRole('candidate');
        $candidateId = Auth::id();

        View::render('candidate/profile/show', [
            'profile' => CandidateProfile::find($candidateId),
            'experience' => CandidateExperience::listForCandidate($candidateId),
            'education' => CandidateEducation::listForCandidate($candidateId),
            'candidateSkills' => CandidateSkill::listForCandidate($candidateId),
            'grouped' => Skill::grouped(),
            'completeness' => CandidateProfile::completeness($candidateId),
        ]);
    }

    public function updateBasics(): void
    {
        Auth::requireRole('candidate');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/candidate/profile');
        }

        CandidateProfile::upsertBasics(Auth::id(), [
            'headline' => trim($_POST['headline'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
        ]);

        View::flash('success', 'Profile updated.');
        Router::redirect('/candidate/profile');
    }

    public function toggleOpenToWork(): void
    {
        Auth::requireRole('candidate');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/candidate/profile');
        }
        $current = CandidateProfile::find(Auth::id());
        $isOpen = $current ? (bool) $current['open_to_work'] : false;
        CandidateProfile::setOpenToWork(Auth::id(), !$isOpen);
        View::flash('success', !$isOpen ? 'You are now visible as Open to Work.' : 'You are no longer visible in recruiter searches.');
        Router::redirect('/candidate/profile');
    }

    public function uploadResume(): void
    {
        Auth::requireRole('candidate');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/candidate/profile');
        }

        if (empty($_FILES['resume']['name'])) {
            View::flash('error', 'Please choose a file to upload.');
            Router::redirect('/candidate/profile');
        }

        $path = Upload::storeResume($_FILES['resume']);
        if (!$path) {
            View::flash('error', 'Resume upload failed — must be PDF/DOC/DOCX under 5MB.');
            Router::redirect('/candidate/profile');
        }

        CandidateProfile::updateResumePath(Auth::id(), $path);
        View::flash('success', 'Resume uploaded.');
        Router::redirect('/candidate/profile');
    }

    public function uploadPhoto(): void
    {
        Auth::requireRole('candidate');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/candidate/profile');
        }
        if (empty($_FILES['photo']['name'])) {
            View::flash('error', 'Please choose a photo to upload.');
            Router::redirect('/candidate/profile');
        }
        $path = Upload::storePhoto($_FILES['photo']);
        if (!$path) {
            View::flash('error', 'Photo upload failed — must be JPG/PNG/WebP under 2MB.');
            Router::redirect('/candidate/profile');
        }
        CandidateProfile::updatePhotoPath(Auth::id(), $path);
        View::flash('success', 'Photo updated.');
        Router::redirect('/candidate/profile');
    }

    // ---- Experience -----------------------------------------------------

    public function addExperience(): void
    {
        Auth::requireRole('candidate');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/candidate/profile');
        }

        $isCurrent = !empty($_POST['is_current']);
        CandidateExperience::create(Auth::id(), [
            'title' => trim($_POST['title'] ?? ''),
            'company' => trim($_POST['company'] ?? ''),
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? null,
            'is_current' => $isCurrent,
            'description' => trim($_POST['description'] ?? ''),
        ]);

        View::flash('success', 'Experience added.');
        Router::redirect('/candidate/profile');
    }

    public function deleteExperience(int $id): void
    {
        Auth::requireRole('candidate');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/candidate/profile');
        }
        CandidateExperience::delete($id, Auth::id());
        View::flash('success', 'Experience entry removed.');
        Router::redirect('/candidate/profile');
    }

    // ---- Education --------------------------------------------------------

    public function addEducation(): void
    {
        Auth::requireRole('candidate');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/candidate/profile');
        }

        CandidateEducation::create(Auth::id(), [
            'degree' => trim($_POST['degree'] ?? ''),
            'institution' => trim($_POST['institution'] ?? ''),
            'field' => trim($_POST['field'] ?? ''),
            'level' => $_POST['level'] ?? 'bachelor',
            'year_completed' => $_POST['year_completed'] ?? null,
        ]);

        View::flash('success', 'Education added.');
        Router::redirect('/candidate/profile');
    }

    public function deleteEducation(int $id): void
    {
        Auth::requireRole('candidate');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/candidate/profile');
        }
        CandidateEducation::delete($id, Auth::id());
        View::flash('success', 'Education entry removed.');
        Router::redirect('/candidate/profile');
    }

    // ---- Skills -------------------------------------------------------------

    public function addSkill(): void
    {
        Auth::requireRole('candidate');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/candidate/profile');
        }

        $skillId = (int) ($_POST['skill_id'] ?? 0);
        $proficiency = $_POST['proficiency'] ?? 'beginner';
        if ($skillId > 0 && in_array($proficiency, ['beginner', 'intermediate', 'expert'], true)) {
            CandidateSkill::add(Auth::id(), $skillId, $proficiency);
            View::flash('success', 'Skill added.');
        }

        Router::redirect('/candidate/profile');
    }

    public function removeSkill(int $skillId): void
    {
        Auth::requireRole('candidate');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/candidate/profile');
        }
        CandidateSkill::remove(Auth::id(), $skillId);
        View::flash('success', 'Skill removed.');
        Router::redirect('/candidate/profile');
    }
}
