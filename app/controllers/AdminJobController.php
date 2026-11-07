<?php

namespace App\Controllers;

use App\Models\Job;
use Core\Auth;
use Core\Csrf;
use Core\Router;
use Core\View;

class AdminJobController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        $jobs = Job::listAllForAdmin();
        View::render('admin/jobs/index', ['jobs' => $jobs]);
    }

    public function flag(int $id): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/jobs');
        }
        $note = trim($_POST['note'] ?? '');
        Job::updateModeration($id, 'flagged', $note ?: 'Flagged for review by admin.');
        View::flash('success', 'Job posting flagged.');
        Router::redirect('/admin/jobs');
    }

    public function remove(int $id): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/jobs');
        }
        $note = trim($_POST['note'] ?? '');
        Job::updateModeration($id, 'removed', $note ?: 'Removed by admin.');
        View::flash('success', 'Job posting removed from listings.');
        Router::redirect('/admin/jobs');
    }

    public function restore(int $id): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/jobs');
        }
        Job::updateModeration($id, 'active', null);
        View::flash('success', 'Job posting restored.');
        Router::redirect('/admin/jobs');
    }
}
