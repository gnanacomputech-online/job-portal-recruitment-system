<?php

namespace App\Controllers;

use App\Models\User;
use Core\Auth;
use Core\Csrf;
use Core\Router;
use Core\View;

class AdminCandidateController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        $status = $_GET['status'] ?? null;
        $candidates = User::listCandidates($status ?: null);
        View::render('admin/candidates/index', ['candidates' => $candidates, 'status' => $status]);
    }

    public function suspend(int $id): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/candidates');
        }
        $user = User::findById($id);
        if ($user && $user['role'] === 'candidate') {
            User::updateStatus($id, 'suspended');
            View::flash('success', 'Candidate suspended.');
        }
        Router::redirect('/admin/candidates');
    }

    public function unsuspend(int $id): void
    {
        Auth::requireRole('admin');
        if (!Csrf::verifyRequest()) {
            Router::redirect('/admin/candidates');
        }
        $user = User::findById($id);
        if ($user && $user['role'] === 'candidate') {
            User::updateStatus($id, 'approved');
            View::flash('success', 'Candidate reinstated.');
        }
        Router::redirect('/admin/candidates');
    }
}
