<?php

namespace App\Controllers;

use App\Models\SkillRequest;
use Core\Auth;
use Core\Csrf;
use Core\Router;
use Core\View;

/** Shared: both recruiters and candidates can request a new taxonomy skill (FEATURES.md). */
class SkillRequestController
{
    public function submit(): void
    {
        Auth::requireRole('recruiter', 'candidate');
        if (!Csrf::verifyRequest()) {
            Router::redirect($_POST['redirect_to'] ?? '/');
        }

        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $redirect = $_POST['redirect_to'] ?? (Auth::role() === 'recruiter' ? '/recruiter/jobs' : '/candidate/profile');

        if ($name === '' || $category === '') {
            View::flash('error', 'Skill name and category are required.');
        } else {
            SkillRequest::create(Auth::id(), $name, $category);
            View::flash('success', 'Skill request submitted for admin review.');
        }

        Router::redirect($redirect);
    }
}
